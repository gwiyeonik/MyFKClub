<?php
// student_myclubs.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

$userID = intval($_SESSION['user_id']);
$clubList = [];
$stmt = mysqli_prepare($link, "SELECT c.clubID, c.clubName FROM clubmembership m JOIN club c ON m.clubID = c.clubID WHERE m.userID = ? ORDER BY c.clubName ASC");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $clubList[] = $row;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Clubs | Student</title>
  <link rel="stylesheet" href="../CSS/student_clublist.css">
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>
      <nav class="sidebar-nav">
        <a href="myProfile.php" class="sidebar-link">My Profile</a>
        <a href="student_myclubs.php" class="sidebar-link active">My Clubs</a>
        <a href="student_clublist.php" class="sidebar-link">Club List</a>
        <a href="student_events.php" class="sidebar-link">Events</a>
        <a href="student_participation.php" class="sidebar-link">Participation</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left"><div class="topbar-title">My Clubs</div></div>
      </div>

      <div class="clublist-area">
        <section class="club-list-container">
          <div class="main-card">
            <div class="top-row">
              <div class="club-details">
                <div class="input-group">

                <?php if (count($clubList) > 0): ?>

                    <label><strong>Select your club</strong></label>
                    <div style="position: relative; display: inline-block; width: 100%;">
                        <input
                            id="club-list-input"
                            list="club-options-list"
                            name="club-selection"
                            class="pill-search-input"
                            placeholder="Select your club..."
                            style="padding-right: 40px; width: 100%; box-sizing: border-box;"
                        >
                        <button id="clear-btn" type="button" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 20px; cursor: pointer; color: #999; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; pointer-events: auto;">✕</button>
                    </div>
                    <datalist id="club-options-list">
                        <?php foreach ($clubList as $club): ?>
                            <option value="<?= htmlspecialchars(sprintf('CB%04d - %s', $club['clubID'], $club['clubName'])) ?>">
                        <?php endforeach; ?>
                    </datalist>

                <?php else: ?>

                    <div class="no-club-message">
                        Please join clubs first.
                    </div>

                <?php endif; ?>

                </div>
                <div class="info-field"><strong>Club Desc</strong><p id="list-club-desc" class="info-value">Select a club to view details</p></div>
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
                        <th>userID</th>
                        <th>userName</th>
                        <th>committeePosition</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr><td colspan="3" class="empty-cell">Select a club to load committee members.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="top-row">
              <div class="committee-card">
                <h3>Club Members</h3>
                <div class="committee-content" id="list-members-content">
                  <table class="committee-table">
                    <thead>
                      <tr>
                        <th>membershipID</th>
                        <th>userName</th>
                        <th>clubJoinDate</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr><td colspan="3" class="empty-cell">Select a club to load members.</td></tr>
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
                      <td colspan="6" class="empty-cell">Select a club to load events.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="club-actions">
                <button type="button" class="btn-unjoin" onclick="unjoinClub()">
                    Unjoin Club
                </button>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

<script>
function fetchClubDetails(clubID) {
    if (!clubID) return;
    fetch(`student_myclubs_api.php?action=get_club_details&clubID=${encodeURIComponent(clubID)}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const club = data.club || {};
        document.getElementById('list-club-desc').textContent = club.clubDesc || 'Not available yet';
        document.getElementById('list-club-advisor').textContent = club.clubAdvisor || 'Not available yet';
        document.getElementById('list-club-status').textContent = club.clubStatus || 'Not available yet';
        document.getElementById('list-club-created').textContent = club.clubCreated || 'Not available yet';
    })
    .catch(err => console.error(err));
}

function loadClubMembers(clubID) {
    const container = document.getElementById('list-members-content');
    if (!clubID) return;
    fetch(`student_myclubs_api.php?action=get_club_members&clubID=${encodeURIComponent(clubID)}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.members || data.members.length === 0) {
            container.innerHTML = '<div class="empty-cell">No club members found.</div>';
            return;
        }
        let html = '<table class="committee-table"><thead><tr><th>Membership ID</th><th>User Name</th><th>Join Date</th></tr></thead><tbody>';
        data.members.forEach(member => {
            html += `<tr><td>${formatID('MM', member.membershipID)}</td><td>${member.userName}</td><td>${member.clubJoinDate || ''}</td></tr>`;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    })
    .catch(err => console.error(err));
}

function unjoinClub() {

    const input = document.getElementById('club-list-input').value;

    if (!input) {
        alert('Please select a club first.');
        return;
    }

    const clubID = parseClubID(input);

    if (!clubID) {
        alert('Invalid club selected.');
        return;
    }

    if (!confirm('Are you sure you want to unjoin this club?')) {
        return;
    }

    const fd = new FormData();
    fd.append('clubID', clubID);

fetch('student_myclubs_api.php?action=unjoin_club', {
    method: 'POST',
    body: fd
})
.then(response => response.text())
.then(text => {

    console.log(text);

    let data;

    try {
        data = JSON.parse(text);
    }
    catch(e) {
        alert('Invalid JSON response. Check PHP errors.');
        return;
    }

    alert(data.message);

    if (data.success) {

        document.getElementById('club-list-input').value = '';

        document.getElementById('list-club-desc').textContent =
            'Select a club to view details';

        document.getElementById('list-club-advisor').textContent =
            'Select a club to view details';

        document.getElementById('list-club-status').textContent =
            'Select a club to view details';

        document.getElementById('list-club-created').textContent =
            'Select a club to view details';

        document.getElementById('list-committee-content').innerHTML =
            '<div class="empty-cell">Select a club to load committee members.</div>';

        document.getElementById('list-members-content').innerHTML =
            '<div class="empty-cell">Select a club to load members.</div>';

        document.getElementById('event-table-body').innerHTML =
            '<tr><td colspan="6" class="empty-cell">Select a club to load events.</td></tr>';

        location.reload();
    }
})
.catch(err => {
    console.error(err);
    alert('Failed to unjoin club.');
});
}

function loadClubCommittee(clubID) {
    const container = document.getElementById('list-committee-content');
    if (!clubID) return;
    fetch(`student_myclubs_api.php?action=get_club_committee&clubID=${encodeURIComponent(clubID)}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.committee || data.committee.length === 0) {
            container.innerHTML = '<div class="empty-cell">No committee members found.</div>';
            return;
        }
        let html = '<table class="committee-table"><thead><tr><th>User ID</th><th>User Name</th><th>Committee Position</th></tr></thead><tbody>';
        data.committee.forEach(member => {
            html += `<tr><td>${formatID('US', member.userID)}</td><td>${member.userName}</td><td>${member.committeePosition || ''}</td></tr>`;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    })
    .catch(err => console.error(err));
}

function loadEventsForClub(clubID) {
    const tbody = document.getElementById('event-table-body');
    tbody.innerHTML = '';
    if (!clubID) {
        tbody.innerHTML = '<tr><td colspan="6" class="empty-cell">Select a club to load events.</td></tr>';
        return;
    }
    fetch(`student_myclubs_api.php?action=get_club_events&clubID=${encodeURIComponent(clubID)}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.events || data.events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="empty-cell">No events found.</td></tr>';
            return;
        }
        let html = '';
        data.events.forEach(event => {
            const start = event.eventDateStart || '';
            const end = event.eventDateEnd || '';
            const eventDate = (start === end || !end) ? formatDate(start) : `${formatDate(start)} - ${formatDate(end)}`;
            html += `<tr><td>${formatID('EV', event.eventID)}</td><td><strong>${event.eventTitle}</strong></td><td>${event.eventVenue || ''}</td><td>${eventDate}</td><td><span class="status-badge ${event.eventStatus.toLowerCase()}">${event.eventStatus}</span></td><td>${event.eventParticipants || 0} / ${event.eventMaxParticipants || 0}</td></tr>`;
        });
        tbody.innerHTML = html;
    })
    .catch(err => {
        console.error(err);
        tbody.innerHTML = '<tr><td colspan="6" class="empty-cell">Unable to load events.</td></tr>';
    });
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
    input.addEventListener('change', function () {
        const val = this.value || '';
        const id = parseClubID(val);
        if (id) {
            fetchClubDetails(id);
            loadClubMembers(id);
            loadClubCommittee(id);
            loadEventsForClub(id);
        }
    });
    
    const clearBtn = document.getElementById('clear-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            document.getElementById('list-club-desc').textContent = 'Select a club to view details';
            document.getElementById('list-club-advisor').textContent = 'Select a club to view details';
            document.getElementById('list-club-status').textContent = 'Select a club to view details';
            document.getElementById('list-club-created').textContent = 'Select a club to view details';
            document.getElementById('list-committee-content').innerHTML = '<div class="empty-cell">Select a club to load committee members.</div>';
            document.getElementById('event-table-body').innerHTML = '<tr><td colspan="6" class="empty-cell">Select a club to load events.</td></tr>';
        });
    }
});
</script>
</body>
</html>
