<?php
session_start();
require_once('config/db.php');

// Check if transaction reference exists in session
if (
    !isset($_SESSION['payment_pending']) || !isset($_GET['transaction_ref']) ||
    $_SESSION['payment_pending']['transaction_ref'] !== $_GET['transaction_ref']
) {
    header("Location: index.php");
    exit;
}

$transactionRef = $_GET['transaction_ref'];
$eventId = $_SESSION['payment_pending']['event_id'];
$amount = $_SESSION['payment_pending']['amount'];
$momoNumber = $_SESSION['payment_pending']['momo_number'] ?? '';
$momoNetwork = $_SESSION['payment_pending']['momo_network'] ?? 'mtn';

// Update the transaction with additional details
$updateQuery = "UPDATE transactions SET payment_details = ? WHERE transaction_ref = ?";
$paymentDetails = json_encode([
    'processing_started' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'momo_number' => $momoNumber,
    'momo_network' => $momoNetwork
]);
$stmt = mysqli_prepare($db_mysql, $updateQuery);
mysqli_stmt_bind_param($stmt, "ss", $paymentDetails, $transactionRef);
mysqli_stmt_execute($stmt);

// In a real implementation, this is where you would initialize the KPay Mobile Money API
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPay Mobile Money - LiveRW</title>
    <link rel="icon" type="image/png" href="assets/favicon_2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .processing-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            text-align: center;
            padding: 20px;
        }

        .processing-icon {
            font-size: 48px;
            color:
                <?php echo $momoNetwork === 'mtn' ? '#FFCC00' : '#FF0000'; ?>
            ;
            margin-bottom: 20px;
            animation: pulse 1.5s infinite;
        }

        .processing-title {
            font-size: 24px;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .processing-text {
            color: #a0a0a0;
            max-width: 500px;
            margin-bottom: 30px;
        }

        .phone-number {
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            margin: 15px 0;
        }

        .countdown {
            background-color: #1a1a1a;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .countdown-timer {
            font-size: 32px;
            font-weight: bold;
            color: #e53170;
            margin: 10px 0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

    <div class="processing-container">
        <i class="fas fa-mobile-alt processing-icon"></i>
        <h1 class="processing-title"><?php echo ucfirst($momoNetwork); ?> Mobile Money Payment</h1>
        <p class="processing-text">
            A payment request has been sent to your mobile phone. Please check your phone and authorize the payment by
            entering your Mobile Money PIN.
        </p>

        <div class="phone-number">
            <?php echo $momoNumber; ?>
        </div>

        <div class="countdown">
            <p>The request will expire in:</p>
            <div class="countdown-timer" id="countdownTimer">05:00</div>
            <p>If you don't receive a prompt, please check your phone number and try again.</p>
        </div>

        <div class="action-buttons">
            <a href="payment.php?event_id=<?php echo $eventId; ?>&status=failed&transaction_ref=<?php echo $transactionRef; ?>"
                class="btn btn-secondary">Cancel</a>
            <a href="payment.php?event_id=<?php echo $eventId; ?>&status=success&transaction_ref=<?php echo $transactionRef; ?>"
                class="btn btn-primary" id="successBtn" style="opacity: 0.5; pointer-events: none;">
                I've Paid
            </a>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'views/footer.php'; ?>

    <script>
        // Countdown timer
        let timeLeft = 300; // 5 minutes in seconds
        const countdownTimer = document.getElementById('countdownTimer');
        const successBtn = document.getElementById('successBtn');

        const updateTimer = () => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;

            countdownTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                countdownTimer.textContent = "00:00";
                countdownTimer.style.color = "#FF0000";
            }
        };

        const timerInterval = setInterval(() => {
            timeLeft--;
            updateTimer();

            // Enable the "I've Paid" button after 10 seconds (simulating payment processing)
            if (timeLeft === 290) {
                successBtn.style.opacity = "1";
                successBtn.style.pointerEvents = "auto";
            }

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
            }
        }, 1000);

        // Initial display
        updateTimer();

        // Simulate a successful payment after 10 seconds
        setTimeout(() => {
            // Flash a notification
            const container = document.querySelector('.processing-container');
            const notification = document.createElement('div');
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.left = '50%';
            notification.style.transform = 'translateX(-50%)';
            notification.style.backgroundColor = '#4CAF50';
            notification.style.color = 'white';
            notification.style.padding = '15px 20px';
            notification.style.borderRadius = '5px';
            notification.style.zIndex = '1000';
            notification.innerHTML = '<i class="fas fa-check-circle" style="margin-right: 10px;"></i> Payment request received on your phone!';

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transition = 'opacity 0.5s';

                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 500);
            }, 5000);
        }, 10000);
    </script>
</body>

</html>