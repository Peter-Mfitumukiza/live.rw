<?php
// Make sure session is started on any page that includes this navbar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Navbar -->
<nav class="navbar">
    <!-- Logo -->
    <div class="logo">
        <a href="index.php">
            <img src="./assets/logo_without_bg.png" alt="Live.rw logo" height="40px" width="auto">
        </a>
    </div>

    <!-- Navigation items -->
    <div class="nav-items">
        <a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'class="active"' : ''; ?>>Home</a>
        <a href="live.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'live.php') ? 'class="active"' : ''; ?>>Live</a>
        <a href="sports.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'sports.php') ? 'class="active"' : ''; ?>>Sports</a>
        <a href="schedule.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'schedule.php') ? 'class="active"' : ''; ?>>Schedule</a>
        <a href="teams.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'teams.php') ? 'class="active"' : ''; ?>>Teams</a>
        <a href="highlights.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'highlights.php') ? 'class="active"' : ''; ?>>Highlights</a>
        <a href="premium.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'premium.php') ? 'class="active"' : ''; ?>>Premium</a>
    </div>

    <!-- Right section - search and auth -->
    <div class="nav-right">
        <!-- Search bar -->
        <div class="search-bar">
            <span class="search-icon">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" placeholder="Search...">
        </div>

        <!-- Auth buttons -->
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-dropdown">
                    <button class="profile-button">
                        <?php $firstLetter = substr($_SESSION['user_name'], 0, 1); ?>
                        <div class="avatar"><?php echo $firstLetter; ?></div>
                    </button>
                    <div class="dropdown-content">
                        <div class="dropdown-header">
                            <span class="user-greeting">Hi, <?php echo $_SESSION['user_name']; ?></span>
                        </div>
                        <div class="dropdown-body">
                            <a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> My Profile</a>
                            <a href="history.php" class="dropdown-item"><i class="fas fa-history"></i> Watch History</a>
                            <a href="favorites.php" class="dropdown-item"><i class="fas fa-heart"></i> Liked</a>
                            <a href="subscription.php" class="dropdown-item"><i class="fas fa-star"></i> Subscription</a>
                            <div class="dropdown-divider"></div>
                            <a href="settings.php" class="dropdown-item"><i class="fas fa-cog"></i> Settings</a>
                            <a href="logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="sign-in">Sign in</a>
                <a href="register.php" class="sign-up">Sign up</a>
            <?php endif; ?>
        </div>

        <!-- Mobile menu toggle -->
        <div class="mobile-menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</nav>