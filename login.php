<?php
// Start session for login tracking
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to homepage if already logged in
    header('Location: index.php');
    exit;
}

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Simple validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // In a real implementation, you would verify credentials against a database
        // For demo purposes, we'll use a hardcoded check
        if ($email === 'demo@liverw.com' && $password === 'password123') {
            // Set session variables
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Demo User';
            $_SESSION['user_email'] = $email;
            
            // Redirect to homepage after successful login
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password';
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="auth.css">
</head>

<body class="auth-page">
    <!-- Navbar -->
    <nav class="navbar">
        <!-- Logo -->
        <div class="logo">
            <a href="index.php">
                <img src="./assets/logo_without_bg.png" alt="Live.rw logo" height="40px" width="auto">
            </a>
        </div>

        <!-- Navigation items -->
        <div class="nav-items">
            <a href="index.php">Home</a>
            <a href="#">Live</a>
            <a href="#">Sports</a>
            <a href="#">Schedule</a>
            <a href="#">Teams</a>
            <a href="#">Highlights</a>
            <a href="#">Premium</a>
        </div>

        <!-- Right section - search and auth -->
        <div class="nav-right">
            <!-- Search bar -->
            <div class="search-bar">
                <span class="search-icon">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" placeholder="Search...">
            </div>

            <!-- Auth buttons -->
            <div class="auth-buttons">
                <a href="login.php" class="sign-in active">Sign in</a>
                <a href="register.php" class="sign-up">Sign up</a>
            </div>

            <!-- Mobile menu toggle -->
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

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

                <!-- <div class="social-login">
                    <p>Or sign in with</p>
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
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
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
        document.querySelector('.toggle-password').addEventListener('click', function() {
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