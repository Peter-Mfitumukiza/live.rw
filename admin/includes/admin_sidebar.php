<?php
// Get current page for highlighting active menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <ul class="sidebar-menu">
        <li class="sidebar-item">
            <a href="index.php" class="sidebar-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <div class="sidebar-heading">Content Management</div>

        <li class="sidebar-item">
            <a href="./events.php"
                class="sidebar-link <?php echo ($current_page == 'events.php' || $current_page == 'event_form.php') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Events</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="highlights.php"
                class="sidebar-link <?php echo ($current_page == 'highlights.php' || $current_page == 'highlight_form.php') ? 'active' : ''; ?>">
                <i class="fas fa-play-circle"></i>
                <span>Highlights</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="leagues.php" class="sidebar-link <?php echo ($current_page == 'leagues.php') ? 'active' : ''; ?>">
                <i class="fas fa-trophy"></i>
                <span>Leagues</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="teams.php" class="sidebar-link <?php echo ($current_page == 'teams.php') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Teams</span>
            </a>
        </li>

        <div class="sidebar-heading">User Management</div>

        <li class="sidebar-item">
            <a href="users.php"
                class="sidebar-link <?php echo ($current_page == 'users.php' || $current_page == 'user_details.php') ? 'active' : ''; ?>">
                <i class="fas fa-user"></i>
                <span>Users</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="admins.php" class="sidebar-link <?php echo ($current_page == 'admins.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-shield"></i>
                <span>Admins</span>
            </a>
        </li>

        <div class="sidebar-heading">Finance</div>

        <li class="sidebar-item">
            <a href="transactions.php"
                class="sidebar-link <?php echo ($current_page == 'transactions.php') ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Transactions</span>
            </a>
        </li>

        <div class="sidebar-heading">Settings</div>

        <li class="sidebar-item">
            <a href="settings.php"
                class="sidebar-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Site Settings</span>
            </a>
        </li>
    </ul>
</aside>