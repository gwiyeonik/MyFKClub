<?php
// committee_manage_events.php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "myfkclub"; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// FETCH EVENTS FOR DROPDOWN
$eventList = [];
$eventQuery = "SELECT eventID, eventTitle FROM event ORDER BY eventTitle ASC";

// FETCH CLUBS FOR DROPDOWN
$clubList = [];
$clubQuery = "SELECT clubID, clubName FROM club ORDER BY clubName ASC";
$clubResult = $conn->query($clubQuery);
if ($clubResult && $clubResult->num_rows > 0) {
    while($row = $clubResult->fetch_assoc()) {
        $clubList[] = $row;
    }
}

$idQuery = "SHOW TABLE STATUS LIKE 'event'";
$idResult = $conn->query($idQuery);
$idRow = $idResult->fetch_assoc();
$nextID = $idRow['Auto_increment'];
$formattedEventID = "EV" . str_pad($nextID, 4, '0', STR_PAD_LEFT);

$eventResult = $conn->query($eventQuery);
$sql = "SELECT * FROM event ORDER BY eventDateStart ASC";
$result = $conn->query($sql);

if ($eventResult && $eventResult->num_rows > 0) {
    while($row = $eventResult->fetch_assoc()) {
        $eventList[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Clubs | MyFKClub committee</title>
  <link rel="stylesheet" href="../CSS/committee_events.css">
  <style>
    /* Quick alignment for the new section */
    .add-event-panel { margin-top: 20px; padding: 20px; }
    .generated-text { color: #888; font-style: italic; padding: 10px; display: block; }
    .add-event-panel .form-field input[type="text"],
    .add-event-panel .form-field input[type="number"],
    .add-event-panel .form-field input[type="date"],
    .add-event-panel .form-field input[type="file"],
    .add-event-panel .form-field textarea {
        width: 100%;
        box-sizing: border-box;
    }
    .club-info-panel .form-field input[type="text"],
    .club-info-panel .form-field input[type="date"],
    .club-info-panel .form-field input[type="file"],
    .club-info-panel .form-field select {
        width: 100%;
        box-sizing: border-box;
    }
  </style>
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>
      <nav class="sidebar-nav">
        <a href="committee_dashboard.php" class="sidebar-link">Home</a>
        <a href="committee_view_clubs.php" class="sidebar-link">View Clubs</a>
        <a href="committee_manage_events.php" class="sidebar-link active">Manage Events</a>
        <a href="committee_manage_members.php" class="sidebar-link">Members</a>
        <a href="committee_attendance.php" class="sidebar-link">Attendance</a>
        <a href="committee_participation_report.php" class="sidebar-link">Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left"><div class="topbar-title">Manage Events</div></div>
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

      <div class="content-area">
        <section class="manage-grid">
          <div class="left-column">
          <div class="chart-card">
              <div class="chart-title">Event List</div>
              
              <div class="table-wrapper">
                  <table class="event-data-table">
                      <thead>
                          <tr>
                              <th>ID</th>
                              <th>Title</th>
                              <th>Venue</th>
                              <th>Date</th>
                              <th>Status</th>
                              <th>Participants</th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php if ($result->num_rows > 0): ?>
                              <?php while($row = $result->fetch_assoc()): ?>
                              <tr class="event-row"
                                data-id="<?= $row['eventID'] ?>"
                                data-title="<?= htmlspecialchars($row['eventTitle']) ?>"
                                data-venue="<?= htmlspecialchars($row['eventVenue']) ?>"
                                data-date="<?= $row['eventDateStart'] ?>"
                                data-date-start="<?= $row['eventDateStart'] ?>"
                                data-date-end="<?= $row['eventDateEnd'] ?>"
                                data-status="<?= $row['eventStatus'] ?>"
                                data-participants="<?= $row['eventParticipants'] ?>"
                                data-max="<?= $row['eventMaxParticipants'] ?>"
                                data-desc="<?= htmlspecialchars($row['eventDesc']) ?>"
                                style="cursor: pointer;">
                                <td><?php echo $row['eventID']; ?></td>
                                <td><strong><?php echo $row['eventTitle']; ?></strong></td>
                                <td><?php echo $row['eventVenue']; ?></td>
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
                                    <span class="status-badge <?php echo strtolower($row['eventStatus']); ?>">
                                        <?php echo $row['eventStatus']; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['eventParticipants']; ?> / <?php echo $row['eventMaxParticipants']; ?></td>
                              </tr>
                              <?php endwhile; ?>
                          <?php else: ?>
                              <tr>
                                  <td colspan="6" class="empty-cell">No events found.</td>
                              </tr>
                          <?php endif; ?>
                      </tbody>
                  </table>
              </div>
          </div>

            <section id="add-section" class="chart-card add-event-panel">
              <div class="chart-title">Add New Event</div>
              <form action="save_event.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                  <div class="form-field">
                    <label>Event ID </label>
                    <input type="text" value="<?= $formattedEventID ?>" readonly>
                    <input type="hidden" name="eventID" value="<?= $nextID ?>">
                  </div>
                  <div class="form-field">
                    <label>Club</label>
                    <select name="clubID" required>
                      <option value="">-- Select a Club --</option>
                      <?php foreach ($clubList as $club): ?>
                        <option value="<?php echo $club['clubID']; ?>">
                          <?php echo htmlspecialchars($club['clubName']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-field">
                    <label>Event Title</label>
                    <input type="text" name="eventTitle" required>
                  </div>
                  <div class="form-field">
                      <label>Event Description</label>
                      <input type="text" name="eventDesc">
                  </div>
                  <div class="form-field">
                    <label>Event Venue</label>
                    <input type="text" name="eventVenue">
                  </div>
                  <div class="form-field">
                    <label>Event Date</label>
                    <div style="display: flex; gap: 15px; margin-bottom: 8px; align-items: center;">
                        <label class="radio-label" style="white-space: nowrap;">
                            <input type="radio" name="date_type" value="single" checked onchange="toggleDateType()"> Single Day
                        </label>
                        <label class="radio-label" style="white-space: nowrap;">
                            <input type="radio" name="date_type" value="range" onchange="toggleDateType()"> Multiple Days
                        </label>
                    </div>
                    <div id="single-date">
                        <input type="date" name="eventDateStart" id="singleEventDate" required>
                    </div>
                    <div id="range-date" style="display:none; gap:10px; align-items:center; width:100%;">
                        <input type="date" name="eventDateStartRange" id="rangeEventDateStart">
                        <span>to</span>
                        <input type="date" name="eventDateEnd" id="eventDateEnd">
                    </div>
                  </div>
                  <div class="form-field">
                    <label>Upload Photo</label>
                    <input type="file" name="eventProfile">
                  </div>
                  <div class="action-buttons" style="grid-column: span 2; justify-content: flex-end; display: flex; gap: 10px;">
                    <button type="reset" class="secondary-pill">Cancel</button>
                    <button type="submit" class="primary-pill" style="bac kground-color: #00a896;">Save Event</button>
                  </div>
                </div>
              </form>
            </section>
          </div>

          <section class="club-info-panel">
            <h3 class="section-title">Event Information</h3>
            
            <form action="update_event.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid club-info-grid">
                  
                  <div class="form-field">
                    <label for="event-select">Event ID Name</label>
                    <select id="event-select" class="club-select" name="event_id">
                      <option value="">-- Select an Event --</option>
                      <?php
                        // Populate the dropdown from DB
                        $res = $conn->query("SELECT eventID, eventTitle FROM event");
                        while($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['eventID']}'>{$row['eventID']} - {$row['eventTitle']}</option>";
                      }
                      ?>
                    </select>
                  </div>

                  <div class="form-field">
                    <label for="info-event-name">Event Name</label>
                    <input id="info-event-name" type="text" name="event_title">
                  </div>

                  <div class="form-field">
                    <label for="info-event-desc">Event Description</label>
                    <input id="info-event-desc" type="text" name="event_desc">
                  </div>

                  <div class="form-field">
                    <label for="info-event-venue">Event Venue</label>
                    <input id="info-event-venue" type="text" name="event_venue">
                  </div>

                  <div class="form-field">
                    <label>Event Status</label>
                    <div class="role-row">
                        <label class="radio-label"><input type="radio" name="event_status" value="Upcoming"> Upcoming</label>
                        <label class="radio-label"><input type="radio" name="event_status" value="Completed"> Completed</label>
                    </div>
                  </div>

                  <div class="form-field">
                    <label>Event Date</label>
                    <div style="display: flex; gap: 15px; margin-bottom: 8px; align-items: center;">
                        <label class="radio-label" style="white-space: nowrap;">
                            <input type="radio" name="update_date_type" value="single" checked onchange="toggleUpdateDateType()"> Single Day
                        </label>
                        <label class="radio-label" style="white-space: nowrap;">
                            <input type="radio" name="update_date_type" value="range" onchange="toggleUpdateDateType()"> Multiple Days
                        </label>
                    </div>
                    <div id="update-single-date">
                        <input id="info-event-date" type="date" name="event_date" style="width:100%; box-sizing:border-box;">
                    </div>
                    <div id="update-range-date"
                        style="display:none; gap:10px; align-items:center; width:100%;">
                        <input id="info-event-date-start"type="date"name="event_date_start"style="flex:1; width:auto; box-sizing:border-box;">
                        <span>to</span>
                        <input id="info-event-date-end" type="date" name="event_date_end"style="flex:1; width:auto; box-sizing:border-box;">
                    </div>
                  </div>

                  <div class="form-field file-field">
                    <label>Upload Photo</label>
                    <input type="file" name="event_photo" accept="image/*">
                  </div>

                  <div class="action-buttons">
                    <button type="button" class="btn-delete" onclick="confirmDelete()">Delete</button>
                    <button type="submit" class="btn-update">Update</button>
                  </div>
                </div>
            </form>
          </section>
        </section>
      </div>
    </main>
  </div>
  <div id="eventModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Event Details</h2>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="detail-item"><strong>Event ID:</strong> <span id="modalID"></span></div>
            <div class="detail-item"><strong>Venue:</strong> <span id="modalVenue"></span></div>
            <div class="detail-item"><strong>Date:</strong> <span id="modalDate"></span></div>
            <div class="detail-item" id="modalDateEndRow"><strong>End Date:</strong> <span id="modalDateEnd"></span></div>
            <div class="detail-item"><strong>Status:</strong> <span id="modalStatus"></span></div>
            <div class="detail-item"><strong>Participants:</strong> <span id="modalParticipants"></span></div>
            <div class="detail-item"><strong>Description:</strong></div>
            <p id="modalDesc" class="modal-description"></p>
        </div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.event-row').forEach(row => {
        row.addEventListener('click', function () {
            const d = this.dataset;

            // Fill the modal
            document.getElementById('modalTitle').textContent        = d.title;
            document.getElementById('modalID').textContent           = d.id;
            document.getElementById('modalVenue').textContent        = d.venue;
            const isSingleDay = d.dateStart === d.dateEnd || !d.dateEnd;
            if (isSingleDay) {
                document.getElementById('modalDate').textContent    = new Date(d.dateStart || d.date)
                    .toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                document.getElementById('modalDateEndRow').style.display = 'none';
            } else {
                document.getElementById('modalDate').textContent    = new Date(d.dateStart)
                    .toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                document.getElementById('modalDateEnd').textContent = new Date(d.dateEnd)
                    .toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                document.getElementById('modalDateEndRow').style.display = 'block';
            }
            document.getElementById('modalStatus').textContent       = d.status;
            document.getElementById('modalParticipants').textContent = d.participants + ' / ' + d.max;
            document.getElementById('modalDesc').textContent         = d.desc;

            document.getElementById('eventModal').style.display = 'flex';

            // Fill the Event Information panel on the right
            document.getElementById('event-select').value     = d.id;
            document.getElementById('info-event-name').value  = d.title;
            document.getElementById('info-event-desc').value  = d.desc;
            document.getElementById('info-event-venue').value = d.venue;
            // Auto-switch date type based on data
            const isSameDate = d.dateStart === d.dateEnd || !d.dateEnd;

            if (isSameDate) {
                document.querySelector('input[name="update_date_type"][value="single"]').checked = true;
                toggleUpdateDateType();
                document.getElementById('info-event-date').value = d.dateStart || d.date;
            } else {
                document.querySelector('input[name="update_date_type"][value="range"]').checked = true;
                toggleUpdateDateType();
                document.getElementById('info-event-date-start').value = d.dateStart;
                document.getElementById('info-event-date-end').value   = d.dateEnd;
            }

            // Auto-select the correct status radio button
            document.querySelectorAll('input[name="event_status"]').forEach(radio => {
                radio.checked = (radio.value === d.status);
            });
        });
    });

    document.getElementById('event-select').addEventListener('change', function () {
    const eventID = this.value;
    if (eventID === '') return;

    fetch('get_event.php?id=' + eventID)
    .then(response => response.json())
    .then(data => {
        document.getElementById('info-event-name').value  = data.eventTitle;
        document.getElementById('info-event-desc').value  = data.eventDesc;
        document.getElementById('info-event-venue').value = data.eventVenue;

        const isSameDate = data.eventDateStart === data.eventDateEnd || !data.eventDateEnd;

        if (isSameDate) {
            document.querySelector('input[name="update_date_type"][value="single"]').checked = true;
            toggleUpdateDateType();
            document.getElementById('info-event-date').value = data.eventDateStart || data.eventDate;
        } else {
            document.querySelector('input[name="update_date_type"][value="range"]').checked = true;
            toggleUpdateDateType();
            document.getElementById('info-event-date-start').value = data.eventDateStart;
            document.getElementById('info-event-date-end').value   = data.eventDateEnd;
        }

        document.querySelectorAll('input[name="event_status"]').forEach(radio => {
            radio.checked = (radio.value === data.eventStatus);
        });
      });
    });

    function closeModal() {
        document.getElementById('eventModal').style.display = 'none';
    }

    document.getElementById('eventModal').addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });

    function confirmDelete() {
        const eventID = document.querySelector('#event-select').value;

        if (eventID === '') {
            alert('Please select an event first.');
            return;
        }

        if (confirm('Are you sure you want to delete this event?')) {
            window.location.href = 'delete_event.php?id=' + eventID;
        }
    }


    function toggleDateType() {
    const type = document.querySelector('input[name="date_type"]:checked').value;

    const single = document.getElementById('single-date');
    const range = document.getElementById('range-date');

    const singleDate = document.getElementById('singleEventDate');
    const rangeStart = document.getElementById('rangeEventDateStart');
    const rangeEnd = document.getElementById('eventDateEnd');

    if (type === 'single') {
        single.style.display = 'block';
        range.style.display = 'none';

        singleDate.required = true;
        rangeStart.required = false;
        rangeEnd.required = false;

        rangeStart.value = '';
        rangeEnd.value = '';
    } else {
        single.style.display = 'none';
        range.style.display = 'flex';

        singleDate.required = false;
        rangeStart.required = true;
        rangeEnd.required = true;

        singleDate.value = '';
    }
}

    function toggleUpdateDateType() {
    const type = document.querySelector('input[name="update_date_type"]:checked').value;

    const single = document.getElementById('update-single-date');
    const range = document.getElementById('update-range-date');

    const singleDate = document.getElementById('info-event-date');
    const rangeStart = document.getElementById('info-event-date-start');
    const rangeEnd = document.getElementById('info-event-date-end');

    if (type === 'single') {
        single.style.display = 'block';
        range.style.display = 'none';

        singleDate.required = true;
        rangeStart.required = false;
        rangeEnd.required = false;

        rangeStart.value = '';
        rangeEnd.value = '';
    } else {
        single.style.display = 'none';
        range.style.display = 'flex';

        singleDate.required = false;
        rangeStart.required = true;
        rangeEnd.required = true;

        singleDate.value = '';
    }
}

// Initialize both forms when page loads
    document.addEventListener('DOMContentLoaded', function () {
    toggleDateType();
    toggleUpdateDateType();

    document.querySelectorAll('input[type="date"]').forEach(dateInput => {
        dateInput.addEventListener('click', function () {
            if (this.showPicker) this.showPicker();
        });
    });
});
</script>


</body>
</html>

