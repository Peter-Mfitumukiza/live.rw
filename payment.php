<?php
session_start();
require_once('config/db.php');
require_once('functions.php');

// Check if user is logged in
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
$event = getEventById($db_mysql, $eventId);

if (!$event) {
    header("Location: index.php");
    exit;
}

// Check if user has already paid for this event
$checkPaymentQuery = "SELECT * FROM user_events WHERE user_id = ? AND event_id = ? AND payment_status = 'completed'";
$stmt = mysqli_prepare($db_mysql, $checkPaymentQuery);
mysqli_stmt_bind_param($stmt, "ii", $userId, $eventId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    // User has already paid, redirect to the player page
    header("Location: player.php?id=" . $eventId);
    exit;
}

// Handle form submission
$error = '';
$success = '';
$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'card';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    // Generate a unique transaction reference
    $transactionRef = 'LIVERW-' . time() . '-' . $userId . '-' . $eventId;
    $amount = $event['price'] ?? 1500;

    // Store transaction info in session for verification later
    $_SESSION['payment_pending'] = [
        'transaction_ref' => $transactionRef,
        'event_id' => $eventId,
        'amount' => $amount,
        'payment_method' => $paymentMethod
    ];

    // Create a pending transaction record in the database
    $insertPendingQuery = "INSERT INTO transactions (user_id, event_id, amount_paid, currency, payment_method, 
                                                    payment_status, transaction_ref, created_at) 
                            VALUES (?, ?, ?, 'Frw', ?, 'pending', ?, NOW())";
    $stmt = mysqli_prepare($db_mysql, $insertPendingQuery);
    mysqli_stmt_bind_param($stmt, "iidss", $userId, $eventId, $amount, $paymentMethod, $transactionRef);
    mysqli_stmt_execute($stmt);

    if ($paymentMethod === 'card') {
        // KPay Card Payment integration
        // Redirect to KPay payment page or initialize KPay API
        $redirectUrl = "kpay_process.php?transaction_ref=" . $transactionRef;
        header("Location: " . $redirectUrl);
        exit;
    } elseif ($paymentMethod === 'momo') {
        $momoNumber = trim($_POST['momo_number']);
        $momoNumber = str_replace(' ', '', $momoNumber);
        $momoNetwork = trim($_POST['momo_network']);

        if (empty($momoNumber) || empty($momoNetwork)) {
            $error = 'Please provide your Mobile Money number and select your network';
        } elseif (!preg_match('/^\d{10}$/', $momoNumber)) {
            $error = 'Please enter a valid 10-digit mobile number';
        } else {
            // Update the transaction with MoMo details
            $updateMomoQuery = "UPDATE transactions SET momo_number = ?, momo_network = ? 
                                WHERE transaction_ref = ?";
            $stmt = mysqli_prepare($db_mysql, $updateMomoQuery);
            mysqli_stmt_bind_param($stmt, "sss", $momoNumber, $momoNetwork, $transactionRef);
            mysqli_stmt_execute($stmt);

            // Store the MoMo details in session
            $_SESSION['payment_pending']['momo_number'] = $momoNumber;
            $_SESSION['payment_pending']['momo_network'] = $momoNetwork;

            $redirectUrl = "kpay_momo_process.php?transaction_ref=" . $transactionRef;
            header("Location: " . $redirectUrl);
            exit;
        }
    }
}

// Check if returning from KPay with a successful payment
if (isset($_GET['status']) && $_GET['status'] === 'success' && isset($_GET['transaction_ref'])) {
    $transactionRef = $_GET['transaction_ref'];

    // Verify that this is a valid transaction from our session
    if (isset($_SESSION['payment_pending']) && $_SESSION['payment_pending']['transaction_ref'] === $transactionRef) {
        // In a real application, you would verify the payment with KPay's API

        // Update transaction status in the transactions table
        $updateTransactionQuery = "UPDATE transactions SET payment_status = 'completed', updated_at = NOW(), 
                                    payment_details = ? WHERE transaction_ref = ?";
        $paymentDetails = json_encode($_GET);
        $stmt = mysqli_prepare($db_mysql, $updateTransactionQuery);
        mysqli_stmt_bind_param($stmt, "ss", $paymentDetails, $transactionRef);

        if (mysqli_stmt_execute($stmt)) {
            // Also add to user_events to maintain compatibility with existing code
            $insertUserEventQuery = "INSERT INTO user_events (user_id, event_id, amount_paid, payment_status) 
                                    VALUES (?, ?, ?, 'completed')";
            $stmt = mysqli_prepare($db_mysql, $insertUserEventQuery);
            $amount = $_SESSION['payment_pending']['amount'];
            mysqli_stmt_bind_param($stmt, "iid", $userId, $eventId, $amount);
            mysqli_stmt_execute($stmt);

            $success = 'Payment successful! You will be redirected to watch the event.';

            // Clear the pending payment from session
            unset($_SESSION['payment_pending']);

            // Redirect to the player page after a short delay
            header("Refresh: 2; URL=player.php?id=" . $eventId);
        } else {
            $error = 'Error recording payment. Please contact support.';
        }
    } else {
        $error = 'Invalid transaction reference.';
    }
} elseif (isset($_GET['status']) && $_GET['status'] === 'failed' && isset($_GET['transaction_ref'])) {
    // Handle failed payment
    $transactionRef = $_GET['transaction_ref'];

    // Update transaction status
    $updateTransactionQuery = "UPDATE transactions SET payment_status = 'failed', updated_at = NOW(), 
                                payment_details = ? WHERE transaction_ref = ?";
    $paymentDetails = json_encode($_GET);
    $stmt = mysqli_prepare($db_mysql, $updateTransactionQuery);
    mysqli_stmt_bind_param($stmt, "ss", $paymentDetails, $transactionRef);
    mysqli_stmt_execute($stmt);

    $error = 'Payment failed. Please try again or choose a different payment method.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - LiveRW</title>
    <link rel="icon" type="image/png" href="assets/favicon_2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="auth.css">
    <style>
        /* Additional payment specific styles */
        .payment-page {
            background-color: #0a0a0a;
            background-image: linear-gradient(to bottom, rgba(10, 10, 10, 0.9), rgba(10, 10, 10, 0.95)),
                url('<?php echo $event['image']; ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .payment-container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: calc(100vh - 250px);
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .payment-details {
            width: 100%;
            max-width: 600px;
            margin-right: 30px;
        }

        .event-summary {
            background-color: #1a1a1a;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .event-image {
            width: 100%;
            height: 200px;
            border-radius: 5px;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .event-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .event-info {
            margin-bottom: 15px;
        }

        .event-info p {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            color: #a0a0a0;
        }

        .event-info i {
            width: 20px;
            margin-right: 10px;
            color: #1C6EA4;
        }

        .teams {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .team {
            flex: 1;
            text-align: center;
        }

        .vs {
            padding: 0 15px;
            color: #a0a0a0;
        }

        .price-tag {
            font-size: 24px;
            font-weight: 700;
            color: #e53170;
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #333;
        }

        .payment-card {
            background-color: #1a1a1a;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            padding: 30px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .payment-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .payment-options {
            display: flex;
            margin-bottom: 20px;
            background-color: #262626;
            border-radius: 8px;
            overflow: hidden;
        }

        .payment-option {
            flex: 1;
            text-align: center;
            padding: 15px 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-option.active {
            background-color: #1C6EA4;
        }

        .payment-option:hover:not(.active) {
            background-color: #333;
        }

        .payment-option i {
            display: block;
            font-size: 24px;
            margin-bottom: 5px;
            color: #a0a0a0;
        }

        .payment-option.active i,
        .payment-option.active span {
            color: #ffffff;
        }

        .payment-form {
            display: none;
        }

        .payment-form.active {
            display: block;
        }

        .card-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .card-icons i {
            font-size: 30px;
            color: #a0a0a0;
        }

        .momo-networks {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .momo-network {
            flex: 1;
            text-align: center;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .momo-network.active {
            border-color: #1C6EA4;
            background-color: rgba(28, 110, 164, 0.1);
        }

        .momo-network img {
            height: 30px;
            width: auto;
            margin-bottom: 5px;
        }

        .momo-network span {
            display: block;
            font-size: 12px;
            color: #a0a0a0;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            color: #4CAF50;
            font-size: 14px;
        }

        .secure-badge i {
            margin-right: 5px;
        }

        .kpay-logo {
            display: block;
            width: 80px;
            height: auto;
            margin: 0 auto 15px;
        }

        @media (max-width: 992px) {
            .payment-container {
                flex-direction: column;
                align-items: center;
            }

            .payment-details {
                margin-right: 0;
                margin-bottom: 30px;
            }
        }

        @media (max-width: 576px) {

            .payment-card,
            .event-summary {
                padding: 20px;
            }

            .payment-header h1 {
                font-size: 20px;
            }

            .event-title {
                font-size: 18px;
            }

            .momo-networks {
                flex-wrap: wrap;
            }

            .momo-network {
                flex: 0 0 calc(50% - 5px);
            }
        }
    </style>
</head>

<body class="payment-page">
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

    <div class="payment-container">
        <div class="payment-details">
            <div class="event-summary">
                <img src="<?php echo $event['image']; ?>" alt="<?php echo $event['title']; ?>" class="event-image">
                <h2 class="event-title"><?php echo $event['title']; ?></h2>

                <div class="teams">
                    <div class="team">
                        <span class="team-name"><?php echo $event['teams']['home']; ?></span>
                    </div>
                    <div class="vs">VS</div>
                    <div class="team">
                        <span class="team-name"><?php echo $event['teams']['away']; ?></span>
                    </div>
                </div>

                <div class="event-info">
                    <p><i class="far fa-calendar-alt"></i>
                        <?php echo date('F j, Y - g:i A', strtotime($event['match_date'])); ?></p>
                    <?php if (isset($event['league'])): ?>
                        <p><i class="fas fa-trophy"></i> <?php echo $event['league']; ?></p>
                    <?php endif; ?>
                    <?php if (isset($event['stage'])): ?>
                        <p><i class="fas fa-flag"></i> <?php echo $event['stage']; ?></p>
                    <?php endif; ?>
                    <p><i class="fas fa-video"></i> <?php echo $event['quality'] ?? 'HD'; ?> Quality</p>
                </div>

                <div class="price-tag">
                    <?php echo $event['price'] ?? '1500'; ?> Frw
                </div>
            </div>
        </div>

        <div class="payment-card">
            <div class="payment-header">
                <img src="assets/kpay.webp" alt="KPay" class="kpay-logo">
                <h1>Choose Payment Method</h1>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="payment-options">
                <div class="payment-option <?php echo $paymentMethod === 'card' ? 'active' : ''; ?>" data-method="card">
                    <i class="far fa-credit-card"></i>
                    <span>Card</span>
                </div>
                <div class="payment-option <?php echo $paymentMethod === 'momo' ? 'active' : ''; ?>" data-method="momo">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Mobile Money</span>
                </div>
            </div>

            <!-- Card Payment Form -->
            <form id="card-form" class="auth-form payment-form <?php echo $paymentMethod === 'card' ? 'active' : ''; ?>"
                method="post" action="payment.php?event_id=<?php echo $eventId; ?>">
                <input type="hidden" name="payment_method" value="card">

                <div class="card-icons">
                    <i class="fab fa-cc-visa"></i>
                    <i class="fab fa-cc-mastercard"></i>
                    <i class="fab fa-cc-amex"></i>
                </div>

                <p style="text-align: center; color: #a0a0a0; margin: 20px 0;">
                    Click the button below to proceed to KPay's secure payment page.
                </p>

                <button type="submit" name="process_payment" class="btn-submit">Pay with Card -
                    <?php echo $event['price'] ?? '1500'; ?> Frw</button>

                <div class="secure-badge">
                    <i class="fas fa-lock"></i> Secure payment via KPay
                </div>
            </form>

            <!-- Mobile Money Form -->
            <form id="momo-form" class="auth-form payment-form <?php echo $paymentMethod === 'momo' ? 'active' : ''; ?>"
                method="post" action="payment.php?event_id=<?php echo $eventId; ?>">
                <input type="hidden" name="payment_method" value="momo">

                <div class="form-group">
                    <label>Select your mobile money provider</label>
                    <div class="momo-networks">
                        <div class="momo-network <?php echo isset($_POST['momo_network']) && $_POST['momo_network'] === 'mtn' ? 'active' : ''; ?>"
                            data-network="mtn">
                            <img src="assets/mtn_momo.jpg" alt="MTN MoMo">
                            <span>MTN Mobile Money</span>
                        </div>
                        <div class="momo-network <?php echo isset($_POST['momo_network']) && $_POST['momo_network'] === 'airtel' ? 'active' : ''; ?>"
                            data-network="airtel">
                            <img src="assets/airtel_money.png" alt="Airtel Money">
                            <span>Airtel Money</span>
                        </div>
                    </div>
                    <input type="hidden" id="momo_network" name="momo_network"
                        value="<?php echo isset($_POST['momo_network']) ? $_POST['momo_network'] : 'mtn'; ?>">
                </div>

                <div class="form-group">
                    <label for="momo_number">Mobile Money Number</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" id="momo_number" name="momo_number" placeholder="07X XXX XXXX"
                            value="<?php echo isset($_POST['momo_number']) ? $_POST['momo_number'] : ''; ?>" required>
                    </div>
                    <div class="password-requirements">
                        <p>Enter the phone number registered with your Mobile Money account</p>
                    </div>
                </div>

                <button type="submit" name="process_payment" class="btn-submit">Pay with Mobile Money -
                    <?php echo $event['price'] ?? '1500'; ?> Frw</button>

                <div class="secure-badge">
                    <i class="fas fa-lock"></i> You'll receive a prompt on your phone to authorize payment
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'views/footer.php'; ?>

    <script src="script.js"></script>
    <script>
        // Payment method toggle
        const paymentOptions = document.querySelectorAll('.payment-option');
        const paymentForms = document.querySelectorAll('.payment-form');

        paymentOptions.forEach(option => {
            option.addEventListener('click', function () {
                const method = this.getAttribute('data-method');

                // Update payment options
                paymentOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');

                // Update forms
                paymentForms.forEach(form => form.classList.remove('active'));
                document.getElementById(method + '-form').classList.add('active');

                // Update hidden input for form submission
                document.querySelector('input[name="payment_method"]').value = method;
            });
        });

        // Mobile Money network selection
        const momoNetworks = document.querySelectorAll('.momo-network');
        const momoNetworkInput = document.getElementById('momo_network');

        momoNetworks.forEach(network => {
            network.addEventListener('click', function () {
                const networkValue = this.getAttribute('data-network');

                // Update network selection
                momoNetworks.forEach(net => net.classList.remove('active'));
                this.classList.add('active');

                // Update hidden input
                momoNetworkInput.value = networkValue;
            });
        });

        // Mobile number formatting
        document.getElementById('momo_number').addEventListener('input', function (e) {
            let input = e.target;
            let value = input.value.replace(/\D/g, '');

            if (value.length > 10) {
                value = value.substring(0, 10);
            }

            // Format the number as needed
            if (value.length > 6) {
                value = value.substring(0, 3) + ' ' + value.substring(3, 6) + ' ' + value.substring(6);
            } else if (value.length > 3) {
                value = value.substring(0, 3) + ' ' + value.substring(3);
            }

            input.value = value;
        });
    </script>
</body>

</html>