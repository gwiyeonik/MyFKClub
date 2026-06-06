<?php
// student_clublist.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
  <title>Club List | Student</title>
  <link rel="stylesheet" href="../CSS/student_clublist.css">
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>
      <nav class="sidebar-nav">
            <a href="student_dashboard.php" class="sidebar-link">Home</a>
            <a href="student_myclubs.php" class="sidebar-link">My Clubs</a>
            <a href="student_clublist.php" class="sidebar-link active">Club List</a>
            <a href="student_events.php" class="sidebar-link">Events</a>
            <a href="student_participation.php" class="sidebar-link">Participation</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar">
        <div class="topbar-left"><div class="topbar-title">List of Clubs</div></div>
      </div>

      <div class="clublist-area">

        <section class="club-list-container">
              <div class="main-card">
                  <div class="top-row">
                      <div class="club-details">
                          <div class="input-group">
                              <label><strong>Club Name/Club ID</strong></label>
                                  <input 
                                      id="club-list-input"
                                      list="club-options-list"
                                      name="club-selection"
                                      class="pill-search-input"
                                      placeholder="Search or select a club..."
                                  >
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
                <div class="chart-action-row">
                    <button id="joinClubBtn" class="btn-add">Join Club</button>
                </div>
                <div id="membership-result" class="membership-result"></div>

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
    if (!id && id !== 0) return '';
    const num = parseInt(String(id).replace(/[^0-9]/g, ''), 10);
    if (Number.isNaN(num)) return String(id);
    return prefix + String(num).padStart(4, '0');
}

document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('club-list-input');
    input.addEventListener('change', function () {
        const val = this.value || '';
        let id = null;
        const m = val.match(/^(?:CB\s*0*)?(\d+)\s*-/i);
        if (m) id = m[1];
        else {
            const m2 = val.match(/^(?:CB\s*0*)?(\d+)\b/i);
            if (m2) id = m2[1];
        }
        if (id) {
            fetchClubDetails(id);
            loadEventsForClub(id);
        }
    });

    const joinBtn = document.getElementById('joinClubBtn');
    const membershipResult = document.getElementById('membership-result');

    function getSelectedClubID() {
        const val = input.value || '';
        const m = val.match(/^(?:CB\s*0*)?(\d+)\s*-/i);
        if (m) return m[1];
        const m2 = val.match(/^(?:CB\s*0*)?(\d+)\b/i);
        return m2 ? m2[1] : null;
    }

    if (joinBtn) {
        joinBtn.addEventListener('click', () => {
            const clubID = getSelectedClubID();
            if (!clubID) {
                alert('Please select a club first.');
                return;
            }

            fetch('student_clublist_api.php?action=join_club', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ clubID })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    membershipResult.textContent = data.error || 'Unable to join this club.';
                    membershipResult.style.color = '#b82a1f';
                    return;
                }
                if (data.message && data.message.toLowerCase().includes('already')) {
                    membershipResult.textContent = `You already joined this club. Membership ID: ${formatID('MM', data.membershipID)} | Joined: ${data.clubJoinDate || 'N/A'}`;
                    membershipResult.style.color = '#b82a1f';
                } else {
                    membershipResult.textContent = `Joined club! Membership ID: ${formatID('MM', data.membershipID)} | Joined: ${data.clubJoinDate || 'N/A'}`;
                    membershipResult.style.color = '#006f6f';
                }
            })
            .catch(err => {
                console.error(err);
                membershipResult.textContent = 'Network error when joining club.';
                membershipResult.style.color = '#b82a1f';
            });
        });
    }
});
</script>
</body>
</html>
