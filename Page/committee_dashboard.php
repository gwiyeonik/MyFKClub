<?php 
session_start(); 

// Security Gate: Ensure only logged-in committee members can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'committee') {
    header('Location: login.php');
    exit;
}

// Get the position from session (saved during auth.php)
$position = $_SESSION['position'] ?? 'Committee Member';
$userName = $_SESSION['user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Dashboard | MyFKClub</title>
    <link rel="stylesheet" href="../CSS/committee.css">
    <style>
        /* Ensuring the topbar behaves as a flex container for the button placement */
        .topbar {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }

        /* Styling the My Profile button using your CSS variables */
        .topbar-button {
            background-color: var(--teal-btn) !important;
            color: white !important;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .topbar-button:hover {
            background-color: var(--teal-hover) !important;
            text-decoration: none;
        }

        .user-welcome {
            font-size: 0.9rem;
            color: rgba(255,255,255,0.8);
            font-weight: normal;
            display: block;
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
                <a href="committee_dashboard.php" class="sidebar-link active">Home</a>
                <a href="committee_view_clubs.php" class="sidebar-link">View Clubs</a>
                <a href="committee_manage_events.php" class="sidebar-link">Manage Events</a>
                <a href="#" class="sidebar-link">Members</a>
                <a href="committee_attendance_report.php" class="sidebar-link">Attendance</a>
                <a href="committee_participation_report.php" class="sidebar-link">Reports</a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <header class="topbar">
                <div class="topbar-left">
                    <h1 class="topbar-title">
                        Committee Dashboard 
                        <span class="user-welcome"><?php echo htmlspecialchars($position); ?>: <?php echo htmlspecialchars($userName); ?></span>
                    </h1>
                </div>
                <div class="topbar-right">
                    <a href="myProfile.php" class="topbar-button">My Profile</a>
                </div>
            </header>

            <div class="content-area">
                <section class="stats-row">
                    <div class="stat-card">
                        <div style="color: var(--muted); font-size: 0.9rem;">Club Members</div>
                        <div style="font-size: 1.8rem; margin-top: 5px;">...</div>
                    </div>
                    <div class="stat-card">
                        <div style="color: var(--muted); font-size: 0.9rem;">Events This Month</div>
                        <div style="font-size: 1.8rem; margin-top: 5px;">...</div>
                    </div>
                    <div class="stat-card">
                        <div style="color: var(--muted); font-size: 0.9rem;">Total Registrations</div>
                        <div style="font-size: 1.8rem; margin-top: 5px;">...</div>
                    </div>
                    <div class="stat-card">
                        <div style="color: var(--muted); font-size: 0.9rem;">Avg Attendance</div>
                        <div style="font-size: 1.8rem; margin-top: 5px;">...</div>
                    </div>
                </section>

                <section class="manage-grid">
                    <div class="manage-panel">
                        <div class="section-header">Upcoming Events</div>
                        <div class="table-columns">
                            <span>Event Names</span>
                            <span>Date</span>
                            <span>Status</span>
                        </div>
                        <div class="panel-body-placeholder">
                            <p style="text-align: center; color: var(--muted); margin-top: 20px;">No upcoming events found.</p>
                        </div>
                        <div class="panel-footer">
                            <button class="btn-primary" onclick="window.location.href='committee_manage_events.php'">Add event</button>
                        </div>
                    </div>

                    <div class="manage-panel">
                        <div class="section-header">Participation by Month</div>
                        <div class="chart-placeholder">
                            <div style="text-align: center;">
                                <span>&lt;&lt; bar chart &gt;&gt;</span>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>