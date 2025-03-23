<header class="admin-header">
    <div class="admin-logo">
        <img src="../assets/logo_without_bg.png" alt="LiveRW">
        <div class="admin-brand">Live<span>RW</span> Admin</div>
    </div>

    <div class="admin-topnav">
        <div class="admin-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search...">
        </div>

        <div class="admin-profile">
            <button class="profile-button" id="profileDropdown">
                <div class="profile-avatar">
                    <?php echo substr($_SESSION['user_name'], 0, 1); ?>
                </div>
                <div class="profile-name"><?php echo $_SESSION['user_name']; ?></div>
                <i class="fas fa-chevron-down"></i>
            </button>

            <div class="admin-dropdown" id="profileMenu">
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i> Profile
                </a>
                <a href="settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="../logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</header>