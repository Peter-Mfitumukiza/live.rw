<?php
// Start session for login tracking
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to homepage if already logged in
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
    
    // Simple validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        // In a real implementation, you would save user data to a database
        // Here we'll just show a success message
        $success = 'Registration successful! You can now sign in.';
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
                <a href="login.php" class="sign-in">Sign in</a>
                <a href="register.php" class="sign-up active">Sign up</a>
            </div>

            <!-- Mobile menu toggle -->
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

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
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                </div>

                <div class="form-options">
                    <div class="agree-terms">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
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
            button.addEventListener('click', function() {
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
            passwordInput.addEventListener('input', function() {
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