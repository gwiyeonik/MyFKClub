<?php
session_start();

$conn = new mysqli("localhost", "root", "", "myfkclub");
if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

function calculateAttendancePoints($attendanceStatus, $attendanceIsVolunteer) {
    $attendancePoints = 0;

    if ($attendanceStatus === "present") {
        $attendancePoints += 10;
    } elseif ($attendanceStatus === "late") {
        $attendancePoints -= 5;
    } elseif ($attendanceStatus === "absent") {
        $attendancePoints -= 10;
    }

    if (intval($attendanceIsVolunteer) === 1) {
        $attendancePoints += 5;
    }

    return $attendancePoints;
}

function refreshStudentPointSummary($conn, $userID) {
    $totalSql = "SELECT COALESCE(SUM(ea.attendancePoints), 0) AS totalPoints
                 FROM eventattendance ea
                 INNER JOIN eventregistration er ON ea.registrationID = er.registrationID
                 WHERE er.userID = ?";

    $totalStmt = $conn->prepare($totalSql);
    $totalStmt->bind_param("i", $userID);
    $totalStmt->execute();
    $totalResult = $totalStmt->get_result();
    $totalRow = $totalResult->fetch_assoc();
    $newTotalPoints = intval($totalRow['totalPoints']);

    $summaryCheckSql = "SELECT userID FROM studentpointsummary WHERE userID = ? LIMIT 1";
    $summaryCheckStmt = $conn->prepare($summaryCheckSql);
    $summaryCheckStmt->bind_param("i", $userID);
    $summaryCheckStmt->execute();
    $summaryCheckResult = $summaryCheckStmt->get_result();

    if (mysqli_num_rows($summaryCheckResult) > 0) {
        $summaryUpdateSql = "UPDATE studentpointsummary SET totalPoints = ?, lastUpdated = NOW() WHERE userID = ?";
        $summaryUpdateStmt = mysqli_prepare($conn, $summaryUpdateSql);
        mysqli_stmt_bind_param($summaryUpdateStmt, "ii", $newTotalPoints, $userID);
        mysqli_stmt_execute($summaryUpdateStmt);
    } else {
        $summaryInsertSql = "INSERT INTO studentpointsummary (userID, totalPoints, lastUpdated) VALUES (?, ?, NOW())";
        $summaryInsertStmt = mysqli_prepare($conn, $summaryInsertSql);
        mysqli_stmt_bind_param($summaryInsertStmt, "ii", $userID, $newTotalPoints);
        mysqli_stmt_execute($summaryInsertStmt);
    }
}

function generateEventQRToken($eventID, $eventTitle) {
    $secret = 'myfkclub_qr_secret';
    return hash_hmac('sha256', $eventID . '|' . $eventTitle, $secret);
}

$eventID = isset($_GET['eventID']) ? intval($_GET['eventID']) : 0;
$token = isset($_GET['token']) ? $_GET['token'] : '';

$message = '';
$success = false;
$eventInfo = null;

if ($eventID > 0) {
    $eventSql = "SELECT eventID, eventTitle, eventVenue, eventDateStart, eventDateEnd FROM `event` WHERE eventID = ? LIMIT 1";
    $eventStmt = mysqli_prepare($conn, $eventSql);
    mysqli_stmt_bind_param($eventStmt, "i", $eventID);
    mysqli_stmt_execute($eventStmt);
    $eventResult = mysqli_stmt_get_result($eventStmt);

    if ($eventResult && mysqli_num_rows($eventResult) > 0) {
        $eventInfo = mysqli_fetch_assoc($eventResult);
        $expectedToken = generateEventQRToken($eventInfo['eventID'], $eventInfo['eventTitle']);
        if ($token !== $expectedToken) {
            $message = 'Invalid or expired QR link. Please generate a new QR code for this event.';
            $eventInfo = null;
        }
    } else {
        $message = 'Event not found. Please scan a valid event QR code.';
    }
} else {
    $message = 'Missing event data in the QR code.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $eventInfo) {
    $studentIDRaw = trim($_POST['student_id']);
    $attendanceIsVolunteer = intval($_POST['is_volunteer']);

    if ($studentIDRaw === '') {
        $message = 'Student ID is required to check in.';
    } elseif (!preg_match('/^US(\d+)$/i', $studentIDRaw, $matches)) {
        $message = 'Invalid Student ID format. Please use US0001 format.';
    } else {
        $studentID = intval($matches[1]);

        $studentSql = "SELECT userID, userName FROM `user` WHERE roleID = 3 AND userID = ? LIMIT 1";
        $studentStmt = mysqli_prepare($conn, $studentSql);
        mysqli_stmt_bind_param($studentStmt, "i", $studentID);
        mysqli_stmt_execute($studentStmt);
        $studentResult = mysqli_stmt_get_result($studentStmt);

        if (!$studentResult || mysqli_num_rows($studentResult) === 0) {
            $message = 'Student not found or not registered as a student.';
        } else {
            $student = mysqli_fetch_assoc($studentResult);
            $userID = intval($student['userID']);

            $registrationSql = "SELECT registrationID FROM eventregistration WHERE eventID = ? AND userID = ? LIMIT 1";
            $registrationStmt = mysqli_prepare($conn, $registrationSql);
            mysqli_stmt_bind_param($registrationStmt, "ii", $eventID, $userID);
            mysqli_stmt_execute($registrationStmt);
            $registrationResult = mysqli_stmt_get_result($registrationStmt);

            if (!$registrationResult || mysqli_num_rows($registrationResult) === 0) {
                $message = 'This student is not registered for the selected event.';
            } else {
                $registration = mysqli_fetch_assoc($registrationResult);
                $registrationID = intval($registration['registrationID']);

                $checkinTime = new DateTime('now');
                $startTime = new DateTime($eventInfo['eventDateStart']);
                $endTime = new DateTime($eventInfo['eventDateEnd'] ?: $eventInfo['eventDateStart']);

                $status = 'present';
                $lateThreshold = clone $startTime;
                $lateThreshold->modify('+15 minutes');
                $absentThreshold = clone $startTime;
                $absentThreshold->modify('+60 minutes');

                if ($checkinTime > $absentThreshold && $checkinTime <= $endTime) {
                    $status = 'late';
                } elseif ($checkinTime > $endTime) {
                    $status = 'absent';
                }

                $attendancePoints = calculateAttendancePoints($status, $attendanceIsVolunteer);

                $checkSql = "SELECT attendanceID FROM eventattendance WHERE registrationID = ?";
                $checkStmt = mysqli_prepare($conn, $checkSql);
                mysqli_stmt_bind_param($checkStmt, "i", $registrationID);
                mysqli_stmt_execute($checkStmt);
                $checkResult = mysqli_stmt_get_result($checkStmt);

                if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                    $updateSql = "UPDATE eventattendance
                                  SET attendanceStatus = ?,
                                      attendanceCheckinTime = NOW(),
                                      attendancePoints = ?,
                                      attendanceIsVolunteer = ?
                                  WHERE registrationID = ?";
                    $updateStmt = mysqli_prepare($conn, $updateSql);
                    mysqli_stmt_bind_param($updateStmt, "siii", $status, $attendancePoints, $attendanceIsVolunteer, $registrationID);
                    mysqli_stmt_execute($updateStmt);
                } else {
                    $insertSql = "INSERT INTO eventattendance
                                  (registrationID, attendanceStatus, attendanceCheckinTime, attendancePoints, attendanceIsVolunteer)
                                  VALUES (?, ?, NOW(), ?, ?)";
                    $insertStmt = mysqli_prepare($conn, $insertSql);
                    mysqli_stmt_bind_param($insertStmt, "isii", $registrationID, $status, $attendancePoints, $attendanceIsVolunteer);
                    mysqli_stmt_execute($insertStmt);
                }

                refreshStudentPointSummary($conn, $userID);
                $success = true;
                $message = 'Check-in recorded. Status: ' . ucfirst($status) . '. Points: ' . $attendancePoints . '.';
            }
        }
    }
}

function htmlEscape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Check-In | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <style>
    .scan-shell {
      display: flex;
      justify-content: center;
      padding: 40px 20px;
    }
    .scan-card {
      max-width: 680px;
      width: 100%;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 12px 30px rgba(0,0,0,.08);
      padding: 28px;
    }
    .scan-card h1 {
      margin-top: 0;
      margin-bottom: 18px;
      font-size: 28px;
    }
    .scan-message {
      padding: 14px 16px;
      border-radius: 10px;
      margin-bottom: 18px;
    }
    .scan-message.success { background: #e6ffed; color: #1f6f3d; }
    .scan-message.error { background: #ffe6e6; color: #9f2222; }
    .scan-field { margin-bottom: 16px; }
    .scan-field label { display: block; margin-bottom: 8px; font-weight: 600; }
    .scan-field input, .scan-field select { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 8px; }
    .scan-submit { background: #2d7be6; color: white; border: none; padding: 12px 18px; border-radius: 8px; cursor: pointer; }
    .scan-submit:hover { background: #1b5bb8; }
    .event-summary { margin-bottom: 24px; }
    .event-summary p { margin: 6px 0; }
  </style>
</head>
<body>
  <div class="scan-shell">
    <div class="scan-card">
      <h1>Event Check-In</h1>

      <?php if ($message) { ?>
        <div class="scan-message <?php echo $success ? 'success' : 'error'; ?>">
          <?php echo htmlEscape($message); ?>
        </div>
      <?php } ?>

      <?php if ($eventInfo) { ?>
        <div class="event-summary">
          <p><strong>Event:</strong> <?php echo htmlEscape($eventInfo['eventTitle']); ?></p>
          <p><strong>Venue:</strong> <?php echo htmlEscape($eventInfo['eventVenue']); ?></p>
          <p><strong>Starts:</strong> <?php echo htmlEscape(date('Y-m-d H:i', strtotime($eventInfo['eventDateStart']))); ?></p>
          <p><strong>Ends:</strong> <?php echo htmlEscape(date('Y-m-d H:i', strtotime($eventInfo['eventDateEnd']))); ?></p>
        </div>

        <form method="POST">
          <div class="scan-field">
            <label for="student_id">Student ID</label>
            <input type="text" id="student_id" name="student_id" placeholder="US0001" required />
          </div>
          <div class="scan-field">
            <label for="is_volunteer">Volunteer / Helper</label>
            <select id="is_volunteer" name="is_volunteer">
              <option value="0">No</option>
              <option value="1">Yes</option>
            </select>
          </div>
          <button type="submit" class="scan-submit">Confirm Check-In</button>
        </form>
      <?php } else { ?>
        <p>Please open the event QR code again from the committee page or contact the event organizer.</p>
      <?php } ?>
    </div>
  </div>
</body>
</html>
