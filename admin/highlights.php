<?php
require_once('../config/db.php');
require_once('includes/admin_functions.php');

// Handle highlight deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $highlightId = intval($_GET['delete']);
    $result = deleteHighlight($db_mysql, $highlightId);

    if ($result['success']) {
        $success_message = "Highlight deleted successfully.";
    } else {
        $error_message = "Error deleting highlight: " . $result['message'];
    }
}

// Get pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get highlights data
$highlightsData = getAllHighlights($db_mysql, $page, 10, $search);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Highlights - LiveRW Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include('includes/admin_header.php'); ?>

    <div class="admin-container">
        <?php include('includes/admin_sidebar.php'); ?>

        <div class="admin-content">
            <div class="admin-content-header">
                <h1>Manage Highlights</h1>
                <p>Create, edit and manage all highlights on the platform.</p>
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
                    <h2>All Highlights</h2>
                    <div class="table-actions">
                        <form action="highlights.php" method="get" class="admin-search" style="margin-right: 0;">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search highlights..."
                                value="<?php echo htmlspecialchars($search); ?>">
                        </form>
                        <a href="highlight_form.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Highlight
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
                                <th>League</th>
                                <th>Duration</th>
                                <th>Date</th>
                                <th>Views/Likes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($highlightsData['highlights']) > 0): ?>
                                <?php foreach ($highlightsData['highlights'] as $highlight): ?>
                                    <tr>
                                        <td><?php echo $highlight['id']; ?></td>
                                        <td>
                                            <img src="../<?php echo $highlight['image']; ?>"
                                                alt="<?php echo $highlight['title']; ?>" class="table-img">
                                        </td>
                                        <td><?php echo $highlight['title']; ?></td>
                                        <td><?php echo $highlight['league_name']; ?></td>
                                        <td><?php echo $highlight['duration']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($highlight['date'])); ?></td>
                                        <td>
                                            <i class="fas fa-eye" style="color: #1C6EA4;"></i>
                                            <?php echo number_format($highlight['views_count']); ?>
                                            <span style="margin: 0 5px;">|</span>
                                            <i class="fas fa-heart" style="color: #e53170;"></i>
                                            <?php echo number_format($highlight['likes_count']); ?>
                                        </td>
                                        <td>
                                            <div class="row-actions">
                                                <a href="highlight_form.php?id=<?php echo $highlight['id']; ?>"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="highlights.php?delete=<?php echo $highlight['id']; ?>"
                                                    class="btn btn-sm btn-danger delete-btn">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No highlights found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($highlightsData['total_pages'] > 1): ?>
                    <div class="pagination">
                        <ul class="pagination-list">
                            <?php if ($page > 1): ?>
                                <li class="pagination-item">
                                    <a
                                        href="highlights.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $highlightsData['total_pages']; $i++): ?>
                                <li class="pagination-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a href="highlights.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $highlightsData['total_pages']): ?>
                                <li class="pagination-item">
                                    <a
                                        href="highlights.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
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