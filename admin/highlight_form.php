<?php
require_once('../config/db.php');
require_once('includes/admin_functions.php');


// Get leagues for select dropdown
$leagues = getAllLeagues($db_mysql);

// Initialize variables
$highlight = [
    'id' => '',
    'title' => '',
    'description' => '',
    'image' => '',
    'duration' => '00:00',
    'date' => date('Y-m-d'),
    'video_url' => '',
    'league_id' => '',
    'league_name' => '',
    'views_count' => 0,
    'likes_count' => 0
];

$page_title = 'Add New Highlight';
$form_action = 'highlight_form.php';
$button_text = 'Create Highlight';
$is_edit = false;

// Check if editing an existing highlight
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $highlightId = intval($_GET['id']);
    $highlightData = getHighlightById($db_mysql, $highlightId);

    if ($highlightData) {
        $highlight = $highlightData;
        $page_title = 'Edit Highlight: ' . $highlight['title'];
        $form_action = 'highlight_form.php?id=' . $highlightId;
        $button_text = 'Update Highlight';
        $is_edit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $highlight['title'] = trim($_POST['title']);
    $highlight['description'] = trim($_POST['description']);
    $highlight['duration'] = trim($_POST['duration']);
    $highlight['date'] = $_POST['date'];
    $highlight['video_url'] = trim($_POST['video_url']);
    $highlight['league_id'] = intval($_POST['league_id']);

    // Check if image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        // Handle image upload
        $upload_result = uploadImage($_FILES['image'], '../uploads/highlights/');

        if ($upload_result['success']) {
            $highlight['image'] = str_replace('../', '', $upload_result['file_path']);
        } else {
            $error_message = $upload_result['message'];
        }
    }

    // Basic validation
    if (
        empty($highlight['title']) || empty($highlight['date']) ||
        empty($highlight['duration']) || empty($highlight['league_id'])
    ) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Save highlight
        if ($is_edit) {
            $result = updateHighlight($db_mysql, $highlightId, $highlight);
        } else {
            // Make sure we have an image for new highlights
            if (empty($highlight['image'])) {
                $error_message = 'Please upload an image for the highlight.';
            } else {
                $result = createHighlight($db_mysql, $highlight);
            }
        }

        if (isset($result) && $result['success']) {
            $success_message = $is_edit ? 'Highlight updated successfully.' : 'Highlight created successfully.';

            if (!$is_edit) {
                // Redirect to edit page for new highlights
                header('Location: highlight_form.php?id=' . $result['highlight_id'] . '&success=1');
                exit;
            }
        } else if (isset($result)) {
            $error_message = $result['message'];
        }
    }

    // Handle success message from redirect
    if (isset($_GET['success'])) {
        $success_message = 'Highlight created successfully.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - LiveRW Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <?php include('includes/admin_header.php'); ?>

    <div class="admin-container">
        <?php include('includes/admin_sidebar.php'); ?>

        <div class="admin-content">
            <div class="admin-content-header">
                <h1><?php echo $page_title; ?></h1>
                <p>Manage highlight details and information.</p>
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

            <div class="form-card">
                <h2><?php echo $is_edit ? 'Edit Highlight' : 'Add New Highlight'; ?></h2>

                <form action="<?php echo $form_action; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Highlight Title *</label>
                            <input type="text" id="title" name="title" class="form-control"
                                value="<?php echo htmlspecialchars($highlight['title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="league_id">League *</label>
                            <select id="league_id" name="league_id" class="form-control" required>
                                <option value="">Select League</option>
                                <?php foreach ($leagues as $league): ?>
                                    <option value="<?php echo $league['id']; ?>" <?php echo ($highlight['league_id'] == $league['id']) ? 'selected' : ''; ?>>
                                        <?php echo $league['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="date">Date *</label>
                            <input type="text" id="date" name="date" class="form-control date-input"
                                value="<?php echo $highlight['date']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="duration">Duration *</label>
                            <input type="text" id="duration" name="duration" class="form-control"
                                value="<?php echo $highlight['duration']; ?>" placeholder="00:00" required>
                            <small style="color: #a0a0a0;">Format: MM:SS (e.g. 05:30 for 5 minutes and 30
                                seconds)</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control"
                            rows="4"><?php echo htmlspecialchars($highlight['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="video_url">Video URL *</label>
                        <input type="text" id="video_url" name="video_url" class="form-control"
                            value="<?php echo htmlspecialchars($highlight['video_url']); ?>"
                            placeholder="URL to the highlight video" required>
                    </div>

                    <div class="form-group">
                        <label>Highlight Thumbnail <?php echo (!$is_edit) ? '*' : ''; ?></label>

                        <div class="image-preview" id="imagePreview">
                            <?php if (!empty($highlight['image'])): ?>
                                <img src="<?php echo '../' . $highlight['image']; ?>" alt="Highlight thumbnail">
                            <?php else: ?>
                                <div class="image-preview-placeholder">No image selected</div>
                            <?php endif; ?>
                        </div>

                        <div class="file-upload">
                            <input type="file" id="image" name="image" class="image-input" data-preview="imagePreview"
                                accept="image/*">
                            <button type="button" class="btn btn-secondary"
                                onclick="document.getElementById('image').click()">
                                <i class="fas fa-upload"></i>
                                <?php echo (!empty($highlight['image'])) ? 'Change Image' : 'Upload Image'; ?>
                            </button>
                        </div>
                    </div>

                    <?php if ($is_edit): ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Views Count</label>
                                <p><?php echo number_format($highlight['views_count']); ?></p>
                            </div>

                            <div class="form-group">
                                <label>Likes Count</label>
                                <p><?php echo number_format($highlight['likes_count']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <a href="highlights.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><?php echo $button_text; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('includes/admin_footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="js/admin.js"></script>
    <script>
        // Initialize date picker
        flatpickr(".date-input", {
            dateFormat: "Y-m-d"
        });

        // Validate duration format
        document.getElementById('duration').addEventListener('input', function (e) {
            let input = e.target;
            let value = input.value.replace(/[^0-9:]/g, '');

            // Format as MM:SS
            if (value.length > 0) {
                // Split by colon
                let parts = value.split(':');

                if (parts.length > 1) {
                    // Already has colon - format minutes and seconds
                    let minutes = parts[0].substring(0, 2);
                    let seconds = parts[1].substring(0, 2);

                    // Pad with zeros if needed
                    if (seconds.length === 1) {
                        seconds = seconds + '0';
                    }

                    value = minutes + ':' + seconds;
                } else if (value.length <= 2) {
                    // Just minutes - add seconds
                    value = value + ':00';
                } else {
                    // No colon but more than 2 digits
                    let minutes = value.substring(0, 2);
                    let seconds = value.substring(2, 4);

                    // Pad with zeros if needed
                    if (seconds.length === 1) {
                        seconds = seconds + '0';
                    }

                    value = minutes + ':' + seconds;
                }
            }

            input.value = value;
        });
    </script>
</body>

</html>