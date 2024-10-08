<?php
require_once 'utils/connect.php';  // Ensure this path is correct

$response = [
    'success' => false,
    'message' => 'Failed to save details'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['name'], $_POST['age'], $_POST['location'], $_POST['experience'])) {
        $name = $_POST['name'];
        $age = $_POST['age'];
        $location = $_POST['location'];
        $experience = $_POST['experience'];

        $stmt = $conn->prepare("INSERT INTO volunteer (name, age, location, experience) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $name, $age, $location, $experience);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Details successfully saved!';
        } else {
            $response['message'] = 'Failed to save details: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['message'] = 'Required fields missing';
    }

    echo json_encode($response);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Registration</title>
    <link rel="stylesheet" href="css/volunteer.css">
    <style>
        .hero-content h2 {
            font-size: 52px;
        }
        #footer {
            background-color: #333;
            color: #fcf8fb;
            padding: 40px 20px;
            display: flex;
            flex-wrap: wrap;
        }

        .footer-section {
            flex: 1 1 300px;
            margin-bottom: 30px;
        }

        .section-content {
            padding: 0 20px;
        }

        .section-content h2 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #f393ec;
        }

        .section-content p,
        .section-content ul {
            color: #f393ec;
            line-height: 1.6;
        }

        .section-content ul {
            padding: 0;
            list-style: none;
        }

        .section-content ul li {
            margin-bottom: 10px;
        }

        .section-content ul li a {
            color: #f393ec;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .section-content ul li a:hover {
            color: #f393ec;
        }

        .social-icons {
            display: flex;
            align-items: center; /* Center align items vertically */
        }

        .social-icons a {
            margin-right: 15px;
            transition: transform 0.3s ease; /* Smooth transition for hover effect */
        }

        .social-icons a:last-child {
            margin-right: 0; /* Remove margin-right from the last child to prevent extra space */
        }

        .social-icons a img {
            width: 30px; /* Set a specific width for the icons */
            height: 30px; /* Set a specific height for the icons */
            border-radius: 50%; /* Make the icons circular */
            transition: opacity 0.3s ease; /* Smooth transition for opacity */
        }

        .social-icons a:hover {
            transform: translateY(-3px); /* Move the icon up slightly on hover */
        }

        .social-icons a:hover img {
            opacity: 0.7; /* Reduce opacity of the icon on hover */
        }


        .footer-bottom {
            text-align: center;
            padding: 15px 0;
            background-color: #a93ca9;
            color: #e9a2db;
            font-size: 14px;
            width: 100%;
        }

             

    </style>

</head>
<body>
    <header>
        <div class="logo">
            <img src="images/logo2.jpg" alt="Logo">
            <h1>Volunteer</h1>
        </div>
        <nav>
            <a href="Dashboard.php">Home</a>
            <a href="#">About</a>
            <a href="#">Volunteer</a>
            <a href="lostfound.php">Reunite Paws</a>
            <input type="search" placeholder="Search in site">
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-content">
                <h2>Become a Volunteer</h2>
                <p>Help save stray dogs by becoming a volunteer!</p>
            </div>
            <div class="hero-image">
                <img src="images/Volunteer.jpg" alt="Volunteer">
            </div>
        </section>

        <section class="registration">
            <h2>Volunteer Registration</h2>
            <p>Fill in the form below to join our volunteer team.</p>
            <div id="notification" class="notification"></div>
            <form id="volunteerForm" action="volunteer.php" method="POST">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="text" id="age" name="age" placeholder="Enter your age" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="Enter your location" required>
                </div>
                <div class="form-group">
                    <label for="experience">Experience</label>
                    <textarea id="experience" name="experience" placeholder="Describe any relevant experience" required></textarea>
                </div>
                <button type="submit">Submit</button>
            </form>
        </section>

        <section class="banner">
            <img src="images/BannerV.jpg" alt="Banner Image">
        </section>
    </main>

    <div id="footer">
        <div class="footer-section">
            <div class="section-content about">
                <h2>About Us</h2>
                <p>StraySaver is dedicated to helping stray dogs find loving homes and providing support to pet owners in need. Join us in making a difference.</p>
            </div>
        </div>
        <div class="footer-section">
            <div class="section-content contact">
                <h2>Contact Us</h2>
                <ul>
                <li><a href="mailto:stray@gmail.com">stray@gmail.com</a></li>
                <li>074-5694236</li>
                    <li>Dickmens rd, Colombo 3, Sri Lanka</li>
                </ul>
            </div>
        </div>
        <div class="footer-section">
            <div class="section-content social">
                <h2>Follow Us</h2>
                <div class="social-icons">
                    <a href="#"><img src="images/facebook.jpg" alt="Facebook"></a>
                    <a href="#"><img src="images/twitter.jpg" alt="Twitter"></a>
                    <a href="#"><img src="images/instagram.jpg" alt="Instagram"></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2024 StraySaver. All Rights Reserved.
        </div>
   </div>

    <script>
        document.getElementById('volunteerForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const form = event.target;
            const formData = new FormData(form);
            const notification = document.getElementById('notification');

            // Send form data via fetch
            fetch('volunteer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessage(data.message, 'success');
                    form.reset(); // Clear the form after successful submission
                } else {
                    displayMessage(data.message || 'Failed to save details. Please try again.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                displayMessage('Failed to save details. Please try again.', 'error');
            });
        });

        function displayMessage(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';

            // Remove the message after 5 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
