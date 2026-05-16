<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee Dashboard | MyFKClub</title>
    <link rel="stylesheet" href="../CSS/committee.css">
</head>
<body>
    <div class="dashboard-shell">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <div class="brand-panel">
                <img src="../Image/fkclub.jpg" alt="FKClub logo">
            </div>

            <nav class="sidebar-nav">
                <a href="#" class="sidebar-link ">Home</a>
                <a href="#" class="sidebar-link">View Clubs</a>
                <a href="#" class="sidebar-link">Manage Events</a>
                <a href="#" class="sidebar-link">Members</a>
                <a href="committee_attendance_report.php" class="sidebar-link">Attendance</a>
                <a href="committee_participation_report.php" class="sidebar-link">Reports</a>
            </nav>
        </aside>

        <!-- Main Workspace -->
        <main class="dashboard-main">
            <header class="topbar">
                <h1 class="topbar-title">Committee Dashboard</h1>
            </header>

            <div class="content-area">
                <!-- 4-Column Stats -->
                <section class="stats-row">
                    <div class="stat-card">Club Members</div>
                    <div class="stat-card">Events This Month</div>
                    <div class="stat-card">Total Registrations</div>
                    <div class="stat-card">Avg Attendance</div>
                </section>

                <!-- Grid Proportions - CLEANED UP -->
                <section class="manage-grid">
                    
                    <!-- Panel 1: Upcoming Events -->
                    <div class="manage-panel">
                        <div class="section-header">Upcoming Events</div>
                        <div class="table-columns">
                            <span>Event Names</span>
                            <span>Date</span>
                            <span>Status</span>
                        </div>
                        <div class="panel-body-placeholder">
                            <!-- Dynamic Event Data Goes Here -->
                        </div>
                        <div class="panel-footer">
                            <button class="btn-primary">Add event</button>
                        </div>
                    </div>

                    <!-- Panel 2: Participation Chart -->
                    <div class="manage-panel">
                        <div class="section-header">Participation by Month</div>
                        <div class="chart-placeholder">&lt;&lt; bar chart&gt;&gt;</div>
                    </div>

                </section>
            </div>
        </main>
    </div>
</body>
</html>