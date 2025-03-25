<?php

session_start();

require_once('config/db.php');
require_once('functions.php'); // Include the device management functions

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($login_id) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Determine if login_id is an email or phone number
        $is_email = filter_var($login_id, FILTER_VALIDATE_EMAIL);

        if ($is_email) {
            $query = "SELECT id, name, email, phone, role, password, status FROM users WHERE email = ?";
        } else {
            $query = "SELECT id, name, email, phone, role, password, status FROM users WHERE phone = ?";
        }

        $stmt = mysqli_prepare($db_mysql, $query);
        mysqli_stmt_bind_param($stmt, "s", $login_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Check if account is active
            if ($user['status'] !== 'active') {
                $error = 'Your account is not active. Please contact support.';
            }
            // Verify password
            else if (password_verify($password, $user['password'])) {
                // Check device limit before login
                $deviceResult = registerDeviceLogin($db_mysql, $user['id']);

                if (!$deviceResult['success'] && isset($deviceResult['limit_reached'])) {
                    // User has reached device limit, show device management page
                    $_SESSION['temp_user_id'] = $user['id']; // Temporary session just for device management
                    $_SESSION['temp_user_name'] = $user['name'];
                    header('Location: manage_devices.php');
                    exit;
                }

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['device_id'] = $deviceResult['device_id']; // Store the device ID

                // Update last login timestamp
                $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($db_mysql, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                mysqli_stmt_execute($update_stmt);

                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days

                    setcookie('remember_token', $token, $expires, '/');
                }

                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                    exit;
                }

                // Redirect to homepage after successful login
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid credentials';
            }
        } else {
            $error = 'Invalid credentials';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - LiveRW</title>
    <link rel="icon" type="image/png" href="assets/favicon_2.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="auth.css">
</head>

<body class="auth-page">
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

    <!-- Login Form Container -->
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Sign In</h1>
                <p>Welcome back! Sign in to continue watching live sports.</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="login.php">
                <div class="form-group">
                    <label for="login_id">Email or Phone</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="login_id" name="login_id" placeholder="Enter your email or phone"
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" name="login" class="btn-submit">Sign In</button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <!-- Footer content -->
        <?php require_once 'views/footer.php'; ?>
    </footer>

    <script src="script.js"></script>
    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>

</html>