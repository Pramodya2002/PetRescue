<?php
session_start();
require_once 'utils/connect.php'; // Ensure this file contains the connection to your database

// Initialize error and success messages
$error = array();
$success_message = '';



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $userRole = $_POST['userRole']; // Get the user role

    // Common variables
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Role-specific variables
    if ($userRole == 'dogLover') {
        $fullName = trim($_POST['fullName']);
        $clinicName = $shelterName = "";

        if (empty($fullName)) {
            $errors[] = "Full Name is required.";
        }
        
    } elseif ($userRole == 'vetClinic') {
        $clinicName = trim($_POST['clinicName']);
        $fullName = $shelterName = "";

        if (empty($clinicName)) {
            $errors[] = "Clinic Name is required.";
        }

    } elseif ($userRole == 'animalShelter') {
        $shelterName = trim($_POST['shelterName']);
        $fullName = $clinicName = "";

        if (empty($shelterName)) {
            $errors[] = "Shelter Name is required.";
        }
    }

    // Common validations
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid Email is required.";
    }
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    if (!preg_match("/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/", $password)) {
        $errors[] = "Password must be at least 8 characters long, include one uppercase letter, one lowercase letter, and one number.";
    }

    if (empty($errors)) {
        // Hash the password before saving to the database

    
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);


        if ($userRole == 'dogLover') {
            $stmt = $conn->prepare("INSERT INTO registration (full_name, email, username, password, user_role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fullName, $email, $username, $passwordHash, $userRole);
        } elseif ($userRole == 'vetClinic') {
            $stmt = $conn->prepare("INSERT INTO registration (clinic_name, email, username, password, user_role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $clinicName, $email, $username, $passwordHash, $userRole);
        } elseif ($userRole == 'animalShelter') {
            $stmt = $conn->prepare("INSERT INTO registration (shelter_name, email, username, password, user_role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $shelterName, $email, $username, $passwordHash, $userRole);
        }

            
        
        if ($stmt->execute()) {
            // Pass success message to JavaScript
            echo "<script type='text/javascript'>
                    window.onload = function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration successful!',
                            showConfirmButton: true
                        });
                    };
                  </script>";
        } else {
            // Pass error message to JavaScript
            $error_message = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            echo "<script type='text/javascript'>
                    window.onload = function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: '$error_message'
                        });
                    };
                  </script>";
        
        }
    }
}
        
        
    
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stray Saver Registration</title>
    <link rel="stylesheet" href="css/registration.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>



    
    <style>
          
          body {
            background: url('images/bg.jpg') no-repeat center center fixed; 
            background-size: cover; /* Ensure the background covers the whole page */
          }
          .hidden {
            display: none;
        }

        .hero {
            background: url('images/hero.jpg') no-repeat center center/cover; /* Background image with cover fit */
            color: #fff; /* Text color for readability against the background */
            padding: 80px 20px; /* Spacing around the text */
            text-align: center; /* Center-align text */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Shadow for depth */
            position: relative; /* Position for overlay effect */
            overflow: hidden; /* Hide any overflow from child elements */
            max-width: 1500px; /* Maximum width of the hero section */
            margin: 0 auto; /* Center-align the hero section horizontally */
        }


        .hero h1 {
            font-size: 48px; /* Large, bold heading */
            margin-bottom: 20px; /* Space below the heading */
            line-height: 1.2; /* Better line spacing for readability */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6); /* Subtle text shadow for contrast */
        }

        .hero p {
            font-size: 20px; /* Slightly smaller than the heading */
            max-width: 800px; /* Limit width for better readability */
            margin: 0 auto; /* Center-align the paragraph */
            line-height: 1.6; /* Better spacing between lines */
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6); /* Subtle text shadow for contrast */
        }

        .hero::before {
            content: ''; /* Empty content for pseudo-element */
            position: absolute; /* Absolute positioning */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3); /* Dark overlay for text readability */
            z-index: 1; /* Overlay should be above the background but below text */
        }

        .hero h1, .hero p {
            position: relative; /* Ensure text is above the overlay */
            z-index: 2;
        }

        
        @keyframes fadeInDown {
            from {
            opacity: 0;
            transform: translateY(-20px);
            }
            to {
            opacity: 1;
            transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
            opacity: 0;
            transform: translateY(20px);
            }
            to {
            opacity: 1;
            transform: translateY(0);
            }
        }
        
        /* Form Container Styling */
        section {
            margin: 20px auto;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .user-type-selection {
            margin: 20px auto;
            padding: 20px;
            max-width: 400px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .user-role-label {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
            display: block;
        }

        .custom-select-wrapper {
            position: relative;
            width: 100%;
        }

        .user-role-dropdown {
            width: 100%;
            padding: 10px 40px 10px 15px; /* Padding to account for the arrow */
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #fff;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
            appearance: none; /* Remove default dropdown arrow */
            cursor: pointer;
            transition: all 0.3s ease;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iOCIgdmlld0JveD0iMCAwIDE2IDgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTEgMUwxNCAxTDggNyIgZmlsbD0iIzAwN2JmZiIgc3Ryb2tlPSJub25lIi8+PC9zdmc+'); /* Custom arrow image */
            background-repeat: no-repeat;
            background-position: right 10px center; /* Position the arrow */
            background-size: 16px 8px; /* Size of the arrow */
        }

        .user-role-dropdown:hover {
            border-color: #007bff;
            box-shadow: inset 0 2px 4px rgba(0, 123, 255, 0.2);
        }



        /* General Form Styling */
            .form-section {
                max-width: 400px; /* Set max width for a smaller form */
                margin: 20px auto; /* Center the form */
                padding: 25px;
                background-color: #f9f9f9;
                border-radius: 12px;
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
                font-family: 'Arial', sans-serif;
            }

            .form-section h2 {
                font-size: 26px;
                margin-bottom: 15px;
                text-align: center;
                color: #333;
                font-weight: bold;
                letter-spacing: 1px;
            }

            .form-group {
                margin-bottom: 18px;
            }

            .form-group label {
                display: block;
                font-weight: bold;
                margin-bottom: 6px;
                font-size: 15px;
                color: #555;
                text-transform: uppercase;
            }

            .form-group input[type="text"],
            .form-group input[type="email"],
            .form-group input[type="password"],
            .form-group select {
                width: 100%;
                padding: 10px;
                font-size: 14px;
                border-radius: 8px;
                border: 1px solid #ccc;
                background-color: #fff;
                box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
                transition: border-color 0.3s ease, box-shadow 0.3s ease;
            }

            .form-group input[type="text"]:focus,
            .form-group input[type="email"]:focus,
            .form-group input[type="password"]:focus,
            .form-group select:focus {
                border-color: #3f51b5;
                outline: none;
                box-shadow: 0 0 8px rgba(63, 81, 181, 0.5);
            }

        /* Submit Button Styling */
            button[type="submit"] {
                width: 100%;
                padding: 12px;
                background-color: #ff9900;
                color: #fff;
                font-size: 16px;
                font-weight: bold;
                text-transform: uppercase;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
                margin-top: 20px;
                box-shadow: 0 4px 10px rgba(63, 81, 181, 0.3);
            }

            button[type="submit"]:hover {
                background-color: #cc7a00;
                box-shadow: 0 6px 14px rgba(48, 63, 159, 0.4);
                transform: translateY(-2px);
            }

            button[type="submit"]:active {
                background-color: #cc7a00;
                box-shadow: 0 2px 5px rgba(48, 63, 159, 0.2);
                transform: translateY(0);
            }

        /* Error and Success Messages */
        .error, .success-message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            font-size: 16px;
        }

        .error {
    margin-top: 20px;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    background-color: #f8d7da; /* Light red background */
    color: #721c24; /* Dark red text */
    border: 2px solid #f5c6cb; /* Slightly darker border */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Shadow to add depth */
}

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Adjust form responsiveness */
        @media screen and (max-width: 600px) {
            .form-section {
                padding: 20px;
            }

            .form-group input[type="text"],
            .form-group input[type="email"],
            .form-group input[type="password"],
            .form-group select {
                padding: 10px;
                font-size: 14px;
            }

            button[type="submit"] {
                padding: 10px;
                font-size: 15px;
            }
        }

        /* Social Icons */
            .footer-social a {
            color: #ecf0f1;
            font-size: 1.5rem;
            margin-right: 10px;
            transition: color 0.3s ease;
            }

            .footer-social a:hover {
            color: #dba134;
            }

            /* Footer Bottom */
            .footer-bottom {
            text-align: center;
            padding: 10px;
            background: #50402c;
            margin-top: 20px;
            border-top: 1px solid #5e3b34;
            }

            .footer-bottom p {
            margin: 0;
            }

            /* Responsive Styles */
            @media (max-width: 767px) {
            .footer-content {
                flex-direction: column;
                align-items: center;
            }
            }


            /* Responsive Styles */
            @media (max-width: 767px) {
            .footer-content {
                flex-direction: column;
                align-items: center;
            }

            .footer-card {
                margin-bottom: 20px;
            }
            }


            /* Responsive Styles */
            @media (max-width: 767px) {
            .footer-content {
                flex-direction: column;
                align-items: center;
            }

            .footer-content > div {
                margin-bottom: 20px;
            }
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
                    <li class="nav-item"><a href="index.php" class="nav-link signup-button">Back</a></li>
                    <li class="nav-item"><a href="login.php" class="nav-link login-button">Log In</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <section class="hero">
            <h1>Join Us in Saving Stray Dogs</h1>
            <p>Register to become a part of our community dedicated to rescuing and caring for stray dogs.</p>
        </section>
        <section class="user-type-selection">
            <label for="userRole" class="user-role-label">Select User Type:</label>
            <select id="userRole" class="user-role-dropdown" onchange="showRegistrationForm()">
                <option value="dogLover">Dog Lover</option>
                <option value="vetClinic">Vet Clinic</option>
                <option value="animalShelter">Animal Shelter/Foster</option>
            </select>
        </section>


        <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php elseif ($success_message): ?>
        <div class="success-message">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

        <!-- Dog Lover Registration Form -->
        <div id="dogLoverForm" class="form-section">
            <h2>Dog Lover Registration</h2>
            <form method="POST" action="registration.php" onsubmit="return validatePasswords()">
                <input type="hidden" name="userRole" value="dogLover">
                <div class="form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" placeholder="Enter full name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                    title="Password must be at least 8 characters long, include one uppercase letter, one lowercase letter, and one number.">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
                </div>
                <button type="submit">Register</button>
            </form>
        </div>

        

        <!-- Vet Clinic Registration Form -->
        <div id="vetClinicForm" class="form-section hidden">
            <h2>Vet Clinic Registration</h2>
            <form method="POST" action="registration.php" onsubmit="return validatePasswords()">
                <input type="hidden" name="userRole" value="vetClinic">
                <div class="form-group">
                    <label for="clinicName">Clinic Name</label>
                    <input type="text" id="clinicName" name="clinicName" placeholder="Enter clinic name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter clinic email" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                    title="Password must be at least 8 characters long, include one uppercase letter, one lowercase letter, and one number.">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
                </div>
                <button type="submit">Register</button>
            </form>
        </div>

        <!-- Animal Shelter Registration Form -->
        <div id="animalShelterForm" class="form-section hidden">
            <h2>Animal Shelter/Foster Registration</h2>
            <form method="POST" action="registration.php" onsubmit="return validatePasswords()">
                <input type="hidden" name="userRole" value="animalShelter">
                <div class="form-group">
                    <label for="shelterName">Shelter Name</label>
                    <input type="text" id="shelterName" name="shelterName" placeholder="Enter shelter name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter shelter email" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required
                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                    title="Password must be at least 8 characters long, include one uppercase letter, one lowercase letter, and one number.">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm password" required>
                </div>
                <button type="submit">Register</button>
            </form>
        </div>


         

    </main>


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
        
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
        function showRegistrationForm() {
            var role = document.getElementById("userRole").value;
            document.getElementById("dogLoverForm").classList.add("hidden");
            document.getElementById("vetClinicForm").classList.add("hidden");
            document.getElementById("animalShelterForm").classList.add("hidden");

            if (role === "dogLover") {
                document.getElementById("dogLoverForm").classList.remove("hidden");
            } else if (role === "vetClinic") {
                document.getElementById("vetClinicForm").classList.remove("hidden");
            } else if (role === "animalShelter") {
                document.getElementById("animalShelterForm").classList.remove("hidden");
            }
        }


        function validatePasswords() {
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("confirmPassword").value;
        
        if (password !== confirmPassword) {
            alert("Passwords do not match!");
            return false;
        }
        return true;
    }



        <?php if ($success_message): ?>
            Swal.fire({
                icon: 'success',
                title: '<?php echo addslashes($success_message); ?>',
                showConfirmButton: true
            });
        <?php elseif (!empty($errors)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo addslashes(implode(" ", $errors)); ?>'
            });
        <?php endif; ?><?php if ($success_message): ?>
            Swal.fire({
                icon: 'success',
                title: '<?php echo addslashes($success_message); ?>',
                showConfirmButton: true
            });
        <?php elseif (!empty($errors)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo addslashes(implode(" ", $errors)); ?>'
            });
        <?php endif; ?>
    </script>
</body>
</html>
