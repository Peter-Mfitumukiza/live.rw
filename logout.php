<?php

session_start();
require_once('config/db.php');
require_once('functions.php');

// Deactivate current device if user is logged in
if (isset($_SESSION['user_id'])) {
    // Check if this is a "logout from all devices" request
    $logoutAll = isset($_GET['all']) && $_GET['all'] == 1;

    if ($logoutAll) {
        // Deactivate all devices for this user
        $query = "UPDATE user_devices SET is_active = 0 WHERE user_id = ?";
        $stmt = mysqli_prepare($db_mysql, $query);
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
    } else {
        // Deactivate just the current device
        logoutCurrentDevice($db_mysql, $_SESSION['user_id']);
    }
}

$_SESSION = array();
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page
header('Location: index.php');
exit;
?>