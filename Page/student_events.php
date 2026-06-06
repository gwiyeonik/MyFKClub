<?php
session_start();

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$current_user = $_SESSION['user_name'];
$userID = $_SESSION['user_id']; // make sure your login sets this

$conn = new mysqli("localhost", "root", "", "myfkclub");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle registration submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eventID'])) {
    $eventID = $_POST['eventID'];

    // Check if already registered
    $check = $conn->prepare("SELECT * FROM eventregistration WHERE userID = ? AND eventID = ?");
    $check->bind_param("ii", $userID, $eventID);
    $check->execute();
    $checkResult = $check->get_result();

    if ($checkResult->num_rows > 0) {
        $msg = "already";
    } else {
        // REMOVED registrationDate and CURDATE() from this query
        $stmt = $conn->prepare("INSERT INTO eventregistration (userID, eventID) VALUES (?, ?)");
        $stmt->bind_param("ii", $userID, $eventID);
        
        if ($stmt->execute()) {
            // Updated to a secure prepared statement to prevent potential SQL errors
            $updateStmt = $conn->prepare("UPDATE event SET eventParticipants = eventParticipants + 1 WHERE eventID = ?");
            $updateStmt->bind_param("i", $eventID);
            $updateStmt->execute();
            
            $msg = "success";
        } else {
            $msg = "error";
        }
    }
    header("Location: student_events.php?msg=$msg");
    exit();
}

// Fetch all events
$sql = "SELECT * FROM event ORDER BY eventDateStart ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/student_events.css">
</head>
<body>
<div class="dashboard-shell">
    <aside class="dashboard-sidebar">
        <div class="brand-panel">
            <img src="../Image/fkclub.jpg" alt="FKClub logo">
        </div>
        <nav class="sidebar-nav">
            <a href="myProfile.php" class="sidebar-link">My Profile</a>
            <a href="student_myclubs.php" class="sidebar-link">My Clubs</a>
            <a href="student_clublist.php" class="sidebar-link">Club List</a>
            <a href="student_events.php" class="sidebar-link active">Events</a>
            <a href="student_participation.php" class="sidebar-link">Participation</a>
        </nav>
    </aside>

    <main class="dashboard-main">
        <div class="topbar">
            <div class="topbar-left">
                <div class="topbar-title">Events</div>
            </div>
        </div>

        <div class="events-table-wrapper">
            <div class="page-title">Available Events</div>

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'success'): ?>
                    <div class="msg-box msg-success">Successfully registered for the event!</div>
                <?php elseif ($_GET['msg'] === 'already'): ?>
                    <div class="msg-box msg-already">You are already registered for this event.</div>
                <?php elseif ($_GET['msg'] === 'error'): ?>
                    <div class="msg-box msg-error">Something went wrong. Please try again.</div>
                <?php endif; ?>
            <?php endif; ?>

            <table class="events-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Venue</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Participants</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="event-row"
                            data-id="<?= $row['eventID'] ?>"
                            data-title="<?= htmlspecialchars($row['eventTitle']) ?>"
                            data-venue="<?= htmlspecialchars($row['eventVenue']) ?>"
                            data-date-start="<?= $row['eventDateStart'] ?>"
                            data-date-end="<?= $row['eventDateEnd'] ?>"
                            data-status="<?= $row['eventStatus'] ?>"
                            data-desc="<?= htmlspecialchars($row['eventDesc']) ?>">
                            <td><?= "EV" . str_pad($row['eventID'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><strong><?= htmlspecialchars($row['eventTitle']) ?></strong></td>
                            <td><?= htmlspecialchars($row['eventVenue']) ?></td>
                            <td>
                                <?php
                                $start = $row['eventDateStart'];
                                $end   = $row['eventDateEnd'];
                                if ($start === $end || empty($end)) {
                                    echo date('d M Y', strtotime($start));
                                } else {
                                    echo date('d M Y', strtotime($start)) . ' – ' . date('d M Y', strtotime($end));
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-badge <?= strtolower($row['eventStatus']) ?>">
                                    <?= $row['eventStatus'] ?>
                                </span>
                            </td>
                            <td><?= $row['eventParticipants'] ?> / <?= $row['eventMaxParticipants'] ?></td>
                            <td>
                                <?php if ($row['eventStatus'] === 'Upcoming'): ?>
                                    <button class="btn-register" onclick="openModal(this)" 
                                        data-id="<?= $row['eventID'] ?>"
                                        data-title="<?= htmlspecialchars($row['eventTitle']) ?>"
                                        data-venue="<?= htmlspecialchars($row['eventVenue']) ?>"
                                        data-date-start="<?= $row['eventDateStart'] ?>"
                                        data-date-end="<?= $row['eventDateEnd'] ?>"
                                        data-desc="<?= htmlspecialchars($row['eventDesc']) ?>">
                                        Register
                                    </button>
                                <?php else: ?>
                                    <button class="btn-register" disabled>Closed</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 40px; color: #5e6d8e;">
                                No events found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Registration Modal -->
<div class="modal-overlay" id="registerModal">
    <div class="modal-box">
        <h3 id="modal-title">Event Details</h3>
        <div class="modal-detail"><strong>Venue</strong> <span id="modal-venue"></span></div>
        <div class="modal-detail"><strong>Date</strong> <span id="modal-date"></span></div>
        <div class="modal-detail"><strong>Description</strong> <span id="modal-desc"></span></div>
        <p style="margin-top:16px; font-size:0.9rem; color:#5e6d8e;">
            Are you sure you want to register for this event?
        </p>
        <form method="POST">
            <input type="hidden" name="eventID" id="modal-event-id">
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-register">Confirm Register</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(btn) {
        const d = btn.dataset;
        const start = d.dateStart;
        const end   = d.dateEnd;
        const dateStr = (start === end || !end)
            ? new Date(start).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'})
            : new Date(start).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'})
              + ' – ' +
              new Date(end).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'});

        document.getElementById('modal-title').textContent    = d.title;
        document.getElementById('modal-venue').textContent    = d.venue;
        document.getElementById('modal-date').textContent     = dateStr;
        document.getElementById('modal-desc').textContent     = d.desc;
        document.getElementById('modal-event-id').value       = d.id;

        document.getElementById('registerModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('registerModal').style.display = 'none';
    }

    document.getElementById('registerModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
</body>
</html>