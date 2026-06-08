<?php
// committee_view_clubs.php
session_start();

// Basic DB connection
$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

$clubList = [];
$res = mysqli_query($link, "SELECT clubID, clubName FROM club ORDER BY clubName ASC");
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) $clubList[] = $r;
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Clubs | Committee</title>
    <link rel="stylesheet" href="../CSS/student_clubs.css">
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>
     <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<nav class="sidebar-nav">

    <a href="committee_dashboard.php"
       class="sidebar-link <?php echo $currentPage == 'committee_dashboard.php' ? 'active' : ''; ?>">
       Home
    </a>

    <a href="committee_view_clubs.php"
       class="sidebar-link <?php echo $currentPage == 'committee_view_clubs.php' ? 'active' : ''; ?>">
       View Clubs
    </a>

    <a href="committee_manage_events.php"
       class="sidebar-link <?php echo $currentPage == 'committee_manage_events.php' ? 'active' : ''; ?>">
       Manage Events
    </a>

    <a href="committee_attendance_report.php"
       class="sidebar-link <?php echo $currentPage == 'committee_attendance_report.php' ? 'active' : ''; ?>">
       Attendance
    </a>

    <a href="committee_participation_report.php"
       class="sidebar-link <?php echo $currentPage == 'committee_participation_report.php' ? 'active' : ''; ?>">
       Reports
    </a>

</nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left"><div class="topbar-title">View Clubs</div></div>
        
      </div>

      <div class="event-area">

        <section class="club-list-container">
              <h1 class="page-title">List of Clubs</h1>
              
              <div class="main-card">
                  <div class="top-row">
                      <div class="club-details">
                          <div class="input-group">
                              <label><strong>Club Name/Club ID</strong></label>
                              <div style="position: relative; display: inline-block; width: 100%;">
                                  <input 
                                      id="club-list-input"
                                      list="club-options-list"
                                      name="club-selection"
                                      class="pill-search-input"
                                      placeholder="Search or select a club..."
                                      style="padding-right: 40px; width: 100%; box-sizing: border-box;"
                                  >
                                  <button id="clear-btn" type="button" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 20px; cursor: pointer; color: #999; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; pointer-events: auto;">✕</button>
                              </div>
                              <datalist id="club-options-list">
                                  <?php foreach ($clubList as $club): ?>
                                      <option value="<?= htmlspecialchars(sprintf('CB%04d - %s', $club['clubID'], $club['clubName'])) ?>">
                                  <?php endforeach; ?>
                              </datalist>
                          </div>
                          <div class="info-field"><strong>Club Description</strong><p id="list-club-desc" class="info-value">Select a club to view details</p></div>
                          <div class="info-field"><strong>Club Advisor</strong><p id="list-club-advisor" class="info-value">Select a club to view details</p></div>
                          <div class="info-field"><strong>Club Status</strong><p id="list-club-status" class="info-value">Select a club to view details</p></div>
                          <div class="info-field"><strong>Date Created</strong><p id="list-club-created" class="info-value">Select a club to view details</p></div>
                      </div>

                      <div class="committee-card">
                          <h3>Club Committee</h3>
                          <div class="committee-content" id="list-committee-content">
                              <table class="committee-table">
                                  <thead>
                                      <tr>
                                          <th>User ID</th>
                                          <th>User Name</th>
                                          <th>Committee Position</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <tr><td colspan="3" class="empty-cell">Select a club to load committee members.</td></tr>
                                  </tbody>
                              </table>
                          </div>
                      </div>
                  </div>

                <div class="chart-card">
                    <div class="chart-title">Club Events</div>

                    <div class="table-wrapper">
                        <table class="event-data-table">
                            <thead>
                                <tr>
                                    <th>Event ID</th>
                                    <th>Event Title</th>
                                    <th>Event Venue</th>
                                    <th>Event Date</th>
                                    <th>Event Status</th>
                                    <th>Participants</th>
                                </tr>
                            </thead>
                            <tbody id="event-table-body">
                                <tr>
                                    <td colspan="6" class="empty-cell">
                                        Select a club to load events.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

              </div>
          </section>

      </div>
    </main>
  </div>

<script>
function fetchClubDetails(clubKey) {
    if (!clubKey) return;
    fetch(`admin_student_clubs_api.php?action=club_details&clubKey=${encodeURIComponent(clubKey)}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const club = data.club || {};
        document.getElementById('list-club-desc').textContent = club.clubDesc || 'Not available yet';
        document.getElementById('list-club-advisor').textContent = club.clubAdvisor || 'Not available yet';
        document.getElementById('list-club-status').textContent = club.clubStatus || 'Not available yet';
        document.getElementById('list-club-created').textContent = club.clubCreated || 'Not available yet';

        // committee
        const listCommittee = document.getElementById('list-committee-content');
        if (data.committee && data.committee.length) {
            let tbl = '<table class="committee-table"><thead><tr><th>User ID</th><th>User Name</th><th>Committee Position</th></tr></thead><tbody>';
            data.committee.forEach(member => {
                tbl += `<tr><td>${formatID('US', member.userID) || ''}</td><td>${member.userName || ''}</td><td>${member.committeePosition || ''}</td></tr>`;
            });
            tbl += '</tbody></table>';
            listCommittee.innerHTML = tbl;
        } else {
            listCommittee.innerHTML = '<div class="empty-cell">No committee members found.</div>';
        }
    })
    .catch(err => console.error(err));
}

function loadEventsForClub(clubID) {
    if (!clubID) return;
    fetch(`admin_student_clubs_api.php?action=get_club_events&clubID=${encodeURIComponent(clubID)}`)
    .then(r => r.json())
    .then(data => {
        const tbody = document.getElementById('event-table-body');
        tbody.innerHTML = '';
        if (!data.success || !data.events || data.events.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="empty-cell">No events found.</td></tr>`;
            return;
        }
        data.events.forEach(event => {
            const start = event.eventDateStart || '';
            const end = event.eventDateEnd || '';
            const eventDate = (start === end || !end) ? formatDate(start) : `${formatDate(start)} - ${formatDate(end)}`;
            const row = `
                <tr>
                    <td>${formatID('EV', event.eventID)}</td>
                    <td><strong>${event.eventTitle}</strong></td>
                    <td>${event.eventVenue || ''}</td>
                    <td>${eventDate}</td>
                    <td><span class="status-badge ${event.eventStatus.toLowerCase()}">${event.eventStatus}</span></td>
                    <td>${event.eventParticipants || 0} / ${event.eventMaxParticipants || 0}</td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    })
    .catch(err => console.error(err));
}

function formatDate(d) {
    if (!d) return '';
    const dt = new Date(d);
    return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatID(prefix, id) {
    if (id === null || id === undefined || id === '') return '';
    const num = parseInt(String(id).replace(/\D+/g, ''), 10);
    if (Number.isNaN(num)) return String(id);
    return prefix + String(num).padStart(4, '0');
}

function parseClubID(value) {
    if (!value) return '';
    const cleaned = String(value).trim();
    let match = cleaned.match(/^CB0*(\d+)\s*(?:-|$)/i);
    if (match) return match[1];
    match = cleaned.match(/^0*(\d+)\s*(?:-|$)/);
    if (match) return match[1];
    return '';
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('club-list-input');
    const clearBtn = document.getElementById('clear-btn');
    
    input.addEventListener('change', function () {
        const val = this.value || '';
        const id = parseClubID(val);
        if (id) {
            fetchClubDetails(id);
            loadEventsForClub(id);
        }
    });
    
    clearBtn.addEventListener('click', function () {
        input.value = '';
        document.getElementById('list-club-desc').textContent = 'Select a club to view details';
        document.getElementById('list-club-advisor').textContent = 'Select a club to view details';
        document.getElementById('list-club-status').textContent = 'Select a club to view details';
        document.getElementById('list-club-created').textContent = 'Select a club to view details';
        document.getElementById('list-committee-content').innerHTML = '<div class="empty-cell">Select a club to load committee members.</div>';
        document.getElementById('event-table-body').innerHTML = '<tr><td colspan="6" class="empty-cell">Select a club to load events.</td></tr>';
    });
});
</script>
</body>
</html>
