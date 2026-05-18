<?php
// admin_student_clubs.php
session_start();

$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
<!-- fetch and populate the left "list" panel (description, committee, events) -->
<script>
function fetchClubListPanel(clubKey) {

    if (!clubKey) return;

    fetch(`admin_student_clubs_api.php?action=club_details&clubKey=${encodeURIComponent(clubKey)}`)

    .then(response => response.json())

    .then(data => {

        if (!data.success) {
            console.error(data.message);
            return;
        }

        const club = data.club || {};

        // =====================================
        // CLUB DETAILS
        // =====================================

        const listDesc =
            document.getElementById('list-club-desc');

        const listAdvisor =
            document.getElementById('list-club-advisor');

        const listStatus =
            document.getElementById('list-club-status');

        const listCreated =
            document.getElementById('list-club-created');

        if (listDesc) {
            listDesc.textContent =
                club.clubDesc || 'Not available yet';
        }

        if (listAdvisor) {
            listAdvisor.textContent =
                club.clubAdvisor || 'Not available yet';
        }

        if (listStatus) {
            listStatus.textContent =
                club.clubStatus || 'Not available yet';
        }

        if (listCreated) {
            listCreated.textContent =
                club.clubCreated || 'Not available yet';
        }

        // =====================================
        // COMMITTEE TABLE
        // =====================================

        const listCommittee =
            document.getElementById('list-committee-content');

        if (listCommittee) {

            if (data.committee && data.committee.length > 0) {

                let tableHTML = `
                    <table class="committee-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>User Name</th>
                                <th>Committee Position</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.committee.forEach(member => {

                    tableHTML += `
                        <tr>
                            <td>${member.userID || ''}</td>
                            <td>${member.userName || ''}</td>
                            <td>${member.committeePosition || ''}</td>
                        </tr>
                    `;
                });

                tableHTML += `
                        </tbody>
                    </table>
                `;

                listCommittee.innerHTML = tableHTML;
            }
            else {

                listCommittee.innerHTML = `
                    <div class="empty-cell">
                        No committee members found.
                    </div>
                `;
            }
        }

        // =====================================
        // EVENT TABLE
        // =====================================

        fetch(`admin_student_clubs_api.php?action=get_club_events&clubID=${encodeURIComponent(club.clubID)}`)

        .then(response => response.json())

        .then(eventData => {

            const tbody =
                document.getElementById('event-table-body');

            if (!tbody) return;

            tbody.innerHTML = '';

            if (
                !eventData.success ||
                !eventData.events ||
                eventData.events.length === 0
            ) {

                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-cell">
                            No events found.
                        </td>
                    </tr>
                `;

                return;
            }

            eventData.events.forEach(event => {

                let eventDate = '';

                const start = event.eventDateStart || '';
                const end = event.eventDateEnd || '';

                if (start === end || end === '') {

                    eventDate = formatDate(start);
                }
                else {

                    eventDate =
                        formatDate(start) +
                        ' - ' +
                        formatDate(end);
                }

                const row = `
                    <tr class="event-row"
                        data-id="${event.eventID}"
                        data-title="${event.eventTitle}"
                        data-venue="${event.eventVenue}"
                        data-date-start="${event.eventDateStart}"
                        data-date-end="${event.eventDateEnd}"
                        data-status="${event.eventStatus}"
                        data-participants="${event.eventParticipants}"
                        data-max="${event.eventMaxParticipants}"
                        data-desc="${event.eventDesc || ''}"
                    >

                        <td>${event.eventID}</td>

                        <td>
                            <strong>
                                ${event.eventTitle}
                            </strong>
                        </td>

                        <td>
                            ${event.eventVenue || ''}
                        </td>

                        <td>
                            ${eventDate}
                        </td>

                        <td>
                            <span class="status-badge ${event.eventStatus.toLowerCase()}">
                                ${event.eventStatus}
                            </span>
                        </td>

                        <td>
                            ${event.eventParticipants}
                            /
                            ${event.eventMaxParticipants}
                        </td>

                    </tr>
                `;

                tbody.innerHTML += row;
            });
        })

        .catch(error => {

            console.error(error);

            document.getElementById('event-table-body').innerHTML = `
                <tr>
                    <td colspan="6" class="empty-cell">
                        Failed to load events.
                    </td>
                </tr>
            `;
        });
    })

    .catch(error => {

        console.error(error);

        alert('Failed to load club information.');
    });
}


// =====================================
// FORMAT DATE
// =====================================

function formatDate(dateString) {

    if (!dateString) return '';

    const date = new Date(dateString);

    return date.toLocaleDateString('en-GB', {

        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}
</script>
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['club_name'])) {
    $clubName = trim($_POST['club_name']);
    $clubDesc = trim($_POST['club_desc']);
    $clubAdvisor = trim($_POST['club_advisor']);
    $clubStatus = (isset($_POST['club_status']) && $_POST['club_status'] === 'inactive') ? 'inactive' : 'Active';
    $clubCreated = trim($_POST['club_created']);

    if ($clubCreated === '') {
        $clubCreated = date('Y-m-d');
    }

    $stmt = mysqli_prepare($link, "INSERT INTO club (clubName, clubDesc, clubCreated, clubAdvisor, clubStatus) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $clubName, $clubDesc, $clubCreated, $clubAdvisor, $clubStatus);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['flash_message'] = 'Club added successfully.';
        $_SESSION['flash_type'] = 'success';
        header("Location: admin_student_clubs.php");
        exit;
    }
}

$nextID = 1;
$idResult = mysqli_query($link, "SELECT MAX(clubID) AS maxID FROM club");
if ($idResult && $row = mysqli_fetch_assoc($idResult)) {
    $nextID = isset($row['maxID']) ? ((int)$row['maxID'] + 1) : 1;
}

$clubList = [];
$clubResult = mysqli_query($link, "SELECT clubID, clubName FROM club ORDER BY clubName");
if ($clubResult) {
    while ($row = mysqli_fetch_assoc($clubResult)) {
        $clubList[] = $row;
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Clubs | MyFKClub Admin</title>
  <link rel="stylesheet" href="../CSS/student_clubs.css">
</head>
<body>
  <div class="dashboard-shell">
    <aside class="dashboard-sidebar">
      <div class="brand-panel">
        <img src="../Image/fkclub.jpg" alt="FKClub logo">
      </div>

      <nav class="sidebar-nav">
        <a href="admin_dashboard.php" class="sidebar-link">Home</a>
        <a href="admin_manage_users.php" class="sidebar-link">Manage Users</a>
        <a href="admin_student_clubs.php" class="sidebar-link active">Student Clubs</a>
        <a href="admin_events.php" class="sidebar-link">Events</a>
        <a href="admin_participation_reports.php" class="sidebar-link">Participation Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <!-- Topbar -->
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">Student Clubs</div>
        </div>
      </div>

      <!-- Content Area -->
      <div class="content-area">

        <div id="toastContainer" class="toast-container"></div>

        <div class="clubs-grid">
          <form method="post" action="admin_student_clubs.php" onsubmit="event.preventDefault(); addClub()">
            <section class="card club-form">
              <h3 class="section-title">Club Registration</h3>
              <div class="form-grid club-info-grid">
              <div class="form-field">
                <label for="club-id">Club ID</label>
                <input id="club-id" type="text" name="club_id" value="<?= isset($nextID) ? htmlspecialchars($nextID) : '' ?>" readonly required>
              </div>
              <div class="form-field">
                <label for="club-name">Club Name</label>
                <input id="club-name" type="text" name="club_name">
              </div>
              <div class="form-field">
                <label for="club-desc">Club Description</label>
                <input id="club-desc" type="text" name="club_desc">
              </div>
              <div class="form-field">
                <label for="club-advisor">Club Advisor</label>
                <input id="club-advisor" type="text" name="club_advisor">
              </div>
              <div class="form-field form-row--inline">
                <label>Club Status</label>
                <label class="radio-label"><input type="radio" name="club_status" value="active"> Active</label>
                <label class="radio-label"><input type="radio" name="club_status" value="inactive"> Not Active</label>
              </div>
              <div class="form-field">
                <label for="club-created">Date Created</label>
                <input id="club-created" type="date" name="club_created">
              </div>
              <div class="form-actions">
                <button type="submit" class="btn-add">Add</button>
              </div>
            </div>
          </section>
          </form>

          <section class="card club-info-panel">
                <h3 class="section-title">Club Information</h3><br>
                <div class="form-grid club-info-grid">
                <input type="hidden" id="info-club-id" name="info_club_id">
                <div class="form-field form-field--stacked">
                    <label for="club-input">Club ID/Name</label>
                    <input 
                        type="text" 
                        id="club-input" 
                        name="club_select" 
                        list="club-options" 
                        class="club-select" 
                        placeholder="Type or select a club..."
                    >
                    <datalist id="club-options">
                        <?php foreach ($clubList as $club): ?>
                            <option value="<?= htmlspecialchars($club['clubID'] . ' - ' . $club['clubName']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-field">
                    <label for="info-club-name">Club Name</label>
                    <input id="info-club-name" type="text" name="info_club_name">
                  </div>
                  <div class="form-field">
                    <label for="info-club-desc">Club Description</label>
                    <input id="info-club-desc" type="text" name="info_club_desc">
                  </div>
                  <div class="form-field">
                    <label for="info-club-advisor">Club Advisor</label>
                    <input id="info-club-advisor" type="text" name="info_club_advisor">
                  </div>
                  <div class="form-field form-row--inline">
                    <label>Club Status</label>
                    <label class="radio-label"><input type="radio" name="info_club_status" value="active"> Active</label>
                    <label class="radio-label"><input type="radio" name="info_club_status" value="inactive"> Not Active</label>
                  </div>
                  <div class="form-field">
                    <label for="info-club-created">Date Created</label>
                    <input id="info-club-created" type="date" name="info_club_created">
                  </div>
                </div>

                <div class="action-buttons">
                <button type="button" class="btn-delete" onclick="deleteClub()">Delete</button>
                <button type="button" class="btn-update" onclick="updateClub()">Update</button>
                </div>
                </section>
        
        <section class="card committee-panel">
          <div class="card-header" style="display: flex; gap: 10px; align-items: center;">
            <h3 class="section-title">Club Committee</h3>
            
            <div class="pill-container" style="position: relative; flex: 1;">
              <input type="text" list="club-options-committee" id="club-choice" placeholder="Select Club (ID or Name)..." class="pill-search">
              <datalist id="club-options-committee">
                  <?php foreach ($clubList as $club): ?>
                      <option value="<?= htmlspecialchars($club['clubID']) ?>">
                          <?= htmlspecialchars($club['clubName']) ?>
                      </option>
                  <?php endforeach; ?>
              </datalist>
            </div>

            <input type="text" id="committee-search" placeholder="Search member..." class="pill-search">
          </div>

          <div class="committee-table-content">
            <div class="committee-grid-header">
              <span>Club ID</span>
              <span>Membership ID</span>
              <span>User ID</span>
              <span>User Name</span>
              <span>Committee Position</span>
                <span>Assigned Date</span>
            </div>
            
            <div class="committee-data-area" id="committee-list-rows">
              <div class="empty-cell">Select a club to view or manage committee members.</div>
            </div>
          </div>

          <div class="committee-actions">
          <button type="button" class="btn-action btn-teal" onclick="openAddModal()">Add</button>
            <button type="button" class="btn-action btn-teal" onclick="openUpdateModal()">Update</button>
            <button type="button" class="btn-action btn-red" onclick="openDeleteModal()">Delete</button>
          </div>
        </section>

          <section class="club-list-container">
              <h1 class="page-title">List of Clubs</h1>
              
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
                                      <option value="<?= htmlspecialchars($club['clubID'] . ' - ' . $club['clubName']) ?>">
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
      </div>
    </main>
  </div>
<script>
    let selectedCommitteeMember = null;

    // --- 1. PAGE INITIALIZATION & SEARCH SYNC ---
function updateCommitteeTable(committeeData) {

    const container = document.getElementById('committee-list-rows');

    container.innerHTML = '';

    selectedCommitteeMember = null;

    if (committeeData.length === 0) {

        container.innerHTML =
            '<div class="empty-cell">No committee members found.</div>';

        return;
    }

    committeeData.forEach(member => {

        const row = document.createElement('div');

        row.className = 'committee-data-row';

        row.innerHTML = `
            <span>${member.clubID}</span>
            <span>${member.membershipID}</span>
            <span>${member.userID}</span>
            <span>${member.userName}</span>
            <span>${member.committeePosition}</span>
            <span>${member.committeeAssignedDate ?? '-'}</span>
        `;

        row.addEventListener('click', function () {

            document.querySelectorAll('.committee-data-row')
                .forEach(r => r.classList.remove('selected-row'));

            row.classList.add('selected-row');

            selectedCommitteeMember = {
                clubID: member.clubID,
                membershipID: member.membershipID,
                userID: member.userID,
                userName: member.userName,
                committeePosition: member.committeePosition,
                committeeAssignedDate: member.committeeAssignedDate,
            };

            console.log(selectedCommitteeMember);
        });

        container.appendChild(row);
    });
}



document.addEventListener("DOMContentLoaded", function() {

    loadClubOptions();

    const clubSearchPill = document.getElementById('club-choice');

    if (clubSearchPill) {

        clubSearchPill.addEventListener('input', function() {

            const val = this.value;

            const options =
                document.getElementById('club-options-committee').options;

            for (let i = 0; i < options.length; i++) {

                if (options[i].value === val) {

                    const cleanID = val.split(' - ')[0].trim();

                    document.getElementById('info-club-id').value = cleanID;

                    fetchCommitteeData(cleanID);

                    break;
                }
            }
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {

    const clubInput = document.getElementById('club-input');

    clubInput.addEventListener('input', function () {

        const value = this.value.trim();

        if (value === '') {
            return;
        }

        let clubID = '';

        // If format is: 1 - Photography Club
        if (value.includes('-')) {

            clubID = value.split('-')[0].trim();
        }

        // If user types only ID
        else if (!isNaN(value)) {

            clubID = value;
        }

        // If user types club name
        else {

            const options =
                document.getElementById('club-options').options;

            for (let i = 0; i < options.length; i++) {

                const optionValue = options[i].value;

                if (
                    optionValue.toLowerCase().includes(value.toLowerCase())
                ) {

                    clubID = optionValue.split('-')[0].trim();

                    break;
                }
            }
        }

        if (clubID !== '') {

            fetchClubInfo(clubID);
        }
    });
});

// wire the list-panel search input to load club details
document.addEventListener('DOMContentLoaded', function () {
    const listInput = document.getElementById('club-list-input');
    if (listInput) {
        const handleListValue = (val) => {
            val = val || '';
            let id = null;
            const m = val.match(/^(\d+)\s*-/);
            if (m) id = m[1];
            else {
                const m2 = val.match(/^(\d+)\b/);
                if (m2) id = m2[1];
            }

            if (id) {
                fetchClubListPanel(id);
            } else {
                const descElm = document.getElementById('list-club-desc');
                const advElm = document.getElementById('list-club-advisor');
                const statElm = document.getElementById('list-club-status');
                const createdElm = document.getElementById('list-club-created');
                const listCommittee = document.getElementById('list-committee-content');
                const tbody = document.getElementById('event-table-body');

                if (descElm) descElm.textContent = 'Not available yet';
                if (advElm) advElm.textContent = 'Not available yet';
                if (statElm) statElm.textContent = 'Not available yet';
                if (createdElm) createdElm.textContent = 'Not available yet';
                if (listCommittee) listCommittee.innerHTML = '<div class="empty-cell">Not available yet</div>';
                if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="empty-cell">Not available yet</td></tr>';
            }
        };

        listInput.addEventListener('input', function () { handleListValue(this.value); });
        listInput.addEventListener('change', function () { handleListValue(this.value); });
    }
});


function loadClubOptions() {

    const fd = new FormData();

    fd.append('action', 'get_clubs');

    fetch('admin_student_clubs_api.php', {

        method: 'POST',
        body: fd

    })
    .then(res => res.json())
    .then(data => {

        if (data.success) {
            // populate each datalist separately so searches remain independent
            const infoDatalist = document.getElementById('club-options');
            const listDatalist = document.getElementById('club-options-list');
            const committeeDatalist = document.getElementById('club-options-committee');

            if (infoDatalist) {
                infoDatalist.innerHTML = '';
                data.clubs.forEach(club => {
                    const option = document.createElement('option');
                    option.value = `${club.clubID} - ${club.clubName}`; // info panel uses ID and name
                    infoDatalist.appendChild(option);
                });
            }

            if (listDatalist) {
                listDatalist.innerHTML = '';
                data.clubs.forEach(club => {
                    const option = document.createElement('option');
                    option.value = `${club.clubID} - ${club.clubName}`; // list panel shows ID - Name
                    listDatalist.appendChild(option);
                });
            }

            if (committeeDatalist) {
                committeeDatalist.innerHTML = '';
                data.clubs.forEach(club => {
                    const option = document.createElement('option');
                    option.value = `${club.clubID} - ${club.clubName}`; // committee search: ID - Name
                    committeeDatalist.appendChild(option);
                });
            }
        }
    });
}



function fetchClubInfo(clubKey) {

    fetch(`admin_student_clubs_api.php?action=club_details&clubKey=${encodeURIComponent(clubKey)}`)

    .then(response => response.json())

    .then(data => {

        if (!data.success) {
            alert(data.message);
            return;
        }

        const club = data.club;

        document.getElementById('info-club-id').value =
            club.clubID || '';

        document.getElementById('info-club-name').value =
            club.clubName || '';

        document.getElementById('info-club-desc').value =
            club.clubDesc || '';

        document.getElementById('info-club-created').value =
            club.clubCreated || '';

        document.getElementById('info-club-advisor').value =
            club.clubAdvisor || '';

        const radios =
            document.getElementsByName('info_club_status');

        radios.forEach(radio => {

            radio.checked =
                (radio.value.toLowerCase() ===
                 club.clubStatus.toLowerCase());
        });
    })

    .catch(error => {

        console.error(error);

        alert("Failed to fetch club info.");
    });
}

function fetchCommitteeData(clubKey) {

    fetch(`admin_student_clubs_api.php?action=club_details&clubKey=${encodeURIComponent(clubKey)}`)

    .then(response => response.json())

    .then(data => {

        if (!data.success) {
            alert(data.message);
            return;
        }

        updateCommitteeTable(data.committee || []);
    })

    .catch(error => {

        console.error(error);

        alert("Failed to fetch committee.");
    });
}



function openAddModal() {

    const clubID =
        document.getElementById('info-club-id').value;

    if (!clubID) {

        alert("Please select a club first.");

        return;
    }

    document.getElementById('add-userID').value = "";
    document.getElementById('add-userName').value = "";
    document.getElementById('add-position').selectedIndex = 0;

    document.getElementById('add-clubID-display').value =
        clubID;

    fetch('admin_student_clubs_api.php?action=get_next_membership_id')

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            document.getElementById('add-membershipID').value =
                data.nextID;

        } else {

            document.getElementById('add-membershipID').value =
                "";
        }
    });

    document.getElementById('addCommitteeModal').style.display =
        'flex';
}



function openUpdateModal() {

    if (!selectedCommitteeMember) {

        alert("Please select a member first!");

        return;
    }

    document.getElementById('upd-userID').value =
        selectedCommitteeMember.userID;

    document.getElementById('upd-userName').value =
        selectedCommitteeMember.userName;

    document.getElementById('upd-clubID-display').value =
        selectedCommitteeMember.clubID;

    document.getElementById('upd-membershipID').value =
        selectedCommitteeMember.membershipID;

    document.getElementById('upd-position').value =
        selectedCommitteeMember.committeePosition;

    document.getElementById('updateCommitteeModal').style.display =
        'flex';
}



function openDeleteModal() {

    if (!selectedCommitteeMember) {

        alert("Please select a member first!");

        return;
    }

    document.getElementById('del-userID').value =
        selectedCommitteeMember.userID;

    document.getElementById('del-userName').value =
        selectedCommitteeMember.userName;

    document.getElementById('del-clubID').value =
        selectedCommitteeMember.clubID;

    document.getElementById('del-membershipID').value =
        selectedCommitteeMember.membershipID;

    document.getElementById('del-position').value =
        selectedCommitteeMember.committeePosition;

    document.getElementById('deleteCommitteeModal').style.display =
        'flex';
}



function closeModal(id) {

    document.getElementById(id).style.display = 'none';
}



function submitAddCommittee() {

    const clubID =
        document.getElementById('info-club-id').value;

    const membershipID =
        document.getElementById('add-membershipID').value;

    const userID =
        document.getElementById('add-userID').value;

    const position =
        document.getElementById('add-position').value;

    const assignedDate =
        new Date().toISOString().split('T')[0];

    if (userID === '' || position === '') {

        alert("Please fill all fields.");

        return;
    }

    const fd = new FormData();

    fd.append('clubID', clubID);
    fd.append('membershipID', membershipID);
    fd.append('userID', userID);
    fd.append('position', position);
    fd.append('assignedDate', assignedDate);

    fetch('admin_student_clubs_api.php?action=add_committee', {

        method: 'POST',
        body: fd
    })

    .then(response => response.text().then(text => {
        if (!response.ok) {
            throw new Error(text || 'Server returned ' + response.status);
        }
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Invalid JSON response:', text);
            throw e;
        }
    }))

    .then(data => {

        alert(data.message);

        if (data.success) {

            closeModal('addCommitteeModal');

            fetchClubData(clubID);
        }
    })

    .catch(error => {

        console.error(error);

        alert("Failed to add committee: " + (error.message || 'Unknown error'));
    });
}



function submitUpdateCommittee() {

    const membershipID =
        document.getElementById('upd-membershipID').value;

    const position =
        document.getElementById('upd-position').value;

    if (membershipID === '' || position === '') {

        alert("Please fill all fields.");
        return;
    }

    const fd = new FormData();

    fd.append('membershipID', membershipID);
    fd.append('position', position);

    fetch('admin_student_clubs_api.php?action=update_committee', {
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
            alert("PHP returned invalid response. Check console.");
            return;
        }

        alert(data.message);

        if (data.success) {

            closeModal('updateCommitteeModal');

            fetchClubData(
                document.getElementById('info-club-id').value
            );
        }
    })

    .catch(error => {

        console.error(error);

        alert("Update failed.");
    });
}

function updateClub() {

    const clubID = document.getElementById('info-club-id').value;
    const clubName = document.getElementById('info-club-name').value.trim();
    const clubDesc = document.getElementById('info-club-desc').value.trim();
    const clubAdvisor = document.getElementById('info-club-advisor').value.trim();
    const clubCreated = document.getElementById('info-club-created').value;

    let clubStatus = '';

    document.querySelectorAll('input[name="info_club_status"]').forEach(radio => {
        if (radio.checked) {
            clubStatus = radio.value;
        }
    });

    if (clubID === '') {
        alert('Please select a club first.');
        return;
    }

    const fd = new FormData();

    fd.append('action', 'update_club');
    fd.append('clubID', clubID);
    fd.append('clubName', clubName);
    fd.append('clubDesc', clubDesc);
    fd.append('clubAdvisor', clubAdvisor);
    fd.append('clubCreated', clubCreated);
    fd.append('clubStatus', clubStatus);

    fetch('admin_student_clubs_api.php', {
        method: 'POST',
        body: fd
    })

    .then(response => response.json())

    .then(data => {

        alert(data.message);

        if (data.success) {

            // Refresh club data
            fetchClubData(clubID);

            // Reload datalist
            loadClubOptions();
        }
    })

    .catch(error => {

        console.error(error);

        alert('Failed to update club.');
    });
}


// ================= DELETE CLUB =================
function addClub() {

    const clubName = document.getElementById('club-name').value.trim();
    const clubDesc = document.getElementById('club-desc').value.trim();
    const clubAdvisor = document.getElementById('club-advisor').value.trim();
    const clubCreated = document.getElementById('club-created').value;

    let clubStatus = '';

    document.querySelectorAll('input[name="club_status"]').forEach(radio => {
        if (radio.checked) {
            clubStatus = radio.value;
        }
    });

    // Validation
    if (clubName === '') {
        alert('Please enter a club name.');
        return;
    }

    const fd = new FormData();

    fd.append('action', 'add_club');
    fd.append('clubName', clubName);
    fd.append('clubDesc', clubDesc);
    fd.append('clubAdvisor', clubAdvisor);
    fd.append('clubCreated', clubCreated);
    fd.append('clubStatus', clubStatus);

    fetch('admin_student_clubs_api.php', {
        method: 'POST',
        body: fd
    })

    .then(response => response.json())

    .then(data => {

        alert(data.message);

        if (data.success) {

            // Reset form
            document.getElementById('club-name').value = '';
            document.getElementById('club-desc').value = '';
            document.getElementById('club-advisor').value = '';
            document.getElementById('club-created').value = '';

            document.querySelectorAll('input[name="club_status"]').forEach(radio => {
                radio.checked = false;
            });

            // Update next club ID
            if (data.nextID) {
                document.getElementById('club-id').value = data.nextID;
            }

            // Reload datalist
            loadClubOptions();
        }
    })

    .catch(error => {

        console.error(error);

        alert('Failed to add club.');
    });
}

function deleteClub() {

    const clubID = document.getElementById('info-club-id').value;

    if (clubID === '') {
        alert('Please select a club first.');
        return;
    }

    if (!confirm('Are you sure you want to delete this club?')) {
        return;
    }

    const fd = new FormData();

    fd.append('action', 'delete_club');
    fd.append('clubID', clubID);

    fetch('admin_student_clubs_api.php', {
        method: 'POST',
        body: fd
    })

    .then(response => response.json())

    .then(data => {

        alert(data.message);

        if (data.success) {

            // Clear form
            document.getElementById('info-club-id').value = '';
            document.getElementById('info-club-name').value = '';
            document.getElementById('info-club-desc').value = '';
            document.getElementById('info-club-advisor').value = '';
            document.getElementById('info-club-created').value = '';

            document.querySelectorAll('input[name="info_club_status"]').forEach(radio => {
                radio.checked = false;
            });

            // Clear committee table
            document.getElementById('committee-list-rows').innerHTML =
                '<div class="empty-cell">Club deleted.</div>';

            // Reload options
            loadClubOptions();
        }
    })

    .catch(error => {

        console.error(error);

        alert('Failed to delete club.');
    });
}

function autoFillName(type) {

    let userID = '';

    if (type === 'add') {
        userID = document.getElementById('add-userID').value;
    }
    else if (type === 'upd') {
        userID = document.getElementById('upd-userID').value;
    }

    if (userID.trim() === '') {

        if (type === 'add') {
            document.getElementById('add-userName').value = '';
        }
        else {
            document.getElementById('upd-userName').value = '';
        }

        return;
    }

    fetch(`admin_student_clubs_api.php?action=get_user_name&userID=${encodeURIComponent(userID)}`)

    .then(response => response.json())

    .then(data => {

        if (data.success) {

            if (type === 'add') {
                document.getElementById('add-userName').value = data.userName;
            }
            else {
                document.getElementById('upd-userName').value = data.userName;
            }

        } else {

            if (type === 'add') {
                document.getElementById('add-userName').value = '';
            }
            else {
                document.getElementById('upd-userName').value = '';
            }
        }
    })

    .catch(error => {
        console.error(error);
    });
}

function submitDeleteCommittee() {
    if (!confirm("Are you sure you want to delete this committee member?")) {
        return;
    }

    const membershipID = document.getElementById('del-membershipID').value;
    const clubID = document.getElementById('del-clubID').value;

    if (!membershipID) {
        alert('No committee membership selected.');
        return;
    }

    const fd = new FormData();
    fd.append('membershipID', membershipID);

    fetch('admin_student_clubs_api.php?action=delete_committee', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) {
            closeModal('deleteCommitteeModal');
            fetchClubData(clubID);
        }
    })
    .catch(error => {
        console.error(error);
        alert('Failed to delete committee member.');
    });
}
</script>
<div id="addCommitteeModal" class="custom-modal" style="display:none;">
    <div class="modal-content">

        <h4 class="modal-title">Add Committee</h4>

        <div class="form-group">
            <label>User ID</label>
            <input type="text" id="add-userID" oninput="autoFillName('add')">
        </div>

        <div class="form-group">
            <label>User Name</label>
            <input type="text" id="add-userName" readonly>
        </div>

        <div class="form-group">
            <label>Club ID</label>
            <input type="text" id="add-clubID-display" readonly>
        </div>

        <div class="form-group">
            <label>Membership ID</label>
            <input type="text" id="add-membershipID" readonly>
        </div>

        <div class="form-group">
            <label>Committee Position</label>
            <select id="add-position">
                <option value="">Select Position</option>
                <option value="President">President</option>
                <option value="Vice President">Vice President</option>
                <option value="Secretary">Secretary</option>
                <option value="Treasurer">Treasurer</option>
            </select>
        </div>

        <div class="modal-actions">
            <button class="btn-red" onclick="closeModal('addCommitteeModal')">Cancel</button>
            <button class="btn-teal" onclick="submitAddCommittee()">Add</button>
        </div>

    </div>
</div>

<div id="updateCommitteeModal" class="custom-modal" style="display:none;">
    <div class="modal-content">

        <h4 class="modal-title">Update Committee</h4>

        <div class="form-group">
            <label>User ID</label>
            <input type="text" id="upd-userID" oninput="autoFillName('upd')">
        </div>

        <div class="form-group">
            <label>User Name</label>
            <input type="text" id="upd-userName" readonly>
        </div>

        <div class="form-group">
            <label>Club ID</label>
            <input type="text" id="upd-clubID-display" readonly>
        </div>

        <div class="form-group">
            <label>Membership ID</label>
            <input type="text" id="upd-membershipID">
        </div>

        <div class="form-group">
            <label>Committee Position</label>
            <select id="upd-position">
                <option value="">Select Position</option>
                <option value="President">President</option>
                <option value="Vice President">Vice President</option>
                <option value="Secretary">Secretary</option>
                <option value="Treasurer">Treasurer</option>
            </select>
        </div>

        <div class="modal-actions">
            <button class="btn-red" onclick="closeModal('updateCommitteeModal')">Cancel</button>
            <button class="btn-teal" onclick="submitUpdateCommittee()">Update</button>
        </div>

    </div>
</div>

<div id="deleteCommitteeModal" class="custom-modal" style="display:none;">
    <div class="modal-content">

        <h4 class="modal-title">Delete Committee</h4>

        <div class="form-group">
            <label>User ID</label>
            <input type="text" id="del-userID" readonly>
        </div>

        <div class="form-group">
            <label>User Name</label>
            <input type="text" id="del-userName" readonly>
        </div>

        <div class="form-group">
            <label>Club ID</label>
            <input type="text" id="del-clubID" readonly>
        </div>

        <div class="form-group">
            <label>Membership ID</label>
            <input type="text" id="del-membershipID" readonly>
        </div>

        <div class="form-group">
            <label>Committee Position</label>
            <select id="del-position" readonly>
                <option value="">Select Position</option>
                <option value="President">President</option>
                <option value="Vice President">Vice President</option>
                <option value="Secretary">Secretary</option>
                <option value="Treasurer">Treasurer</option>
            </select>
        </div>

        <div class="modal-actions">
            <button class="btn-red" onclick="closeModal('deleteCommitteeModal')">Cancel</button>
            <button class="btn-teal" onclick="submitDeleteCommittee()">Delete</button>
        </div>
    </div>
</div>
</body>
</html>
