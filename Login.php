<?php
session_start();
require_once 'utils/connect.php'; // Adjust this to your actual connection file

// Initialize error and success messages
$error_message = '';
$success_message = '';

// Check if form was submitted for login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Check if username and password are set
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Get form data
        $user = $_POST['username'];
        $pass = $_POST['password'];

        // Prepare and execute query
        $sql = "SELECT * FROM registration WHERE username=? OR email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user, $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($pass, $row['password'])) {
                $_SESSION['user'] = $row['username'];
                $stmt->close();
                $conn->close();
                header("Location: Dashboard.php");
                exit();
            } else {
                // Invalid password
                $_SESSION['error_message'] = 'Invalid password. Please try again.';
            }
        } else {
            // User not found
            $_SESSION['error_message'] = 'User does not exist.';
        }

        $stmt->close();
    } else {
        // Username or password not set
        $_SESSION['error_message'] = 'Please enter username and password.';
    }
}

// Check if form was submitted for forgot password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_password'])) {
    // Check if email is set
    if (isset($_POST['email'])) {
        // Get form data
        $email = $_POST['email'];

        // Prepare and execute query
        $sql = "SELECT * FROM registration WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $password = $row['password'];

            $reset_token = bin2hex(random_bytes(16));
            $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $sql = "UPDATE registration SET reset_token=?, reset_expiry=? WHERE email=?";   
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $reset_token, $reset_expiry, $email);
            $stmt->execute();

             // Send reset link via email
             $reset_link = "http://yourdomain.com/reset_password.php?token=" . $reset_token;
             $to = $email;
             $subject = "Password Reset Request";
             $message = "Click the following link to reset your password: " . $reset_link;
             $headers = "From: no-reply@straysaver.com";

             if (mail($to, $subject, $message, $headers)) {
                $_SESSION['success_message'] = 'A password reset link has been sent to your email address.';
            } else {
                $_SESSION['error_message'] = 'Failed to send email. Please try again later.';
            }
        } else {
            // Email not found
            $_SESSION['error_message'] = 'Email not found.';
        }

        $stmt->close();
    } else {
        // Email not set
        $_SESSION['error_message'] = 'Please enter your email address.';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stray Saver - Login</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
    
    background: url('images/login-bg.jpg') no-repeat center center fixed; 
    background-size: cover; /* Ensure the background covers the whole page */
}
    </style>
</head>


<body>
<header class="header">
        <div class="header-container">
            <div class="logo">
                <img src="images/logo.png" alt="StraySaver Logo">
                <h1 class="title">StraySaver</h1>
            </div>
            <nav class="nav">
                <ul class="nav-list">
                    <li class="nav-item"><a href="index.php" class="nav-link home-button">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="welcome-section">
            <div class="welcome-text">
                <img src="images/paw.jpg" alt="Paw Logo" class="welcome-img">
                <div>
                    <h2>Welcome Back!</h2>
                    <p class="login-message">Login to access your account and manage your saved pets.</p>
                    <p class="signup-message">New here? Join our community to help rescue and find homes for stray pets!</p>

                </div>
            </div>
            <div class="button1">
                <a href="Registration.php" class="sign-up">Create Account</a>
            </div>
        </div>
        <div class="login-section">
            <div class="login-form">
                <h2>Login</h2>
                <?php
                if (isset($_SESSION['error_message']) && $_SESSION['error_message'] != '') {
                    echo '<p class="error">' . $_SESSION['error_message'] . '</p>';
                    unset($_SESSION['error_message']);
                }
                if (isset($_SESSION['success_message']) && $_SESSION['success_message'] != '') {
                    echo '<p class="success">' . $_SESSION['success_message'] . '</p>';
                    unset($_SESSION['success_message']);
                }
                ?>
                <form method="POST" action="login.php">
                    <input type="hidden" name="login" value="1">
                    <label for="username">Username/Email</label><br />
                    <input type="text" id="username" name="username" placeholder="Username or email" required><br />
                    <label for="password">Password</label><br />
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <span id="toggle-password" onclick="togglePassword()">Show/Hide password</span>
                    <br /><br />
                    <button type="submit" class="button2">Login</button>
                    <p class="error" id="error"></p>
                    <p class="forgot-password-link"><a href="#" onclick="showForgotPassword()">Forgot Password?</a></p>

                </form>
                <div class="create-account">
                    <p>Don't have an account? <a href="Registration.php">Create account</a></p>
                </div>
            </div>
        </div>
        <div class="forgot-password-section" style="display: none;">
            <h2>Forgot Password</h2>
            <form method="POST" action="login.php">
                <input type="hidden" name="forgot_password" value="1">
                <label for="email">Enter your email address</label><br />
                <input type="email" id="email" name="email" placeholder="Enter your email" required><br /><br />
                <button type="submit">Send Reset Link</button>
                <p class="error" id="error"></p>
                <p class="success" id="success"></p>
            </form>
            <button onclick="showLoginForm()" class="btn-back">Back to Login</button>
            </div>

        
        <footer id="footer-section" class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-about">
                <h3>About Us</h3>
                <p>StraySaver is committed to rescuing, rehabilitating, and finding new homes for stray pets. Together, we make a difference in their lives.</p>
            </div>
            <div class="footer-contact">
                <h3>Contact Us</h3>
                <p>Email: <a href="mailto:info@straysaver@gmail.com">info@straysaver@gmail.com</a></p>
                <p>Phone: <a href="tel:+1234567890">+1 234 567 890</a></p>
                <p>Address: No.6/ Dickmens Road, Colombo 6</p>
            </div>
            <div class="footer-subscribe">
                <h3>Subscribe</h3>
                <p>Stay updated with our latest news and events. Sign up for our newsletter.</p>
                <form action="subscribe.php" method="post">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn-subscribe">Subscribe</button>
                </form>
            </div>
            <div class="footer-social">
                <h3>Follow Us</h3>
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 StraySaver. All rights reserved.</p>
        </div>
    </div>
</footer>

    </main>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const togglePassword = document.getElementById('toggle-password');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                togglePassword.innerText = 'Hide password';
            } else {
                passwordField.type = 'password';
                togglePassword.innerText = 'Show password';
            }
        }

        function showForgotPassword() {
        document.querySelector('.login-section').style.display = 'none';
        document.querySelector('.forgot-password-section').style.display = 'block';
        document.querySelector('.btn-back').style.display = 'block'; // Ensure the button is visible

    }

    function showLoginForm() {
            document.querySelector('.forgot-password-section').style.display = 'none';
            document.querySelector('.login-section').style.display = 'flex';
            document.querySelector('.btn-back').style.display = 'none'; // Hide the button



        }


    </script>
</body>
</html>
