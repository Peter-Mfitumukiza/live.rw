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

// In a real implementation, this is where you would initialize the KPay API

// Update the transaction with additional details
$updateQuery = "UPDATE transactions SET payment_details = ? WHERE transaction_ref = ?";
$paymentDetails = json_encode(['processing_started' => true, 'timestamp' => date('Y-m-d H:i:s')]);
$stmt = mysqli_prepare($db_mysql, $updateQuery);
mysqli_stmt_bind_param($stmt, "ss", $paymentDetails, $transactionRef);
mysqli_stmt_execute($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPay Card Payment - LiveRW</title>
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
            color: #1C6EA4;
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

        .mock-form {
            background-color: #1a1a1a;
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
            margin: 0 auto;
        }

        .mock-form h3 {
            margin-bottom: 15px;
            color: #ffffff;
        }

        .form-field {
            margin-bottom: 15px;
        }

        .form-field label {
            display: block;
            margin-bottom: 5px;
            color: #d1d1d1;
        }

        .form-field input {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            background-color: #262626;
            color: #ffffff;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

    <div class="processing-container">
        <i class="fas fa-credit-card processing-icon"></i>
        <h1 class="processing-title">KPay Card Payment</h1>
        <p class="processing-text">
            Please complete your payment details below. After successful payment, you will be redirected back to LiveRW.
        </p>

        <div class="mock-form">
            <h3>Enter Card Details</h3>
            <form action="payment.php?event_id=<?= $eventId ?>&status=success&transaction_ref=<?= $transactionRef ?>"
                method="post">
                <div class="form-field">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" placeholder="4242 4242 4242 4242" required>
                </div>

                <div class="form-field">
                    <label for="card_name">Cardholder Name</label>
                    <input type="text" id="card_name" name="card_name" placeholder="John Doe" required>
                </div>

                <div style="display: flex; gap: 10px;">
                    <div class="form-field" style="flex: 1;">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" id="expiry" name="expiry" placeholder="MM/YY" required>
                    </div>

                    <div class="form-field" style="flex: 1;">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="margin-top: 10px;">Pay <?= $amount ?> Frw</button>

                <div style="text-align: center; margin-top: 15px;">
                    <a href="payment.php?event_id=<?= $eventId ?>&status=failed&transaction_ref=<?= $transactionRef ?>"
                        style="color: #a0a0a0; text-decoration: none; font-size: 14px;">
                        Cancel payment
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'views/footer.php'; ?>

    <script>
        // Simulating a real payment gateway
        setTimeout(() => {
            document.querySelector('input[name="card_number"]').value = "4242 4242 4242 4242";
            document.querySelector('input[name="card_name"]').value = "John Doe";
            document.querySelector('input[name="expiry"]').value = "12/25";
            document.querySelector('input[name="cvv"]').value = "123";
        }, 500);

        // Card number formatting
        document.getElementById('card_number').addEventListener('input', function (e) {
            let input = e.target;
            let value = input.value.replace(/\D/g, '');

            if (value.length > 16) {
                value = value.substring(0, 16);
            }

            // Format with spaces every 4 digits
            value = value.replace(/(.{4})/g, '$1 ').trim();

            input.value = value;
        });

        // Expiry date formatting
        document.getElementById('expiry').addEventListener('input', function (e) {
            let input = e.target;
            let value = input.value.replace(/\D/g, '');

            if (value.length > 4) {
                value = value.substring(0, 4);
            }

            if (value.length > 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }

            input.value = value;
        });

        // CVV formatting - numbers only
        document.getElementById('cvv').addEventListener('input', function (e) {
            let input = e.target;
            let value = input.value.replace(/\D/g, '');

            if (value.length > 4) {
                value = value.substring(0, 4);
            }

            input.value = value;
        });
    </script>
</body>

</html>