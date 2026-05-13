<?php
// admin_manage_users.php
session_start();
// Add authentication logic as needed.
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users | MyFKClub</title>
  <link rel="stylesheet" href="../CSS/dashboard.css">
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
        <a href="#" class="sidebar-link">Participation Reports</a>
      </nav>
    </aside>

    <main class="dashboard-main">
      <div class="topbar topbar-manage">
        <div class="topbar-title">Manage Users</div>
      </div>

      <div class="search-bar-wrap">
        <input class="search-input" type="search" placeholder="Search name/email/userID">
        <div class="filter-row">
          <button class="filter-pill">Filter by club</button>
          <button class="filter-pill">Semester</button>
          <button class="filter-pill">Club Status</button>
          <button class="primary-pill">Apply Filter</button>
          <button class="secondary-pill">Export Report</button>
        </div>
      </div>

      <section class="manage-grid">
        <div class="manage-panel">
          <div class="section-header">User Registration</div>
          <div class="form-grid">
            <div class="form-field">
              <label>User ID</label>
              <input type="text" placeholder="User ID">
            </div>
            <div class="form-field">
              <label>Password</label>
              <input type="password" placeholder="Password">
            </div>
            <div class="form-field">
              <label>Name</label>
              <input type="text" placeholder="Name">
            </div>
            <div class="form-field">
              <label>Email</label>
              <input type="email" placeholder="Email">
            </div>
            <div class="form-field">
              <label>Role</label>
              <div class="role-row">
                <label><input type="radio" name="userRole" value="admin"> Admin</label>
                <label><input type="radio" name="userRole" value="student"> Student</label>
              </div>
            </div>
            <div class="form-field">
              <label>Contact</label>
              <input type="text" placeholder="Contact">
            </div>
            <div class="form-field file-field">
              <label>User Photo</label>
              <div class="file-upload">Photo</div>
            </div>
          </div>
          <button class="assign-button">Add User</button>
        </div>

        <aside class="requests-card">
          <div class="section-header">Requests</div>
          <div class="request-item">
            <div>
              <div class="request-title">User ID</div>
              <div class="request-meta">User Name • Role Type</div>
            </div>
            <div class="request-actions">
              <button class="reject-btn">Reject</button>
              <button class="accept-btn">Accept</button>
            </div>
          </div>
          <div class="request-item">
            <div>
              <div class="request-title">User ID</div>
              <div class="request-meta">User Name • Role Type</div>
            </div>
            <div class="request-actions">
              <button class="reject-btn">Reject</button>
              <button class="accept-btn">Accept</button>
            </div>
          </div>
          <div class="request-item">
            <div>
              <div class="request-title">User ID</div>
              <div class="request-meta">User Name • Role Type</div>
            </div>
            <div class="request-actions">
              <button class="reject-btn">Reject</button>
              <button class="accept-btn">Accept</button>
            </div>
          </div>
          <div class="request-item">
            <div>
              <div class="request-title">User ID</div>
              <div class="request-meta">User Name • Role Type</div>
            </div>
            <div class="request-actions">
              <button class="reject-btn">Reject</button>
              <button class="accept-btn">Accept</button>
            </div>
          </div>
        </aside>
      </section>

      <section class="manage-grid">
        <div class="manage-panel">
          <div class="section-header">Assign Committees</div>
          <div class="form-grid">
            <div class="form-field">
              <label>User ID</label>
              <input type="text" placeholder="User ID">
            </div>
            <div class="form-field">
              <label>Club ID/Name</label>
              <select>
                <option>Select club</option>
                <option>Tech Club</option>
                <option>Art Club</option>
              </select>
            </div>
            <div class="form-field">
              <label>Membership ID</label>
              <input type="text" placeholder="Membership ID">
            </div>
            <div class="form-field">
              <label>Committee ID/Type</label>
              <input type="text" placeholder="Committee ID/Type">
            </div>
            <div class="form-field">
              <label>Name</label>
              <input type="text" placeholder="Name">
            </div>
            <div class="form-field">
              <label>Email</label>
              <input type="email" placeholder="Email">
            </div>
            <div class="form-field file-field">
              <label>User Photo</label>
              <div class="file-upload">Photo</div>
            </div>
            <div class="form-field">
              <label>Contact</label>
              <input type="text" placeholder="Contact">
            </div>
          </div>
          <button class="assign-button">Assign</button>
        </div>

        <div class="list-card">
          <div class="list-header">
            <div class="section-header">User List</div>
            <input class="inline-search" type="search" placeholder="Search userName/userID">
          </div>
          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>User ID</th>
                  <th>User Name</th>
                  <th>User Email</th>
                  <th>User Contact</th>
                  <th>User Role</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="5" class="empty-cell">No users found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
