<?php
// admin_manage_users.php
session_start();

// Security check
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? null) !== 1) {
    header('Location: login.php');
    exit;
}

// Map role IDs to role names
$roleMap = [1 => 'Admin', 2 => 'Committee', 3 => 'Student'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
  <style>
    .action-buttons {
      display: flex;
      gap: 8px;
    }
    .edit-btn, .delete-btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .edit-btn {
      background-color: #007bff;
      color: white;
    }
    .edit-btn:hover {
      background-color: #0056b3;
    }
    .delete-btn {
      background-color: #dc3545;
      color: white;
    }
    .delete-btn:hover {
      background-color: #c82333;
    }
    .manage-user-registration {
      width: 100%;
      margin-bottom: 40px;
    }
    .assign-committee {
      margin-top: 20px;
    }
    .committee-card {
      background: #ffffff;
      border: 2px solid #e2e8f0;
      border-radius: 0;
      padding: 26px 28px;
      box-shadow: 0 18px 30px rgba(15, 23, 42, 0.08);
    }
    .committee-card .section-header {
      margin-bottom: 22px;
      color: #0f172a;
      font-size: 1.1rem;
      letter-spacing: -0.02em;
    }
    .committee-grid {
      display: grid;
      gap: 18px;
    }
    .committee-card .form-field input,
    .committee-card .form-field select {
      min-height: 52px;
      padding: 0 18px;
      border-radius: 14px;
      border: 1px solid #cbd5e1;
      background: #f8fafc;
      color: #0f172a;
    }
    .committee-card .form-field label {
      font-size: 0.96rem;
      font-weight: 700;
      color: #334155;
      margin-bottom: 8px;
    }
    .action-row-register {
      display: flex;
      gap: 15px;
    }
    /* Modal Styles */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background-color: rgba(0, 0, 0, 0.55);
      display: none;
      justify-content: center;
      align-items: center;
      padding: 20px;
      z-index: 1000;
      transition: opacity 0.2s ease;
      opacity: 0;
      pointer-events: none;
    }
    .modal-overlay.open {
      display: flex !important;
      opacity: 1;
      pointer-events: auto;
    }
    .modal-content {
      background-color: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
      width: min(500px, 100%);
      max-width: 520px;
      max-height: calc(100vh - 80px);
      overflow-y: auto;
      border: 1px solid rgba(148, 163, 184, 0.16);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      border-bottom: 2px solid #f0f0f0;
      padding-bottom: 15px;
    }
    .modal-header h2 {
      margin: 0;
      font-size: 22px;
      color: #333;
    }
    .close-modal-btn {
      font-size: 28px;
      font-weight: bold;
      color: #999;
      cursor: pointer;
      transition: color 0.3s ease;
    }
    .close-modal-btn:hover {
      color: #333;
    }
    .modal-form-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 15px;
      margin-bottom: 20px;
    }
    .modal-form-field {
      display: flex;
      flex-direction: column;
    }
    .modal-form-field label {
      font-weight: 600;
      margin-bottom: 5px;
      color: #333;
    }
    .modal-form-field input,
    .modal-form-field select {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    .modal-form-field input:focus,
    .modal-form-field select:focus {
      outline: none;
      border-color: #007bff;
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }
    .modal-buttons {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }
    .modal-save-btn, .modal-cancel-btn {
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .modal-save-btn {
      background-color: #28a745;
      color: white;
    }
    .modal-save-btn:hover {
      background-color: #218838;
    }
    .modal-cancel-btn {
      background-color: #6c757d;
      color: white;
    }
    .modal-cancel-btn:hover {
      background-color: #5a6268;
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
        <a href="admin_dashboard.php" class="sidebar-link">Home</a>
        <a href="admin_manage_users.php" class="sidebar-link active">Manage Users</a>
        <a href="admin_student_clubs.php" class="sidebar-link">Student Clubs</a>
        <a href="admin_events.php" class="sidebar-link">Events</a>
        <a href="admin_participation_reports.php" class="sidebar-link">Participation Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <!-- Topbar stays flush to the sidebar -->
      <div class="topbar">
        <div class="topbar-left">
          <div class="topbar-title">FK Club Admin</div>
        </div>
        <a href="#profile" class="topbar-button">My Profile</a>
      </div>

    <div class="content-area">

<div class="user-registration-wrapper" style="display: flex; gap: 20px; align-items: flex-start;">

  <!-- USER REGISTRATION (LEFT SIDE) -->
  <section class="manage-user-registration" style="flex: 2;">

    <div class="manage-panel">

      <div class="section-header">
        User Registration
      </div>
      
      <!-- Dummy hidden fields to prevent browser autofill -->
      <input type="text" name="fakeusernameremembered" autocomplete="username" style="position:absolute; left:-9999px; top:-9999px; opacity:0;" />
      <input type="password" name="fakepasswordremembered" autocomplete="new-password" style="position:absolute; left:-9999px; top:-9999px; opacity:0;" />

      <div class="form-grid">

        <!-- USER ID -->
        <div class="form-field">
          <label>User ID</label>
          <input 
            type="text"
            id="inputUserID"
            autocomplete="off"
            readonly
            style="background:#f1f5f9; cursor:not-allowed;"
          >
          <input type="hidden" id="inputUserIDRaw">
        </div>

        <!-- PASSWORD -->
        <div class="form-field">
          <label>Password</label>

          <input 
            type="password"
            id="inputPassword"
            autocomplete="new-password"
          >
        </div>

        <!-- NAME -->
        <div class="form-field">
          <label>Username</label>

          <input 
            type="text"
            id="inputName"
            autocomplete="off"
          >
        </div>

        <!-- EMAIL -->
        <div class="form-field">
          <label>Email Address</label>

          <input 
            type="email"
            id="inputEmail"
            autocomplete="off"
          >
        </div>

        <!-- ROLE -->
        <div class="form-field">

          <label>Role</label>

          <div class="role-row">

            <label class="role-option">
              <input 
                type="radio"
                name="userRole"
                value="1"
              >
              <span>Admin</span>
            </label>

            <label class="role-option">
              <input 
                type="radio"
                name="userRole"
                value="3"
              >
              <span>Student</span>
            </label>

          </div>

        </div>

        <!-- CONTACT -->
        <div class="form-field">
          <label>Contact Number</label>

          <input 
            type="text"
            id="inputContact"
            autocomplete="off"
          >
        </div>

<!-- USER PHOTO -->
<div class="form-field">

  <label style="display:block; font-weight:700; margin-bottom:6px; color:#0f2e60;">
    User Photo
  </label>

  <!-- CUSTOM UPLOAD AREA -->
  <div 
    onclick="document.getElementById('inputUserPhoto').click()"
    style="
      width:120px;
      height:120px;
      border:2px dashed #cbd5e0;
      border-radius:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      overflow:hidden;
      position:relative;
      background:#f8fafc;
    "
  >

    <!-- ICON / TEXT -->
    <span id="uploadText" style="font-size:14px; color:#64748b;">
      Upload
    </span>

    <!-- PREVIEW IMAGE -->
    <img 
      id="userPhotoPreview"
      style="
        width:100%;
        height:100%;
        object-fit:cover;
        display:none;
      "
    >

  </div>

  <!-- hidden input -->
  <input 
    type="file" 
    id="inputUserPhoto" 
    accept="image/*"
    autocomplete="off"
    onchange="previewUserPhoto(event)"
    style="display:none;"
  >

</div>

      </div>

      <!-- BUTTON -->
<div class="action-row-register">

  <button 
    type="button"
    class="clear-button"
    onclick="clearUserForm()"
  >
    Clear
  </button>

  <button 
    type="button"
    class="assign-button"
    onclick="addNewUser()"
  >
    Add User
  </button>

</div>

    </div>

  </section>

  <!-- REQUEST PANEL -->
<section class="request-panel" style="flex: 1;">

    <div class="request-header">
      Registration Requests
    </div>

    <div class="request-list">

      <!-- JS LOAD HERE -->

    </div>

  </section>

</div>

   <section class="list-card">
  <div class="list-header">
    <div class="section-header">User List</div>
    <input 
      class="inline-search" 
      type="search" 
      id="adminSearchInput"
      placeholder="Search userName/userID" 
      autocomplete="off" 
      name="user-search-prevent"
    >
  </div>
  <div class="table-wrapper">
    <table>
      <thead></thead>
      <tbody id="userTableBody"></tbody>
    </table>
  </div>
</section>
<section class="assign-committee">
        <div class="committee-card">
          <div class="section-header">Committee Assignment</div>
          <div class="form-grid committee-grid">
            <div class="form-field">
              <label>Select Club</label>
              <select id="selectClub" disabled>
                <option value="">Loading clubs...</option>
              </select>
            </div>
            <div class="form-field">
              <label>Select Club Member</label>
              <select id="selectMember" disabled>
                <option value="">Select a club first</option>
              </select>
            </div>
            <div class="form-field">
              <label>Committee Position</label>
              <select id="selectPosition" disabled>
                <option value="">Loading positions...</option>
              </select>
            </div>
          </div>
            <div class="action-row-committee">
                <button id="assignCommitteeBtn" type="button" class="assign-button" onclick="assignCommittee()" disabled>Assign Committee</button>
            </div>
        </div>
    </section>

<div id="editUserModal" class="modal-overlay" onclick="overlayClick(event)">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Edit User Information</h2>
      <span class="close-modal-btn" onclick="closeEditModal()">&times;</span>
    </div>
    
    <form id="editUserForm">
      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155; text-align: left;">User ID</label>
        <input type="text" id="modalUserID" style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; box-sizing: border-box;">
        <input type="hidden" id="modalOldUserID" name="oldUserID">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155; text-align: left;">User Name</label>
        <input type="text" id="modalName" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; box-sizing: border-box;">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155; text-align: left;">Email Address</label>
        <input type="email" id="modalEmail" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; box-sizing: border-box;">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
        <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155; text-align: left;">Contact Number</label>
        <input type="text" id="modalContact" style="width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; box-sizing: border-box;">
      </div>

      <div class="form-group" style="margin-bottom: 16px;">
  <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155; text-align: left;">
    User Profile
  </label>

  <input 
    type="file" 
    id="modalUserProfile" 
    accept="image/*"
    onchange="previewProfileImage(event)"
  >

  <!-- Preview -->
  <div style="margin-top: 10px;">
    <img 
      id="profilePreview" 
      src="" 
      alt="Profile Preview"
      style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; display: none; border: 2px solid #ccc;"
    >
  </div>
</div>

      <div class="form-group" style="margin-bottom: 24px;">
        <label style="display: block; font-weight: 600; margin-bottom: 6px; color: #334155; text-align: left;">System Role</label>
        <div style="display: flex; gap: 20px; margin-top: 4px;">
          <label style="cursor: pointer; display: flex; align-items: center; gap: 6px; font-weight: normal; color: #334155;"><input type="radio" name="modalRole" value="1"> Admin</label>
          <label style="cursor: pointer; display: flex; align-items: center; gap: 6px; font-weight: normal; color: #334155;"><input type="radio" name="modalRole" value="3"> Student</label>
        </div>
      </div>

      <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 10px;">
        <button type="button" onclick="closeEditModal()" style="padding: 10px 20px; background: #e2e8f0; color: #475569; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Cancel</button>
        <button type="submit" style="padding: 10px 20px; background: #1a365d; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

  <script>
    // System maps used by your layout template
   
    // Load users and initialize search tracker on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadUsers();
      initializeSearchFilter(); // Triggers the real-time matching engine
      initializeCommitteeAssignSection();
    });

    // Automatically fetch requests when the dashboard loads
document.addEventListener("DOMContentLoaded", function() {
    loadRegistrationRequests();
});

function loadRegistrationRequests() {
    fetch('admin_manage_users_api.php?action=get_requests')
        .then(response => response.json())
        .then(data => {
            // Target your exact <div class="request-list"> container
            const container = document.querySelector('.request-list');
            if (!container) return;
            container.innerHTML = '';

            if (!data.success || data.requests.length === 0) {
                container.innerHTML = '<p style="color:gray; padding:15px; font-size:14px; text-align:center;">No pending requests.</p>';
                return;
            }

            data.requests.forEach(req => {
                const card = document.createElement('div');
                
                // Row container style matching your layout
                card.style = "display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-bottom: 1px solid #eee; background: #fff;";
                
                // Safely read role name from your system mapping
                const displayRole = roleMap[req.roleID] || 'Pending User';

                // Package the raw row data securely as a JSON string attribute
                const rowData = JSON.stringify(req)
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');

                // ✅ UNIVERSAL: Displays the Name, Email, and their requested System Role
                card.innerHTML = `
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <span style="font-size: 15px; color: #333; font-weight: 600;">${escapeHtml(req.userName)}</span>
                        <span style="font-size: 13px; color: #777; font-weight: 500;">
                            ${escapeHtml(req.userEmail)} • <strong style="color: #007bff;">${displayRole}</strong>
                        </span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" onclick="rejectRequest(${req.requestID})" style="background: #e0e0e0; color: #333; border: none; padding: 6px 14px; border-radius: 20px; font-size: 13px; cursor: pointer; font-weight: 500;">Reject</button>
                        <button type="button" onclick="autofillRegistrationForm('${rowData}')" style="background: #00a896; color: white; border: none; padding: 6px 14px; border-radius: 20px; font-size: 13px; cursor: pointer; font-weight: 500;">Accept</button>
                    </div>
                `;
                container.appendChild(card);
            });
        });
}

// 🚀 Core Auto-Fill Logic updated for all roles
function autofillRegistrationForm(jsonData) {
    const userObj = JSON.parse(jsonData);
    
    // Autofill text inputs
    if (document.getElementById('userName')) document.getElementById('userName').value = userObj.userName;
    if (document.getElementById('userEmail')) document.getElementById('userEmail').value = userObj.userEmail;
    if (document.getElementById('userContact')) document.getElementById('userContact').value = userObj.userContact || '';
    
    if (document.getElementById('userPass')) {
        document.getElementById('userPass').value = '';
        document.getElementById('userPass').placeholder = "Create a account password";
    }

    // ✅ DYNAMIC ROLE CHECK: Automatically selects Admin, Committee, or Student radio bubble
    const targetedRadio = document.querySelector(`input[name="roleID"][value="${userObj.roleID}"]`);
    if (targetedRadio) {
        targetedRadio.checked = true;
    }

    // Smooth scroll straight to the form
    document.getElementById('userRegistrationForm').scrollIntoView({ behavior: 'smooth' });
}

    // =========================================================
    // COMMITTEE ASSIGNMENT DATA LOADERS
    // =========================================================
    function initializeCommitteeAssignSection() {
      const clubSelect = document.getElementById('selectClub');
      const memberSelect = document.getElementById('selectMember');
      const positionSelect = document.getElementById('selectPosition');
      const assignButton = document.getElementById('assignCommitteeBtn');

      if (!clubSelect || !memberSelect || !positionSelect || !assignButton) {
        return;
      }

      loadClubOptions();
      loadCommitteePositions();

      clubSelect.addEventListener('change', function() {
        if (this.value) {
          loadClubMembers(this.value);
        } else {
          memberSelect.innerHTML = '<option value="">Select a club first</option>';
          memberSelect.disabled = true;
          assignButton.disabled = true;
        }
      });
    }

    function loadNextUserID() {
      fetch('admin_manage_users_api.php?action=get_next_user_id')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const nextID = data.nextID;
            const formatted = 'US' + String(parseInt(nextID, 10)).padStart(4, '0');
            document.getElementById('inputUserID').value = formatted;
            document.getElementById('inputUserIDRaw').value = String(nextID);
          }
        })
        .catch(err => console.error(err));
    }

document.addEventListener('DOMContentLoaded', loadNextUserID);

    function loadClubOptions() {
      const clubSelect = document.getElementById('selectClub');
      clubSelect.disabled = true;
      clubSelect.innerHTML = '<option value="">Loading clubs...</option>';

      fetch('admin_student_clubs_api.php?action=list_clubs')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const clubs = data.clubs || [];
            clubSelect.innerHTML = '<option value="">Select a club</option>';
            clubs.forEach(club => {
              const option = document.createElement('option');
              option.value = club.clubID;
              option.textContent = `${club.clubName}`;
              clubSelect.appendChild(option);
            });
          } else {
            clubSelect.innerHTML = '<option value="">Unable to load clubs</option>';
          }
        })
        .catch(error => {
          console.error('Error loading clubs:', error);
          clubSelect.innerHTML = '<option value="">Unable to load clubs</option>';
        })
        .finally(() => {
          clubSelect.disabled = false;
        });
    }

    function loadClubMembers(clubID) {
      const memberSelect = document.getElementById('selectMember');
      const assignButton = document.getElementById('assignCommitteeBtn');
      memberSelect.disabled = true;
      assignButton.disabled = true;
      memberSelect.innerHTML = '<option value="">Loading club members...</option>';

      fetch('admin_manage_users_api.php?action=get_club_memberships&clubID=' + encodeURIComponent(clubID))
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const memberships = data.memberships || [];
            if (memberships.length === 0) {
              memberSelect.innerHTML = '<option value="">No club members found</option>';
              return;
            }
            memberSelect.innerHTML = '<option value="">Select a member</option>';
            memberships.forEach(row => {
              const option = document.createElement('option');
              option.value = row.membershipID;
              option.textContent = `${row.userName} (${row.userID})`;
              memberSelect.appendChild(option);
            });
            memberSelect.disabled = false;
          } else {
            memberSelect.innerHTML = '<option value="">Unable to load members</option>';
          }
        })
        .catch(error => {
          console.error('Error loading club members:', error);
          memberSelect.innerHTML = '<option value="">Unable to load members</option>';
        })
        .finally(() => {
          updateAssignButtonState();
        });
    }

    function loadCommitteePositions() {
      const positionSelect = document.getElementById('selectPosition');
      positionSelect.disabled = true;
      positionSelect.innerHTML = '<option value="">Loading positions...</option>';

      fetch('admin_manage_users_api.php?action=get_committee_positions')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const positions = data.positions || [];
            if (positions.length === 0) {
              positionSelect.innerHTML = '<option value="">No positions available</option>';
              return;
            }
            positionSelect.innerHTML = '<option value="">Select a position</option>';
            positions.forEach(position => {
              const option = document.createElement('option');
              option.value = position;
              option.textContent = position;
              positionSelect.appendChild(option);
            });
            positionSelect.disabled = false;
          } else {
            positionSelect.innerHTML = '<option value="">Unable to load positions</option>';
          }
        })
        .catch(error => {
          console.error('Error loading positions:', error);
          positionSelect.innerHTML = '<option value="">Unable to load positions</option>';
        })
        .finally(() => {
          updateAssignButtonState();
        });
    }

    function updateAssignButtonState() {
      const memberSelect = document.getElementById('selectMember');
      const positionSelect = document.getElementById('selectPosition');
      const assignButton = document.getElementById('assignCommitteeBtn');
      assignButton.disabled = !memberSelect || !positionSelect || !memberSelect.value || !positionSelect.value;
    }

    function assignCommittee() {
      const clubSelect = document.getElementById('selectClub');
      const memberSelect = document.getElementById('selectMember');
      const positionSelect = document.getElementById('selectPosition');

      if (!clubSelect.value) {
        alert('Please select a club.');
        return;
      }
      if (!memberSelect.value) {
        alert('Please select a club member.');
        return;
      }
      if (!positionSelect.value) {
        alert('Please select a committee position.');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'assign_committee');
      formData.append('membershipID', memberSelect.value);
      formData.append('committeePosition', positionSelect.value);

      fetch('admin_manage_users_api.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Committee assigned successfully');
          loadUsers();
          loadClubMembers(clubSelect.value);
        } else {
          alert('Error assigning committee: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error assigning committee:', error);
        alert('Error assigning committee.');
      });
    }

    document.addEventListener('change', function(event) {
      if (event.target && event.target.matches('#selectMember, #selectPosition')) {
        updateAssignButtonState();
      }
    });

    // =========================================================
    // REAL-TIME PRECISION SEARCH FILTER
    // =========================================================
   function initializeSearchFilter() {
      const searchInput = document.getElementById('adminSearchInput');
      
      if (searchInput) {
        // 1. Force clear right away
        searchInput.value = ""; 
        
        // 2. Extra safety guard: Clear it again after a tiny fraction of a second 
        // to wipe out any aggressive browser autofill attempts!
        setTimeout(() => {
          searchInput.value = "";
        }, 100);

        searchInput.addEventListener('keyup', function() {
          const filterValue = this.value.toLowerCase().trim();
          const tableBody = document.getElementById('userTableBody');
          const tableRows = tableBody.querySelectorAll('tr');
          
          const oldFeedbackRow = document.getElementById('no-match-row');
          if (oldFeedbackRow) oldFeedbackRow.remove();

          let visibleRowCount = 0;

          tableRows.forEach(row => {
            if (row.querySelector('.empty-cell')) return;

            const userIDCell   = row.cells[0] ? row.cells[0].textContent.toLowerCase().trim() : '';
            const userNameCell = row.cells[1] ? row.cells[1].textContent.toLowerCase().trim() : '';

            const isIDMatch   = userIDCell.startsWith(filterValue);
            const isNameMatch = userNameCell.includes(filterValue);

            if (isIDMatch || isNameMatch) {
              row.style.display = ""; 
              visibleRowCount++;
            } else {
              row.style.display = "none"; 
            }
          });

          if (visibleRowCount === 0 && tableRows.length > 0 && !tableBody.querySelector('.empty-cell')) {
            const noMatchRow = document.createElement('tr');
            noMatchRow.id = 'no-match-row';
            noMatchRow.innerHTML = `
              <td colspan="6" style="text-align: center; color: #a0aec0; padding: 25px; font-style: italic; background-color: #f7fafc;">
                No registered users found matching "${escapeHtml(this.value)}"
              </td>
            `;
            tableBody.appendChild(noMatchRow);
          }
        });
      }
    }
  const roleMap = {
    1: 'Admin',
    2: 'Committee',
    3: 'Student'
};
    // Load all users from API
    function loadUsers() {
      fetch('admin_manage_users_api.php?action=get_users')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            populateUserTable(data.users);
          } else {
            showError('Failed to load users: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showError('Error loading users');
        });
    }
    function showError(message) {
    console.error(message);
    const tableBody = document.getElementById('userTableBody');
    if (tableBody) {
        tableBody.innerHTML = `<tr><td colspan="6" style="color:red; text-align:center; padding:20px;">⚠️ ${message}</td></tr>`;
    }
}

    // Populate user table with data
    function populateUserTable(users) {
      const tableBody = document.getElementById('userTableBody');
      tableBody.innerHTML = '';

      if (users.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="empty-cell">No users found.</td></tr>';
        return;
      }

      users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${escapeHtml(user.userID)}</td>
          <td>${escapeHtml(user.userName)}</td>
          <td>${escapeHtml(user.userEmail)}</td>
          <td>${escapeHtml(user.userContact || 'N/A')}</td>
          <td>${roleMap[user.roleID] || 'Unknown'}</td>
          <td>
            <div class="action-buttons"></div>
          </td>
        `;

        const actionContainer = row.querySelector('.action-buttons');
        const editBtn = document.createElement('button');
        editBtn.className = 'edit-btn';
        editBtn.type = 'button';
        editBtn.textContent = 'Edit';
        editBtn.addEventListener('click', () => editUser(user.userID));

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'delete-btn';
        deleteBtn.type = 'button';
        deleteBtn.textContent = 'Delete';
        deleteBtn.addEventListener('click', () => deleteUser(user.userID));

        actionContainer.appendChild(editBtn);
        actionContainer.appendChild(deleteBtn);
        tableBody.appendChild(row);
      });
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
      if (!text) return '';
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

   // =========================================================
    // MODAL POPUP ACTION OPERATIONS
    // =========================================================
    
    // Open modal and load user data into fields
    function editUser(userID) {
      fetch('admin_manage_users_api.php?action=get_user&userID=' + encodeURIComponent(userID))
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            const user = data.user;
            
            // Populate the modal input elements
            document.getElementById('modalUserID').value = user.userID;
            document.getElementById('modalOldUserID').value = user.userID;
            document.getElementById('modalName').value = user.userName;
            document.getElementById('modalEmail').value = user.userEmail;
            document.getElementById('modalContact').value = user.userContact || '';
            
            // Set matching role checkbox selection inside the modal window block
            const roleRadios = document.querySelectorAll('input[name="modalRole"]');
            roleRadios.forEach(radio => {
              radio.checked = (radio.value == user.roleID);
            });
            
            // Reveal the popup window layout overlay 
            document.getElementById('editUserModal').classList.add('open');
          } else {
            showError('Failed to load user info records: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error fetching user:', error);
          showError('Unexpected connection failure checking profile parameters');
        });
    }

    // Close and reset the display state of the modal overlay
    function closeEditModal() {
      document.getElementById('editUserModal').classList.remove('open');
      document.getElementById('editUserForm').reset();
    }

    function overlayClick(event) {
      if (event.target === event.currentTarget) {
        closeEditModal();
      }
    }

document.addEventListener('DOMContentLoaded', function() {
  const modalForm = document.getElementById('editUserForm');

  if (modalForm) {
    modalForm.addEventListener('submit', function(event) {
      event.preventDefault();

      const userID = document.getElementById('modalUserID').value.trim();
      const oldUserID = document.getElementById('modalOldUserID').value.trim();
      const name = document.getElementById('modalName').value.trim();
      const email = document.getElementById('modalEmail').value.trim();
      const contact = document.getElementById('modalContact').value.trim();
      const roleElement = document.querySelector('input[name="modalRole"]:checked');
      const roleID = roleElement ? roleElement.value : '3';

      if (!name || !email) {
        alert("Name and Email cannot be empty.");
        return;
      }

      const formData = new FormData();
      formData.append('action', 'update_user');

      // 🔥 IMPORTANT: use OLD ID for WHERE condition
      formData.append('oldUserID', oldUserID);

      // updated values
      formData.append('userID', userID); // optional (only if you allow ID change)
      formData.append('userName', name);
      formData.append('userEmail', email);
      formData.append('userContact', contact);
      formData.append('userPhoto', document.getElementById('modalUserProfile').files[0] || ''); // optional file upload
      formData.append('roleID', roleID);

      fetch('admin_manage_users_api.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('Profile updated successfully!');
          closeEditModal();
          loadUsers();
        } else {
          alert('Error: ' + (data.message || 'Update failed'));
        }
      })
      .catch(err => {
        console.error(err);
        alert('Network error');
      });
    });
  }
});

    // Delete user
    function deleteUser(userID) {
      if (confirm('Are you sure you want to delete user ' + userID + '?')) {
        fetch('admin_manage_users_api.php?action=delete_user', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'action=delete_user&userID=' + encodeURIComponent(userID)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('User deleted successfully');
            loadUsers(); // Reload user list completely
          } else {
            showError('Delete failed: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showError('Error deleting user');
        });
      }
    }

    // Add new user
function addNewUser() {

  const userID = document.getElementById('inputUserIDRaw').value.trim();
  const password = document.getElementById('inputPassword').value.trim();
  const name = document.getElementById('inputName').value.trim();
  const email = document.getElementById('inputEmail').value.trim();
  const contact = document.getElementById('inputContact').value.trim();

  const selectedRole = document.querySelector('input[name="userRole"]:checked');
  const roleID = selectedRole ? selectedRole.value : '3';

  const photoFile = document.getElementById('inputUserPhoto').files[0]; // ✅ ADD THIS

  if (!userID || !password || !name || !email) {
    alert('Please fill in required fields');
    return;
  }

  const formData = new FormData();

  formData.append('action', 'add_user');
  formData.append('userID', userID);
  formData.append('userName', name);
  formData.append('userEmail', email);
  formData.append('userContact', contact);
  formData.append('userPass', password);
  formData.append('roleID', roleID);

  // ✅ THIS IS WHERE YOU PUT IT
  if (photoFile) {
    formData.append('userPhoto', photoFile);
  }

  fetch('admin_manage_users_api.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('User added successfully!');
      clearUserForm();
      loadUsers();
      loadNextUserID(); // Refresh the next available user ID in the form
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(err => console.error(err));
}

    // Clear user registration form fields
function clearUserForm() {

  // text inputs
  document.getElementById('inputPassword').value = '';
  document.getElementById('inputName').value = '';
  document.getElementById('inputEmail').value = '';
  document.getElementById('inputContact').value = '';
  document.getElementByID('inputRole').value = '';


  // reset file input (VERY IMPORTANT)
  const fileInput = document.getElementById('inputUserPhoto');
  if (fileInput) {
    fileInput.value = null;
  }

  // reset image preview
  const preview = document.getElementById('userPhotoPreview');
  if (preview) {
    preview.src = '';
    preview.style.display = 'none';
  }
}
function previewUserPhoto(event) {
  const file = event.target.files[0];

  if (file) {
    const reader = new FileReader();

    reader.onload = function (e) {
      document.getElementById('userPhotoPreview').src = e.target.result;
      document.getElementById('userPhotoPreview').style.display = 'block';
      document.getElementById('uploadText').style.display = 'none';
    };

    reader.readAsDataURL(file);
  }
}
  </script>