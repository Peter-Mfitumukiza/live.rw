<?php
// dashboard.php - User dashboard page

session_start();
require_once('config/db.php');
require_once('functions.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get user's purchased events
$purchasedEvents = [];
$query = "SELECT ue.*, e.title, e.image, e.match_date, e.status, 
                 home.name AS home_team, away.name AS away_team, l.name AS league_name
          FROM user_events ue
          JOIN events e ON ue.event_id = e.id
          LEFT JOIN leagues l ON e.league_id = l.id
          LEFT JOIN event_teams et_home ON e.id = et_home.event_id AND et_home.is_home = 1
          LEFT JOIN event_teams et_away ON e.id = et_away.event_id AND et_away.is_home = 0
          LEFT JOIN teams home ON et_home.team_id = home.id
          LEFT JOIN teams away ON et_away.team_id = away.id
          WHERE ue.user_id = ? AND ue.payment_status = 'completed'
          ORDER BY e.match_date DESC";

$stmt = mysqli_prepare($db_mysql, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($event = mysqli_fetch_assoc($result)) {
    $purchasedEvents[] = $event;
}

// Get user's active devices
$activeDevices = getUserActiveDevices($db_mysql, $userId);

// Format date for better readability
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

function formatMatchDate($dateString)
{
    $date = new DateTime($dateString);
    return $date->format('M j, Y - g:i A');
}

// Check if event is upcoming, live, or past
function getEventStatus($matchDate, $status)
{
    $now = new DateTime();
    $match = new DateTime($matchDate);

    if ($status === 'live') {
        return 'live';
    } elseif ($match > $now) {
        return 'upcoming';
    } else {
        return 'past';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - LiveRW</title>
    <link rel="icon" type="image/png" href="assets/favicon_2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Dashboard specific styles */
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .dashboard-header {
            margin-bottom: 30px;
        }

        .dashboard-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .dashboard-header p {
            color: #a0a0a0;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .dashboard-card {
            background-color: #1a1a1a;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .dashboard-card h2 {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
            display: flex;
            align-items: center;
        }

        .dashboard-card h2 i {
            margin-right: 10px;
            color: #1C6EA4;
        }

        .event-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .event-item {
            display: flex;
            background-color: #232323;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .event-item:hover {
            transform: translateY(-3px);
        }

        .event-image {
            width: 120px;
            height: 80px;
            object-fit: cover;
        }

        .event-details {
            flex: 1;
            padding: 10px 15px;
            display: flex;
            flex-direction: column;
        }

        .event-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .event-meta {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #a0a0a0;
            margin-bottom: 5px;
        }

        .event-teams {
            font-size: 12px;
            color: #d1d1d1;
        }

        .event-status {
            margin-left: auto;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            margin-top: auto;
            align-self: flex-start;
        }

        .status-live {
            background-color: rgba(229, 49, 112, 0.2);
            color: #e53170;
        }

        .status-upcoming {
            background-color: rgba(28, 110, 164, 0.2);
            color: #1C6EA4;
        }

        .status-past {
            background-color: rgba(160, 160, 160, 0.2);
            color: #a0a0a0;
        }

        .event-actions {
            padding: 10px 15px;
            align-self: center;
        }

        .btn-watch {
            background-color: #e53170;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .btn-watch:hover {
            background-color: #d92c66;
        }

        .devices-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .device-item {
            background-color: #232323;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .device-info {
            flex: 1;
        }

        .device-name {
            font-weight: 600;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .device-name i {
            margin-right: 10px;
            color: #1C6EA4;
        }

        .device-meta {
            font-size: 12px;
            color: #a0a0a0;
        }

        .device-meta span {
            display: block;
            margin-bottom: 2px;
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
            font-size: 12px;
            margin-left: 8px;
        }

        .btn-remove {
            background-color: transparent;
            color: #e53170;
            border: 1px solid #e53170;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-remove:hover {
            background-color: #e53170;
            color: white;
        }

        .manage-link {
            display: block;
            text-align: center;
            color: #1C6EA4;
            text-decoration: none;
            margin-top: 15px;
            font-size: 14px;
            transition: color 0.3s;
        }

        .manage-link:hover {
            color: #FFC700;
            text-decoration: underline;
        }

        .empty-message {
            color: #a0a0a0;
            text-align: center;
            padding: 30px 0;
            font-style: italic;
        }

        @media (max-width: 900px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .event-item {
                flex-direction: column;
            }

            .event-image {
                width: 100%;
                height: 120px;
            }

            .event-actions {
                padding: 0 15px 15px;
                align-self: flex-end;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo $userName; ?>!</h1>
            <p>Manage your account, view purchased events, and control device access.</p>
        </div>

        <div class="dashboard-grid">
            <div class="main-content">
                <!-- Purchased Events Section -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-ticket-alt"></i> My Purchased Events</h2>

                    <?php if (empty($purchasedEvents)): ?>
                        <div class="empty-message">
                            <p>You haven't purchased any events yet.</p>
                            <p>Browse our upcoming events and secure your access today!</p>
                        </div>
                    <?php else: ?>
                        <div class="event-list">
                            <?php foreach ($purchasedEvents as $event):
                                $eventStatus = getEventStatus($event['match_date'], $event['status']);
                                ?>
                                <div class="event-item">
                                    <img src="<?php echo $event['image']; ?>" alt="<?php echo $event['title']; ?>"
                                        class="event-image">

                                    <div class="event-details">
                                        <div class="event-title"><?php echo $event['title']; ?></div>

                                        <div class="event-meta">
                                            <span><?php echo formatMatchDate($event['match_date']); ?></span>
                                            <span><?php echo $event['league_name']; ?></span>
                                        </div>

                                        <div class="event-teams">
                                            <?php echo $event['home_team']; ?> vs <?php echo $event['away_team']; ?>
                                        </div>

                                        <span class="event-status status-<?php echo $eventStatus; ?>">
                                            <?php
                                            if ($eventStatus === 'live')
                                                echo 'LIVE NOW';
                                            elseif ($eventStatus === 'upcoming')
                                                echo 'UPCOMING';
                                            else
                                                echo 'COMPLETED';
                                            ?>
                                        </span>
                                    </div>

                                    <div class="event-actions">
                                        <a href="player.php?id=<?php echo $event['event_id']; ?>" class="btn-watch">
                                            <?php if ($eventStatus === 'live'): ?>
                                                <i class="fas fa-play"></i> Watch Now
                                            <?php elseif ($eventStatus === 'upcoming'): ?>
                                                <i class="far fa-calendar-alt"></i> Details
                                            <?php else: ?>
                                                <i class="fas fa-play"></i> Replay
                                            <?php endif; ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar">
                <!-- Devices Section -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-mobile-alt"></i> My Devices (<?php echo count($activeDevices); ?>/3)</h2>

                    <?php if (empty($activeDevices)): ?>
                        <div class="empty-message">
                            <p>No active devices found.</p>
                        </div>
                    <?php else: ?>
                        <div class="devices-list">
                            <?php foreach ($activeDevices as $device):
                                // Determine if this is the current device
                                $isCurrentDevice = false;
                                if (isset($_SESSION['device_id']) && $_SESSION['device_id'] == $device['id']) {
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
                                <div class="device-item <?php echo $isCurrentDevice ? 'current-device' : ''; ?>">
                                    <div class="device-info">
                                        <div class="device-name">
                                            <i class="<?php echo $deviceIcon; ?>"></i>
                                            <?php echo $device['device_name']; ?>
                                            <?php if ($isCurrentDevice): ?>
                                                <span class="current-badge">Current Device</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="device-meta">
                                            <span>Last active: <?php echo formatDate($device['last_active']); ?></span>
                                        </div>
                                    </div>

                                    <?php if (!$isCurrentDevice): ?>
                                        <form method="post" action="manage_devices.php">
                                            <input type="hidden" name="device_id" value="<?php echo $device['id']; ?>">
                                            <button type="submit" name="remove_device" class="btn-remove">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <a href="manage_devices.php" class="manage-link">Manage All Devices</a>
                    <?php endif; ?>
                </div>

                <!-- Account Summary Section -->
                <div class="dashboard-card">
                    <h2><i class="fas fa-user-circle"></i> Account Summary</h2>

                    <div style="margin-bottom: 15px;">
                        <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                            <span style="color: #a0a0a0;">Email:</span>
                            <span><?php echo $_SESSION['user_email']; ?></span>
                        </div>

                        <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                            <span style="color: #a0a0a0;">Phone:</span>
                            <span><?php echo $_SESSION['user_phone']; ?></span>
                        </div>

                        <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                            <span style="color: #a0a0a0;">Purchased Events:</span>
                            <span><?php echo count($purchasedEvents); ?></span>
                        </div>

                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #a0a0a0;">Active Devices:</span>
                            <span><?php echo count($activeDevices); ?>/3</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: center; margin-top: 20px;">
                        <a href="profile.php"
                            style="background-color: #1C6EA4; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-size: 14px; transition: background-color 0.3s;">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'views/footer.php'; ?>

    <script src="script.js"></script>
</body>

</html>