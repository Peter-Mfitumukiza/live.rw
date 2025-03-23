<?php
require_once('../config/db.php');
require_once('./includes/admin_functions.php');

// Get dashboard statistics
$stats = getDashboardStats($db_mysql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LiveRW</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/admin.css">
</head>

<body>
    <?php include('includes/admin_header.php'); ?>

    <div class="admin-container">
        <?php include('includes/admin_sidebar.php'); ?>

        <div class="admin-content">
            <div class="admin-content-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo $_SESSION['user_name']; ?>!</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_events']; ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_highlights']; ?></h3>
                        <p>Total Highlights</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_revenue']); ?> Frw</h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>

            <div class="recent-activity">
                <div class="activity-header">
                    <h2>Recent Activity</h2>
                    <a href="transactions.php" class="view-all">View All</a>
                </div>

                <div class="activity-table-container">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Event</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['recent_transactions'] as $transaction): ?>
                                <tr>
                                    <td>#<?php echo $transaction['id']; ?></td>
                                    <td><?php echo $transaction['username']; ?></td>
                                    <td><?php echo $transaction['event_title']; ?></td>
                                    <td><?php echo number_format($transaction['amount_paid']); ?> Frw</td>
                                    <td>
                                        <span
                                            class="status-badge <?php echo strtolower($transaction['payment_status']); ?>">
                                            <?php echo $transaction['payment_status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($transaction['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="upcoming-section">
                <div class="section-header">
                    <h2>Upcoming Events</h2>
                    <a href="events.php" class="view-all">View All</a>
                </div>

                <div class="upcoming-events">
                    <?php foreach ($stats['upcoming_events'] as $event): ?>
                        <div class="event-card">
                            <div class="event-thumbnail" style="background-image: url('<?php echo $event['image']; ?>');">
                                <div class="event-date">
                                    <?php echo date('M j', strtotime($event['match_date'])); ?>
                                </div>
                            </div>
                            <div class="event-details">
                                <h3><?php echo $event['title']; ?></h3>
                                <div class="event-teams">
                                    <?php echo $event['teams']['home']; ?> vs <?php echo $event['teams']['away']; ?>
                                </div>
                                <div class="event-meta">
                                    <span><i class="far fa-clock"></i>
                                        <?php echo date('g:i A', strtotime($event['match_date'])); ?></span>
                                    <span><i class="fas fa-tag"></i> <?php echo number_format($event['price']); ?>
                                        Frw</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/admin_footer.php'); ?>

    <script src="js/admin.js"></script>
</body>

</html>