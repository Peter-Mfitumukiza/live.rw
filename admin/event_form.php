<?php
require_once('../config/db.php');
require_once('includes/admin_functions.php');

// Get leagues and teams for select dropdowns
$leagues = getAllLeagues($db_mysql);
$teams = getAllTeams($db_mysql);
$sports = getAllSports($db_mysql);

// Initialize variables
$event = [
    'id' => '',
    'title' => '',
    'description' => '',
    'image' => '',
    'category' => 'Live',
    'quality' => 'HD',
    'match_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'stage' => '',
    'status' => 'scheduled',
    'is_paid' => 1,
    'price' => 1500,
    'league_id' => '',
    'league_name' => '',
    'stream_url' => '',
    'featured' => 0,
    'home_team_id' => '',
    'away_team_id' => '',
    'home_team' => '',
    'away_team' => '',
    'sport_id' => ''
];

$page_title = 'Add New Event';
$form_action = 'event_form.php';
$button_text = 'Create Event';
$is_edit = false;

// Check if editing an existing event
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $eventId = intval($_GET['id']);
    $eventData = getEventById($db_mysql, $eventId);

    if ($eventData) {
        $event = $eventData;
        $page_title = 'Edit Event: ' . $event['title'];
        $form_action = 'event_form.php?id=' . $eventId;
        $button_text = 'Update Event';
        $is_edit = true;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $event['title'] = trim($_POST['title']);
    $event['description'] = trim($_POST['description']);
    $event['category'] = $_POST['category'];
    $event['quality'] = $_POST['quality'];
    $event['match_date'] = $_POST['match_date'];
    $event['stage'] = trim($_POST['stage']);
    $event['status'] = $_POST['status'];
    $event['is_paid'] = isset($_POST['is_paid']) ? 1 : 0;
    $event['price'] = floatval($_POST['price']);
    $event['league_id'] = intval($_POST['league_id']);
    $event['stream_url'] = trim($_POST['stream_url']);
    $event['featured'] = isset($_POST['featured']) ? 1 : 0;
    $event['home_team_id'] = intval($_POST['home_team_id']);
    $event['away_team_id'] = intval($_POST['away_team_id']);
    $event['sport_id'] = intval($_POST['sport_id']);

    // Check if image was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        // Handle image upload
        $upload_result = uploadImage($_FILES['image'], '../uploads/events/');

        if ($upload_result['success']) {
            $event['image'] = str_replace('../', '', $upload_result['file_path']);
        } else {
            $error_message = $upload_result['message'];
        }
    }

    // Basic validation
    if (
        empty($event['title']) || empty($event['match_date']) ||
        empty($event['league_id']) || empty($event['home_team_id']) ||
        empty($event['away_team_id']) || empty($event['sport_id']) 
    ) {
        $error_message = 'Please fill in all required fields.';
    } else {
        // Save event
        if ($is_edit) {
            $result = updateEvent($db_mysql, $eventId, $event);
        } else {
            // Make sure we have an image for new events
            if (empty($event['image'])) {
                $error_message = 'Please upload an image for the event.';
            } else {
                $result = createEvent($db_mysql, $event);
            }
        }

        if (isset($result) && $result['success']) {
            $success_message = $is_edit ? 'Event updated successfully.' : 'Event created successfully.';

            if (!$is_edit) {
                // Redirect to edit page for new events
                header('Location: event_form.php?id=' . $result['event_id'] . '&success=1');
                exit;
            }
        } else if (isset($result)) {
            $error_message = $result['message'];
        }
    }

    // Handle success message from redirect
    if (isset($_GET['success'])) {
        $success_message = 'Event created successfully.';
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
                <p>Manage event details and information.</p>
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
                <h2><?php echo $is_edit ? 'Edit Event' : 'Add New Event'; ?></h2>

                <form action="<?php echo $form_action; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Event Title *</label>
                            <input type="text" id="title" name="title" class="form-control"
                                value="<?php echo htmlspecialchars($event['title']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="league_id">League *</label>
                            <select id="league_id" name="league_id" class="form-control" required>
                                <option value="">Select League</option>
                                <?php foreach ($leagues as $league): ?>
                                    <option value="<?php echo $league['id']; ?>" <?php echo ($event['league_id'] == $league['id']) ? 'selected' : ''; ?>>
                                        <?php echo $league['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="sport_id">Sport *</label>
                            <select id="sport_id" name="sport_id" class="form-control" required>
                                <option value="">Select Sport</option>
                                <?php foreach ($sports as $sport): ?>
                                    <option value="<?php echo $sport['id']; ?>" <?php echo ($event['sport_id'] == $sport['id']) ? 'selected' : ''; ?>>
                                        <?php echo $sport['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="home_team_id">Home Team *</label>
                            <select id="home_team_id" name="home_team_id" class="form-control" required>
                                <option value="">Select Home Team</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo $team['id']; ?>" <?php echo ($event['home_team_id'] == $team['id']) ? 'selected' : ''; ?>>
                                        <?php echo $team['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="away_team_id">Away Team *</label>
                            <select id="away_team_id" name="away_team_id" class="form-control" required>
                                <option value="">Select Away Team</option>
                                <?php foreach ($teams as $team): ?>
                                    <option value="<?php echo $team['id']; ?>" <?php echo ($event['away_team_id'] == $team['id']) ? 'selected' : ''; ?>>
                                        <?php echo $team['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="match_date">Match Date & Time *</label>
                            <input type="text" id="match_date" name="match_date" class="form-control datetime-input"
                                value="<?php echo $event['match_date']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="stage">Stage/Round</label>
                            <input type="text" id="stage" name="stage" class="form-control"
                                value="<?php echo htmlspecialchars($event['stage']); ?>"
                                placeholder="e.g. Quarter Finals, Week 5">
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="scheduled" <?php echo ($event['status'] == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="live" <?php echo ($event['status'] == 'live') ? 'selected' : ''; ?>>Live
                                </option>
                                <option value="completed" <?php echo ($event['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo ($event['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="postponed" <?php echo ($event['status'] == 'postponed') ? 'selected' : ''; ?>>Postponed</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="Live" <?php echo ($event['category'] == 'Live') ? 'selected' : ''; ?>>Live
                                </option>
                                <option value="Upcoming" <?php echo ($event['category'] == 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="Replay" <?php echo ($event['category'] == 'Replay') ? 'selected' : ''; ?>>
                                    Replay</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quality">Quality</label>
                            <select id="quality" name="quality" class="form-control">
                                <option value="HD" <?php echo ($event['quality'] == 'HD') ? 'selected' : ''; ?>>HD
                                </option>
                                <option value="4K" <?php echo ($event['quality'] == '4K') ? 'selected' : ''; ?>>4K
                                </option>
                                <option value="SD" <?php echo ($event['quality'] == 'SD') ? 'selected' : ''; ?>>SD
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control"
                            rows="4"><?php echo htmlspecialchars($event['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="stream_url">Stream URL</label>
                        <input type="text" id="stream_url" name="stream_url" class="form-control"
                            value="<?php echo htmlspecialchars($event['stream_url']); ?>"
                            placeholder="URL to stream the event">
                    </div>

                    <div class="form-group">
                        <label>Event Image <?php echo (!$is_edit) ? '*' : ''; ?></label>

                        <div class="image-preview" id="imagePreview">
                            <?php if (!empty($event['image'])): ?>
                                <img src="<?php echo '../' . $event['image']; ?>" alt="Event image">
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
                                <?php echo (!empty($event['image'])) ? 'Change Image' : 'Upload Image'; ?>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="is_paid" name="is_paid" <?php echo ($event['is_paid']) ? 'checked' : ''; ?>>
                            <label for="is_paid">This is a paid event</label>
                        </div>
                    </div>

                    <div class="form-group" id="priceGroup" <?php echo (!$event['is_paid']) ? 'style="display: none;"' : ''; ?>>
                        <label for="price">Price (Frw)</label>
                        <input type="number" id="price" name="price" class="form-control"
                            value="<?php echo $event['price']; ?>" min="0">
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" id="featured" name="featured" <?php echo ($event['featured']) ? 'checked' : ''; ?>>
                            <label for="featured">Feature this event on homepage</label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="events.php" class="btn btn-secondary">Cancel</a>
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
        // Initialize datetime picker
        flatpickr(".datetime-input", {
            enableTime: true,
            dateFormat: "Y-m-d H:i:s",
            time_24hr: true
        });

        // Toggle price field based on is_paid checkbox
        document.getElementById('is_paid').addEventListener('change', function () {
            const priceGroup = document.getElementById('priceGroup');
            priceGroup.style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>

</html>