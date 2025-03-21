<?php

session_start();

require_once('config/db.php');

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Handle registration form submission
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        // Check if email already exists
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($db_mysql, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Email already exists. Please use a different email or try logging in.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into database
            $insert_query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($db_mysql, $insert_query);
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($db_mysql);

                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                header('Location: index.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again later.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - LiveRW</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="auth.css">
</head>

<body class="auth-page">
    <!-- Navbar -->
    <?php require_once 'views/navbar.php'; ?>

    <!-- Registration Form Container -->
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Join our community and enjoy unlimited access to live sports events.</p>
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

            <form class="auth-form" method="post" action="register.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                        <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-requirements">
                        <p>Password must be at least 8 characters long</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="Confirm your password" required>
                    </div>
                </div>

                <div class="form-options">
                    <div class="agree-terms">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy
                                Policy</a></label>
                    </div>
                </div>

                <button type="submit" name="register" class="btn-submit">Create Account</button>

                <!-- <div class="social-login">
                    <p>Or sign up with</p>
                    <div class="social-buttons">
                        <button type="button" class="btn-social btn-google">
                            <i class="fab fa-google"></i> Google
                        </button>
                        <button type="button" class="btn-social btn-facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </button>
                    </div>
                </div> -->
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <div class="logo">
                    <img src="assets/logo_without_bg.png" alt="LiveRW Logo" class="logo-image">
                </div>
                <p class="footer-tagline">Watch sports matches and highlights online in HD quality</p>
            </div>

            <div class="footer-nav">
                <nav class="footer-links">
                    <a href="#">About Us</a>
                    <a href="#">Terms Of Use</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">FAQ</a>
                    <a href="#">Contact Us</a>
                    <a href="https://x.com/livedotrw">X (Twitter)</a>
                    <a href="https://www.instagram.com/livedotrw">Instagram</a>
                </nav>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> LiveRW. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        // Toggle password visibility
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(button => {
            button.addEventListener('click', function () {
                const passwordInput = this.parentElement.querySelector('input');
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
        });

        // Password strength validation
        const passwordInput = document.getElementById('password');
        const requirementsElement = document.querySelector('.password-requirements p');

        if (passwordInput && requirementsElement) {
            passwordInput.addEventListener('input', function () {
                const password = this.value;
                let requirementsMet = true;

                if (password.length < 8) {
                    requirementsElement.textContent = 'Password must be at least 8 characters long';
                    requirementsElement.style.color = '#e53170';
                    requirementsMet = false;
                } else {
                    requirementsElement.textContent = 'Password requirements met!';
                    requirementsElement.style.color = '#4CAF50';
                }

                // You can add more password requirements here (uppercase, special chars, etc.)
            });
        }
    </script>
</body>

</html>