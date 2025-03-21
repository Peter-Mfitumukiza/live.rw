<?php
session_start();

$_SESSION = array();

session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

header('Location: index.php');
exit;
?>