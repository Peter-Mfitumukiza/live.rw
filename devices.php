<?php
// manage_devices.php - Page for managing active devices

session_start();
require_once('config/db.php');
require_once('functions.php');

// Check if user has temporary session or is fully logged in
if (!isset($_SESSION['temp_user_id']) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'] ?? $_SESSION['temp_user_id'];
$userName = $_SESSION['user_name'] ?? $_SESSION['temp_user_name'];
$isTemp = isset($_SESSION['temp_user_id']);

// Handle device removal
$message = '';
$error = '';
$activeDevices = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_device'])) {
    $deviceId = intval($_POST['device_id']);

    $result = deactivateDevice($db_mysql, $userId, $deviceId);

    if ($result['success']) {
        $message = "Device removed successfully. You can now log in with your new device.";
    } else {
        $error = $result['message'];
    }
}

// Get active devices
$activeDevices = getUserActiveDevices($db_mysql, $userId);

function formatDate($dateString)
{
    $date = new DateTime($dateString);
    $now = new DateTime();
    $diff = $date->diff($now);

    if ($diff->days == 0) {
        if ($diff->h == 0) {
            return $diff->i . ' minutes ago';
        }
        return $diff->h . ' hours ago';
    } elseif ($diff->days == 1) {
        return 'Yesterday at ' . $date->format('g:i A');
    } else {
        return $date->format('M j, Y - g:i A');
    }
}

// If user is here via temp session and has less than 3 devices, something is wrong
// Just redirect them to login
if ($isTemp && count($activeDevices) < 3) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Devices - LiveRW</title>
    <link rel="icon" type="image/png" href="assets/favicon_2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="auth.css">
    <style>
        .devices-container {
            margin-top: 20px;
        }

        .device-card {
            background-color: #1e1e1e;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .device-info {
            flex: 1;
        }

        .device-name {
            font-weight: 600;
            color: white;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .device-name i {
            margin-right: 10px;
            color: #1C6EA4;
        }

        .device-meta {
            color: #a0a0a0;
            font-size: 0.9rem;
        }

        .device-meta span {
            display: block;
            margin-bottom: 3px;
        }

        .current-device {
            background-color: rgba(28, 110, 164, 0.2);
            border: 1px solid #1C6EA4;
        }

        .current-badge {
            display: inline-block;
            background-color: #1C6EA4;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            margin-left: 10px;
        }

        .device-actions form {
            margin: 0;
        }

        .btn-remove {
            background-color: #e53170;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-remove:hover {
            background-color: #d32f2f;
        }

        .info-banner {
            background-color: #1a1a1a;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #FFC700;
        }

        .info-banner h3 {
            margin-bottom: 10px;
            color: #FFC700;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-back {
            background-color: transparent;
            border: 1px solid #333;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            flex: 1;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background-color: #333;
        }

        .btn-logout-all {
            background-color: #e53170;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            text-align: center;
            flex: 1;
            transition: all 0.3s;
        }

        .btn-logout-all:hover {
            background-color: #d32f2f;
        }
    </style>
</head>

<body class="auth-page">
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

    <!-- Device Management Container -->
    <div class="auth-container">
        <div class="auth-card" style="max-width: 700px;">
            <div class="auth-header">
                <h1>Manage Your Devices</h1>
                <p>You can have a maximum of 3 devices logged in at the same time.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($isTemp): ?>
                <div class="info-banner">
                    <h3><i class="fas fa-info-circle"></i> Device Limit Reached</h3>
                    <p>You have reached the maximum number of devices (3) that can be logged in simultaneously. Please
                        remove a device to continue with your new login.</p>
                </div>
            <?php endif; ?>

            <div class="devices-container">
                <h2>Active Devices (<?php echo count($activeDevices); ?>/3)</h2>

                <?php foreach ($activeDevices as $device):
                    // Determine if this is the current device
                    $isCurrentDevice = false;
                    if (!$isTemp && isset($_SESSION['device_id']) && $_SESSION['device_id'] == $device['id']) {
                        $isCurrentDevice = true;
                    }

                    // Determine device icon
                    $deviceIcon = 'fas fa-desktop';
                    if (strpos($device['device_name'], 'iPhone') !== false || strpos($device['device_name'], 'Android Phone') !== false) {
                        $deviceIcon = 'fas fa-mobile-alt';
                    } elseif (strpos($device['device_name'], 'iPad') !== false || strpos($device['device_name'], 'Android Tablet') !== false) {
                        $deviceIcon = 'fas fa-tablet-alt';
                    }
                    ?>
                    <div class="device-card <?php echo $isCurrentDevice ? 'current-device' : ''; ?>">
                        <div class="device-info">
                            <div class="device-name">
                                <i class="<?php echo $deviceIcon; ?>"></i>
                                <?php echo $device['device_name']; ?>
                                <?php if ($isCurrentDevice): ?>
                                    <span class="current-badge">Current Device</span>
                                <?php endif; ?>
                            </div>
                            <div class="device-meta">
                                <span><i class="fas fa-clock"></i> Last active:
                                    <?php echo formatDate($device['last_active']); ?></span>
                                <span><i class="fas fa-sign-in-alt"></i> Last login:
                                    <?php echo formatDate($device['last_login']); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> IP Address:
                                    <?php echo $device['ip_address']; ?></span>
                            </div>
                        </div>
                        <div class="device-actions">
                            <?php if (!$isCurrentDevice || $isTemp): ?>
                                <form method="post" action="manage_devices.php"
                                    onsubmit="return confirm('Are you sure you want to remove this device?');">
                                    <input type="hidden" name="device_id" value="<?php echo $device['id']; ?>">
                                    <button type="submit" name="remove_device" class="btn-remove">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="action-buttons">
                <?php if (!$isTemp): ?>
                    <a href="profile.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                <?php endif; ?>

                <?php if (!$isTemp): ?>
                    <a href="logout.php?all=1" class="btn-logout-all"
                        onclick="return confirm('Are you sure you want to log out from all devices?');">
                        <i class="fas fa-sign-out-alt"></i> Log Out from All Devices
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <!-- Footer content -->
        <?php require_once 'views/footer.php'; ?>
    </footer>

    <script src="script.js"></script>
</body>

</html>