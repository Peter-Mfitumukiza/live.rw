<?php
require_once('../config/db.php');
require_once('includes/admin_functions.php');

// Handle event deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $eventId = intval($_GET['delete']);
    $result = deleteEvent($db_mysql, $eventId);

    if ($result['success']) {
        $success_message = "Event deleted successfully.";
    } else {
        $error_message = "Error deleting event: " . $result['message'];
    }
}

// Get pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get events data
$eventsData = getAllEvents($db_mysql, $page, 10, $search);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - LiveRW Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include('includes/admin_header.php'); ?>

    <div class="admin-container">
        <?php include('includes/admin_sidebar.php'); ?>

        <div class="admin-content">
            <div class="admin-content-header">
                <h1>Manage Events</h1>
                <p>Create, edit and manage all events on the platform.</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <div class="table-header">
                    <h2>All Events</h2>
                    <div class="table-actions">
                        <form action="events.php" method="get" class="admin-search" style="margin-right: 0;">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search events..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </form>
                        <a href="event_form.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Event
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Teams</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($eventsData['events']) > 0): ?>
                                <?php foreach ($eventsData['events'] as $event): ?>
                                    <tr>
                                        <td><?php echo $event['id']; ?></td>
                                        <td>
                                            <img src="<?php echo $event['image']; ?>" alt="<?php echo $event['title']; ?>"
                                                class="table-img">
                                        </td>
                                        <td>
                                            <?php echo $event['title']; ?>
                                            <div style="font-size: 12px; color: #1C6EA4;"><?php echo $event['league_name']; ?>
                                            </div>
                                        </td>
                                        <td><?php echo $event['home_team'] . ' vs ' . $event['away_team']; ?></td>
                                        <td><?php echo date('M j, Y - g:i A', strtotime($event['match_date'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($event['status']); ?>">
                                                <?php echo $event['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($event['is_paid']): ?>
                                                <?php echo number_format($event['price']); ?> Frw
                                            <?php else: ?>
                                                <span style="color: #4CAF50;">Free</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="row-actions">
                                                <a href="event_form.php?id=<?php echo $event['id']; ?>"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="events.php?delete=<?php echo $event['id']; ?>"
                                                    class="btn btn-sm btn-danger delete-btn">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No events found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($eventsData['total_pages'] > 1): ?>
                    <div class="pagination">
                        <ul class="pagination-list">
                            <?php if ($page > 1): ?>
                                <li class="pagination-item">
                                    <a href="events.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $eventsData['total_pages']; $i++): ?>
                                <li class="pagination-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a href="events.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $eventsData['total_pages']): ?>
                                <li class="pagination-item">
                                    <a href="events.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('includes/admin_footer.php'); ?>

    <script src="js/admin.js"></script>
</body>

</html>