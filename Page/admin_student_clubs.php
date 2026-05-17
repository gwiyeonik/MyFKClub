<?php
// admin_student_clubs.php
session_start();

$link = mysqli_connect("localhost", "root", "", "myfkclub");
if (!$link) {
    die("Connection failed: " . mysqli_connect_error());
}

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
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

      <!-- Content Area -->
      <div class="content-area">


        <div class="clubs-grid">
          <form method="post" action="admin_student_clubs.php">
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
                        onchange="fetchClubData(this.value)" 
                    >
                    <datalist id="club-options">
                        <?php foreach ($clubList as $club): ?>
                            <option value="<?= htmlspecialchars($club['clubID']) ?>">
                                <?= htmlspecialchars($club['clubName']) ?>
                            </option>
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
                              <input list="club-options" id="club-list-input" name="club-selection" placeholder="Select or type a club...">
                              <datalist id="club-options">
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
                          <div class="committee-table-header">
                              <span>User ID</span>
                              <span>User Name</span>
                              <span>Committee Position</span>
                          </div>
                          <div class="committee-content" id="list-committee-content">
                              <div class="empty-cell">Select a club to load committee members.</div>
                          </div>
                      </div>
                  </div>

                  <div class="events-section">
                      <h3>Club Events</h3>
                      <div class="events-grid" id="list-events-grid">
                          <div class="event-placeholder">Select a club to view events.</div>
                      </div>
                      <div class="button-wrapper">
                          <button class="view-events-btn" type="button">View Events</button>
                      </div>
                  </div>
              </div>
          </section>
        </div>
      </div>
    </main>
    <script src="admin_student_clubs.js"></script>
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

                    fetchClubData(cleanID);

                    break;
                }
            }
        });
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

            const datalist =
                document.getElementById('club-options-committee');

            datalist.innerHTML = '';

            data.clubs.forEach(club => {

                const option = document.createElement('option');

                option.value =
                    `${club.clubID} - ${club.clubName}`;

                datalist.appendChild(option);
            });
        }
    });
}



function fetchClubData(clubKey) {

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

        updateCommitteeTable(data.committee || []);
    })

    .catch(error => {

        console.error(error);

        alert("Failed to fetch club details.");
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

    .then(response => response.json())

    .then(data => {

        alert(data.message);

        if (data.success) {

            closeModal('addCommitteeModal');

            fetchClubData(clubID);
        }
    })

    .catch(error => {

        console.error(error);

        alert("Failed to add committee.");
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



function submitDeleteCommittee() {

    if (!confirm("Are you sure you want to delete this committee member?")) {

        return;
    }

    const membershipID =
        document.getElementById('del-membershipID').value;

    const clubID =
        document.getElementById('del-clubID').value;

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

        alert("Delete failed.");
    });
}
function updateClub() {

    const clubID = document.getElementById('info-club-id').value;
    const clubName = document.getElementById('info-club-name').value;
    const clubDesc = document.getElementById('info-club-desc').value;
    const clubAdvisor = document.getElementById('info-club-advisor').value;
    const clubCreated = document.getElementById('info-club-created').value;

    let clubStatus = '';
    const radios = document.getElementsByName('info_club_status');

    radios.forEach(radio => {
        if (radio.checked) {
            clubStatus = radio.value;
        }
    });

    if (clubID === '') {
        alert("Please select a club first.");
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
            fetchClubData(clubID);
        }
    })
    .catch(error => {
        console.error(error);
        alert("Update failed.");
    });
}


// ================= DELETE CLUB =================
function deleteClub() {

    const clubID = document.getElementById('info-club-id').value;

    if (clubID === '') {
        alert("Please select a club first.");
        return;
    }

    if (!confirm("Are you sure you want to delete this club?")) {
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

            document.getElementById('info-club-id').value = '';
            document.getElementById('info-club-name').value = '';
            document.getElementById('info-club-desc').value = '';
            document.getElementById('info-club-advisor').value = '';
            document.getElementById('info-club-created').value = '';

            document.querySelectorAll('input[name="info_club_status"]').forEach(r => {
                r.checked = false;
            });

            document.getElementById('committee-list-rows').innerHTML =
                '<div class="empty-cell">Club deleted.</div>';
        }
    })
    .catch(error => {
        console.error(error);
        alert("Delete failed.");
    });
}

function submitDeleteCommittee() { if (!confirm("Are you sure you want to delete this committee member?")) { return; } const clubID = document.getElementById('del-clubID').value; const userID = document.getElementById('del-userID').value; const fd = new FormData(); fd.append('clubID', clubID); fd.append('userID', userID); fetch('admin_student_clubs_api.php?action=delete_committee', { method: 'POST', body: fd }) .then(r => r.json()) .then(res => { alert(res.message); if (res.success) { closeModal('deleteCommitteeModal'); fetchClubData(clubID); } }); }
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
