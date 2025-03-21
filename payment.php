<?php
session_start();
require_once('config/db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

if (!$eventId) {
    header("Location: index.php");
    exit;
}

// Fetch event details
$eventQuery = $db_mysql->prepare("SELECT * FROM events WHERE id = ?");
$eventQuery->bind_param("i", $eventId);
$eventQuery->execute();
$eventResult = $eventQuery->get_result();
$eventData = $eventResult->fetch_assoc();

if (!$eventData) {
    header("Location: index.php");
    exit;
}

$eventPrice = $eventData['price']; // Assume `price` column exists
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment for <?php echo htmlspecialchars($eventData['title']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h2>Payment Required</h2>
    <p>To watch "<?php echo htmlspecialchars($eventData['title']); ?>", you need to pay
        <strong>$<?php echo $eventPrice; ?></strong>.</p>

    <form action="process_payment.php" method="POST">
        <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
        <button type="submit">Proceed to Payment</button>
    </form>
</body>

</html>