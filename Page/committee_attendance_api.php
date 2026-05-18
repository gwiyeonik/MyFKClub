<?php
session_start();

/* DATABASE CONNECTION */
$conn = mysqli_connect("localhost", "root", "", "myfkclub");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$message = "";

/* GET SELECTED EVENT ID */
$selectedEventID = isset($_GET['eventID']) ? intval($_GET['eventID']) : 0;

/* IF NO EVENT SELECTED, USE FIRST EVENT */
if ($selectedEventID === 0) {
    $firstEventSql = "SELECT eventID FROM `event` ORDER BY eventDateStart ASC LIMIT 1";
    $firstEventResult = mysqli_query($conn, $firstEventSql);

    if ($firstEventResult && mysqli_num_rows($firstEventResult) > 0) {
        $firstEvent = mysqli_fetch_assoc($firstEventResult);
        $selectedEventID = intval($firstEvent['eventID']);
    }
}

/* HELPER: CALCULATE POINTS */
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

/* HELPER: UPDATE STUDENT TOTAL POINTS */
function refreshStudentPointSummary($conn, $userID) {
    $totalSql = "SELECT COALESCE(SUM(ea.attendancePoints), 0) AS totalPoints
                 FROM eventattendance ea
                 INNER JOIN eventregistration er 
                 ON ea.registrationID = er.registrationID
                 WHERE er.userID = ?";

    $totalStmt = mysqli_prepare($conn, $totalSql);
    mysqli_stmt_bind_param($totalStmt, "i", $userID);
    mysqli_stmt_execute($totalStmt);
    $totalResult = mysqli_stmt_get_result($totalStmt);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $newTotalPoints = intval($totalRow['totalPoints']);

    $summaryCheckSql = "SELECT userID FROM studentpointsummary WHERE userID = ? LIMIT 1";
    $summaryCheckStmt = mysqli_prepare($conn, $summaryCheckSql);
    mysqli_stmt_bind_param($summaryCheckStmt, "i", $userID);
    mysqli_stmt_execute($summaryCheckStmt);
    $summaryCheckResult = mysqli_stmt_get_result($summaryCheckStmt);

    if (mysqli_num_rows($summaryCheckResult) > 0) {
        $summaryUpdateSql = "UPDATE studentpointsummary
                             SET totalPoints = ?, lastUpdated = NOW()
                             WHERE userID = ?";

        $summaryUpdateStmt = mysqli_prepare($conn, $summaryUpdateSql);
        mysqli_stmt_bind_param($summaryUpdateStmt, "ii", $newTotalPoints, $userID);
        mysqli_stmt_execute($summaryUpdateStmt);
    } else {
        $summaryInsertSql = "INSERT INTO studentpointsummary
                             (userID, totalPoints, lastUpdated)
                             VALUES (?, ?, NOW())";

        $summaryInsertStmt = mysqli_prepare($conn, $summaryInsertSql);
        mysqli_stmt_bind_param($summaryInsertStmt, "ii", $userID, $newTotalPoints);
        mysqli_stmt_execute($summaryInsertStmt);
    }
}

/* SAVE MANUAL ATTENDANCE */
if (isset($_POST['save_manual_attendance'])) {
    $studentSearch = trim($_POST['student_search']);
    $eventID = intval($_POST['event_id']);
    $attendanceStatus = $_POST['attendance_status'];
    $attendanceIsVolunteer = intval($_POST['is_volunteer']);

    if ($studentSearch === "" || $eventID === 0 || $attendanceStatus === "") {
        $message = "Please complete all required fields.";
    } else {
        $studentSql = "SELECT userID, userName
               FROM `user`
               WHERE roleID = 3
               AND (
                    userName LIKE ?
                    OR userEmail LIKE ?
                    OR CAST(userID AS CHAR) = ?
               )
               LIMIT 1";

        $studentLike = "%" . $studentSearch . "%";

        $studentStmt = mysqli_prepare($conn, $studentSql);
        mysqli_stmt_bind_param($studentStmt, "sss", $studentLike, $studentLike, $studentSearch);
        mysqli_stmt_execute($studentStmt);
        $studentResult = mysqli_stmt_get_result($studentStmt);

        if (mysqli_num_rows($studentResult) === 0) {
            $message = "Student not found.";
        } else {
            $student = mysqli_fetch_assoc($studentResult);
            $userID = intval($student['userID']);

            $registrationSql = "SELECT registrationID
                                FROM eventregistration
                                WHERE eventID = ?
                                AND userID = ?
                                LIMIT 1";

            $registrationStmt = mysqli_prepare($conn, $registrationSql);
            mysqli_stmt_bind_param($registrationStmt, "ii", $eventID, $userID);
            mysqli_stmt_execute($registrationStmt);
            $registrationResult = mysqli_stmt_get_result($registrationStmt);

            if (mysqli_num_rows($registrationResult) === 0) {
                $message = "This student is not registered for the selected event.";
            } else {
                $registration = mysqli_fetch_assoc($registrationResult);
                $registrationID = intval($registration['registrationID']);
                $attendancePoints = calculateAttendancePoints($attendanceStatus, $attendanceIsVolunteer);

                $checkSql = "SELECT attendanceID FROM eventattendance WHERE registrationID = ?";
                $checkStmt = mysqli_prepare($conn, $checkSql);
                mysqli_stmt_bind_param($checkStmt, "i", $registrationID);
                mysqli_stmt_execute($checkStmt);
                $checkResult = mysqli_stmt_get_result($checkStmt);

                if (mysqli_num_rows($checkResult) > 0) {
                    $updateSql = "UPDATE eventattendance
                                  SET attendanceStatus = ?,
                                      attendanceCheckinTime = NOW(),
                                      attendancePoints = ?,
                                      attendanceIsVolunteer = ?
                                  WHERE registrationID = ?";

                    $updateStmt = mysqli_prepare($conn, $updateSql);
                    mysqli_stmt_bind_param($updateStmt, "siii", $attendanceStatus, $attendancePoints, $attendanceIsVolunteer, $registrationID);
                    mysqli_stmt_execute($updateStmt);
                } else {
                    $insertSql = "INSERT INTO eventattendance
                                  (registrationID, attendanceStatus, attendanceCheckinTime, attendancePoints, attendanceIsVolunteer)
                                  VALUES (?, ?, NOW(), ?, ?)";

                    $insertStmt = mysqli_prepare($conn, $insertSql);
                    mysqli_stmt_bind_param($insertStmt, "isii", $registrationID, $attendanceStatus, $attendancePoints, $attendanceIsVolunteer);
                    mysqli_stmt_execute($insertStmt);
                }

                refreshStudentPointSummary($conn, $userID);

                header("Location: committee_attendance_report.php?eventID=" . $eventID . "&saved=1");
                exit;
            }
        }
    }
}

/* UPDATE ATTENDANCE FROM TABLE */
if (isset($_POST['update_attendance'])) {
    $registrationID = intval($_POST['registrationID']);
    $userID = intval($_POST['userID']);
    $attendanceStatus = $_POST['attendanceStatus'];
    $attendanceIsVolunteer = intval($_POST['attendanceIsVolunteer']);

    $attendancePoints = calculateAttendancePoints($attendanceStatus, $attendanceIsVolunteer);

    $checkSql = "SELECT attendanceID FROM eventattendance WHERE registrationID = ?";
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "i", $registrationID);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($checkResult) > 0) {
        $updateSql = "UPDATE eventattendance
                      SET attendanceStatus = ?,
                          attendanceCheckinTime = NOW(),
                          attendancePoints = ?,
                          attendanceIsVolunteer = ?
                      WHERE registrationID = ?";

        $updateStmt = mysqli_prepare($conn, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "siii", $attendanceStatus, $attendancePoints, $attendanceIsVolunteer, $registrationID);
        mysqli_stmt_execute($updateStmt);
    } else {
        $insertSql = "INSERT INTO eventattendance
                      (registrationID, attendanceStatus, attendanceCheckinTime, attendancePoints, attendanceIsVolunteer)
                      VALUES (?, ?, NOW(), ?, ?)";

        $insertStmt = mysqli_prepare($conn, $insertSql);
        mysqli_stmt_bind_param($insertStmt, "isii", $registrationID, $attendanceStatus, $attendancePoints, $attendanceIsVolunteer);
        mysqli_stmt_execute($insertStmt);
    }

    refreshStudentPointSummary($conn, $userID);

    header("Location: committee_attendance_report.php?eventID=" . $selectedEventID . "&updated=1");
    exit;
}

/* DELETE / CLEAR ATTENDANCE RECORD ONLY */
if (isset($_POST['delete_attendance'])) {
    $registrationID = intval($_POST['registrationID']);
    $userID = intval($_POST['userID']);

    $deleteSql = "DELETE FROM eventattendance WHERE registrationID = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSql);
    mysqli_stmt_bind_param($deleteStmt, "i", $registrationID);
    mysqli_stmt_execute($deleteStmt);

    refreshStudentPointSummary($conn, $userID);

    header("Location: committee_attendance_report.php?eventID=" . $selectedEventID . "&deleted=1");
    exit;
}

/* MESSAGE */
if (isset($_GET['saved'])) {
    $message = "Attendance saved successfully.";
} elseif (isset($_GET['updated'])) {
    $message = "Attendance updated successfully.";
} elseif (isset($_GET['deleted'])) {
    $message = "Attendance record deleted successfully.";
}

/* GET ALL EVENTS FOR DROPDOWN */
$events = [];

$eventListSql = "SELECT eventID, eventTitle, eventDateStart, eventDateEnd, eventVenue
                 FROM `event`
                 ORDER BY eventDateStart ASC";

$eventListResult = mysqli_query($conn, $eventListSql);

if ($eventListResult && mysqli_num_rows($eventListResult) > 0) {
    while ($eventRow = mysqli_fetch_assoc($eventListResult)) {
        $events[] = $eventRow;
    }
}

/* GET SELECTED EVENT DETAILS */
$eventInfo = null;

if ($selectedEventID > 0) {
    $eventSql = "SELECT 
                    e.eventID,
                    e.eventTitle,
                    e.eventVenue,
                    e.eventDateStart,
                    e.eventDateEnd,
                    COUNT(er.registrationID) AS registeredCount
                 FROM `event` e
                 LEFT JOIN eventregistration er 
                 ON e.eventID = er.eventID
                 WHERE e.eventID = ?
                 GROUP BY 
                    e.eventID,
                    e.eventTitle,
                    e.eventVenue,
                    e.eventDateStart,
                    e.eventDateEnd";

    $eventStmt = mysqli_prepare($conn, $eventSql);
    mysqli_stmt_bind_param($eventStmt, "i", $selectedEventID);
    mysqli_stmt_execute($eventStmt);
    $eventResult = mysqli_stmt_get_result($eventStmt);

    if ($eventResult && mysqli_num_rows($eventResult) > 0) {
        $eventInfo = mysqli_fetch_assoc($eventResult);
    }
}

/* GET REGISTERED PARTICIPANTS FOR SELECTED EVENT */
$participants = [];

if ($selectedEventID > 0) {
    $participantSql = "SELECT
                            er.registrationID,
                            er.userID,
                            u.userName,
                            ea.attendanceCheckinTime,
                            ea.attendanceStatus,
                            ea.attendanceIsVolunteer,
                            ea.attendancePoints
                       FROM eventregistration er
                       INNER JOIN `user` u ON er.userID = u.userID
                       LEFT JOIN eventattendance ea ON er.registrationID = ea.registrationID
                       WHERE er.eventID = ?
                       ORDER BY u.userName ASC";

    $participantStmt = mysqli_prepare($conn, $participantSql);
    mysqli_stmt_bind_param($participantStmt, "i", $selectedEventID);
    mysqli_stmt_execute($participantStmt);
    $participantResult = mysqli_stmt_get_result($participantStmt);

    if ($participantResult && mysqli_num_rows($participantResult) > 0) {
        while ($participantRow = mysqli_fetch_assoc($participantResult)) {
            $participants[] = $participantRow;
        }
    }
}
?>