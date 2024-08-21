<?php
session_start();
require_once 'utils/connect.php';


// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = [
    'success' => false,
    'message' => 'An error occurred'
];

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['description'], $_POST['location'], $_POST['behaviour'])) {
        $description = $_POST['description'];
        $location = $_POST['location'];
        $behaviour = $_POST['behaviour'];

       


        // File upload handling
        $photos = [];
        if (!empty($_FILES['photos']['name'][0])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            foreach ($_FILES['photos']['name'] as $key => $name) {
                $target_file = $target_dir . basename($name);
                if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $target_file)) {
                    $photos[] = $target_file;
                } else {
                    $response['message'] = 'Failed to upload file: ' . htmlspecialchars($name);
                    echo json_encode($response);
                    exit;
                }
            }
        }

        $photos_serialized = serialize($photos);

        // Insert data into database
        $stmt = $conn->prepare("INSERT INTO reportstray (description, location, photos, behaviour) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            error_log('Prepare failed: ' . htmlspecialchars($conn->error));
            $response['message'] = 'Prepare failed: ' . htmlspecialchars($conn->error);
            echo json_encode($response);
            exit;
        }
        
        $stmt->bind_param("ssss", $description, $location, $photos_serialized, $behaviour);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Details successfully saved!';
        } else {
            $response['message'] = 'Failed to save details: ' . htmlspecialchars($stmt->error);
        }
        
        $stmt->close();
    } elseif (isset($_POST['report_id'], $_POST['status'])) {
        $report_id = $_POST['report_id'];
        $status = $_POST['status'];

        // Check if report_id and status are valid
        if ($report_id > 0 && !empty($status)) {

        // Update status in the database
        $stmt = $conn->prepare("UPDATE reportstray SET status = ? WHERE id = ?");
        if ($stmt === false) {
            $response['message'] = 'Prepare failed: ' . htmlspecialchars($conn->error);
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("si", $status, $report_id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Status successfully updated!';
        } else {
            $response['message'] = 'Failed to update status: ' .  htmlspecialchars($stmt->error);
        }
        $stmt->close();
    } else {
        $response['message'] = 'Invalid report_id or status';
    }
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
    <title>Report a Stray Dog</title>
    <link rel="stylesheet" href="css/reportStray.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />



    <style>
       

        /* Header */
            .header {
            background: linear-gradient(135deg, #9e571d, #6f4501);
            padding: 20px 0; /* Increase padding for a larger header */
            color: white;
            border-bottom: 4px solid #5a2f02;
            display: flex; /* Use flexbox for alignment */
            align-items: center; /* Vertically center content */
            height: 180px; /* Increased height */
            justify-content: flex-start; /* Align items to the left */
            }

            .header-container {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 1170px;
            margin: 0 auto;
            padding: 0 20px;
            }

            .logo {
            display: flex;
            align-items: center; /* Vertically align items */
            margin-right: auto; /* Push logo and title to the left */
            }

            .logo img {
            width: 120px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            height: 100%;
            }

            .title {
            font-family: 'Trebuchet MS', sans-serif;
            font-size: 80px; /* Increased font size */
            color: #f7d1aa;
            margin-left: 15px; /* Margin for spacing */
            font-weight: 700;
            position: relative;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.5);
            letter-spacing: 2px;
            }

            .nav {
            display: flex;
            align-items: center;
            }

            .nav-list {
            display: flex;
            list-style: none;
            }

            .nav-item {
            margin-left: 15px; /* Spacing between buttons */
            }

            .nav-link {
            text-decoration: none;
            color: white;
            font-size: 16px; /* Font size for buttons */
            padding: 12px 20px; /* Padding for larger buttons */
            transition: background-color 0.3s, color 0.3s;
            border-radius: 5px;
            }

            .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            }
            .home-button {
            background-color: #af874c;
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s, color 0.3s, transform 0.3s;
            border-radius: 25px;
            
        }
        
        .home-button:hover {
            background-color: #a05a45;
            transform: scale(1.05);
        }

                    /* Title Section Styling */
                    .report-title {
                background: linear-gradient(135deg, rgba(161, 87, 13, 0.9), rgba(229, 159, 30, 0.9)); /* Add a texture or pattern */
                padding: 40px;
                text-align: center;
                border-radius: 20px;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
                margin-bottom: 40px;
                position: relative;
                overflow: hidden;
                z-index: 1;
            }

            .report-title::before {
                content: '';
                position: absolute;
                top: 0;
                left: -50%;
                width: 200%;
                height: 100%;
                background: rgba(255, 255, 255, 0.1);
                transform: skewX(-20deg);
                transition: all 0.7s ease;
                z-index: 2;
            }

            .report-title:hover::before {
                left: 100%;
            }

            .report-title h2 {
                font-size: 3rem;
                color: white;
                text-transform: uppercase;
                letter-spacing: 4px;
                margin: 0;
                position: relative;
                z-index: 3;
                font-family: 'Oswald', sans-serif; /* Use a unique font */
                text-shadow: 2px 4px 6px rgba(0, 0, 0, 0.5);
                animation: fadeInTitle 1s ease-in-out;
            }

            /* Keyframes for title animation */
            @keyframes fadeInTitle {
                0% {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .report-title {
                    padding: 30px;
                }

                .report-title h2 {
                    font-size: 2.5rem;
                    letter-spacing: 2px;
                }
            }


            /* Content Section Styling */
            .report-section {
                background: linear-gradient(135deg, #fdf2e3, #f9c190);
                border-radius: 15px;
                padding: 40px;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
                max-width: 700px;
                margin: 0 auto;
                font-family: 'Arial', sans-serif;
            }

            .report-content p {
                font-size: 1.1rem;
                color: #333;
                line-height: 1.7;
                margin-bottom: 20px;
            }

            .report-content ul {
                list-style: none;
                padding: 0;
                margin: 20px 0;
                text-align: left;
            }

            .report-guidelines li {
            font-size: 1rem;
            color: #c07615;
            padding-left: 30px; /* Adjust spacing to accommodate the icon */
            margin-bottom: 12px;
            position: relative;
        }

        .report-guidelines li::before {
           
            font-weight: 900;
            color: #e5b01e;
            margin-right: 10px; 
            position: absolute;
            left: 0;
            top: 50%;
        }
        .icon-location::before {
            content: "\f3c5"; /* FontAwesome map marker icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: #e5b01e;
        }

        .icon-time::before {
            content: "\f073"; /* FontAwesome calendar icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: #e5b01e;
        }

        .icon-description::before {
            content: "\f1b0"; /* FontAwesome paw icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: #e5b01e;
        }

        .icon-features::before {
            content: "\f02c"; /* FontAwesome tag icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: #e5b01e;
        }

        .icon-photos::before {
            content: "\f030"; /* FontAwesome camera icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: #e5b01e;
        }





        .report-button {
        background-color: #8B4513; /* SaddleBrown */
        color: #ffffff;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.1em;
        transition: background-color 0.3s, transform 0.3s;
        font-family: 'Roboto', sans-serif;
        margin: 20px 0;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .report-button:hover {
        background-color: #6F4F28; /* Darker shade of brown */
        transform: scale(1.02);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }



            /* Responsive Design */
            @media (max-width: 768px) {
                .report-title h2 {
                    font-size: 2rem;
                }

                .report-section {
                    padding: 30px;
                }

                .report-content p,
                .report-guidelines li {
                    font-size: 1rem;
                }

                .report-button {
                    padding: 10px 20px;
                    font-size: 1rem;
                }
            }


            



        .form-container, .reports-section {
            width: 80%;
            max-width: 800px;
            margin-bottom: 40px;
        }

        

        @media (max-width: 768px) {
            .form-container, .reports-section {
                width: 100%;
            }

            .report {
                flex-direction: column;
                align-items: flex-start;
            }

            .report img {
                width: 100%;
                height: auto;
            }
        }

        .footer {
            background: linear-gradient(135deg, #6f4501, #9e571d);
            color: #ecf0f1;
            padding: 40px 0;
            font-family: 'Arial', sans-serif;
        }
        
        /* Footer Content */
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        
        /* Footer Sections */
        .footer-about, .footer-contact, .footer-subscribe, .footer-social {
            flex: 1;
            margin: 10px;
            max-width: 25%;
        }
        
        .footer-about h3, .footer-contact h3, .footer-subscribe h3, .footer-social h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 10px;
            font-weight: bold;
        }
        
        .footer-about p, .footer-contact p, .footer-subscribe p {
            font-size: 1rem;
            line-height: 1.5;
        }
        
        /* Footer Subscribe Form */
        .footer-subscribe form {
            display: flex;
            flex-direction: column;
        }
        
        .footer-subscribe input[type=email] {
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 1rem;
        }
        
        .btn-subscribe {
            background: #db8534;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease;
        }
        
        .btn-subscribe:hover {
            background: #b98729;
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



        /* Form Section Styles */
        .form-section {
            width: 100%;
            max-width: 700px;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid #dcdcdc;
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .form-section:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* Form Image Styles */
        .form-image {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Form Title Styles */
        .form-section h2 {
            margin-bottom: 20px;
            font-size: 2em;
            color: #ff8000;
            font-family: 'Roboto', sans-serif;
            text-align: center;
            background: linear-gradient(90deg, #ff9d00, #ffae00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            padding-bottom: 10px;
        }

        .form-section h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            width: 60%;
            height: 3px;
            background-color: #ff8000;
            transform: translateX(-50%);
        }

        /* Form Description Styles */
        .form-section p {
            margin-bottom: 20px;
            font-size: 1.1em;
            color: #666;
            font-family: 'Arial', sans-serif;
            text-align: center;
        }

        /* Input and Label Styles */
        .form-section label {
            display: block;
            margin: 15px 0 5px;
            font-weight: 600;
            color: #444;
            font-family: 'Arial', sans-serif;
        }

        .form-section input[type="text"],
        .form-section input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            margin-bottom: 20px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-section input[type="text"]:focus,
        .form-section input[type="file"]:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
            outline: none;
        }

        /* Radio Group Styles */
        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .radio-group label {
            font-size: 1em;
            color: #444;
            display: flex;
            align-items: center;
        }

        /* Button Styles */
        .form-section button {
            background-color: #ffaa00;
            color: #010000;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s, transform 0.3s;
            font-family: 'Roboto', sans-serif;
        }

        .form-section button:hover {
            background-color: #b35c00;
            transform: scale(1.02);
        }



.description-note {
        font-size: 0.9rem;
        color: #555;
        margin-top: 5px;
        font-family: 'Arial', sans-serif;
    }

    #map {
            height: 400px;
            width: 100%;
        }


        .notification {
    display: none; /* Hidden by default, will be shown via JavaScript */
    padding: 30px; /* Increased padding for a larger message area */
    border-radius: 10px; /* More rounded corners for a prominent look */
    font-size: 20px; /* Larger font size for better visibility */
    position: fixed;
    top: 50%; /* Center vertically */
    left: 50%; /* Center horizontally */
    transform: translate(-50%, -50%); /* Adjust for perfect centering */
    z-index: 1000; /* Ensure it stays on top of other content */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2); /* Larger shadow for emphasis */
    width: 400px; /* Fixed width to ensure consistency */
    max-width: 90%; /* Responsive on smaller screens */
    text-align: center; /* Centers the text inside */
    background-color: #fff; /* White background for better contrast */
    border: 1px solid #ddd; /* Light border to delineate the notification */
}

.notification.success {
    background-color: #d4edda; /* Light green background */
    color: #155724; /* Dark green text color */
    border: 1px solid #c3e6cb; /* Slightly darker border */
}

.notification.error {
    background-color: #f8d7da; /* Light red background */
    color: #721c24; /* Dark red text color */
    border: 1px solid #f5c6cb; /* Slightly darker border */
}

/* General styling for the emergency reports container */
.reports-section {
    font-family: 'Roboto', sans-serif;
    background-color: #f0f2f5;
    padding: 25px;
    border-radius: 10px;
    max-width: 900px;
    margin: 0 auto;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

/* Header styling */
.reports-section h2 {
    text-align: center;
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 30px;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* Styling for each individual report */
.report {
    background-color: #ffffff;
    margin-bottom: 30px;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.report:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

/* Styling for the report image */
.report img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Styling for the report details */
.report-details {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Styling for the report description */
.report-details h3 {
    font-size: 22px;
    margin: 0;
    color: #2c3e50;
    font-weight: bold;
    border-bottom: 2px solid #e74c3c;
    padding-bottom: 10px;
}

/* Styling for the report location */
.report-details p {
    font-size: 22px; /* Larger font size for better visibility */
    color: #000000; /* Dark color for high contrast */
    margin: 10px 0 20px;
    font-weight: 600; /* Semi-bold to emphasize the text */
    letter-spacing: 0.5px; /* Slightly increased letter spacing for readability */
    text-transform: uppercase; /* Uppercase to make the text stand out more */
    line-height: 1.4; /* Improved line height for readability */
}


/* Styling for the status update section */
.status-update {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background-color: #ecf0f1;
    border-radius: 8px;
    border-left: 4px solid #3498db;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Styling for the status select dropdown */
.status-select {
    flex: 1;
    padding: 10px;
    border: 2px solid #3498db;
    border-radius: 4px;
    background-color: #ffffff;
    font-size: 16px;
    color: #2c3e50;
    outline: none;
    transition: border-color 0.3s ease;
    margin-right: 10px;
}

.status-select:focus {
    border-color: #2980b9;
}

/* Styling for the save button */
.save-status-button {
    padding: 10px 20px;
    background-color: #e74c3c;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.save-status-button:hover {
    background-color: #c0392b;
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
                    <li class="nav-item"><a href="Dashboard.php" class="nav-link home-button">Home</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="notification success">
        <p><?php echo $_SESSION['success']; ?></p>
    </div>
    <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="notification error">
            <p><?php echo $_SESSION['error']; ?></p>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

            <div class="report-title">
            <h2>Report a Stray</h2>
        </div>
        <div class="report-section">
            <div class="report-content">
                <p>Help us keep your community safe by reporting stray dogs. Please provide as much detail as possible.</p>
                <ul class="report-guidelines">
                    <li><i class="fas fa-map-marker-alt"></i> Location of the sighting</li>
                    <li><i class="fas fa-calendar-alt"></i> Time and date of the sighting</li>
                    <li><i class="fas fa-paw"></i> Description of the dog (color, size, breed, etc.)</li>
                    <li><i class="fas fa-dog"></i> Behavior (friendly or aggressive)</li>
                    <li><i class="fas fa-camera"></i> Photos or videos if available</li>
                </ul>
                <p>Your report helps us take swift action and ensure the safety of both the dog and the community.</p>
                <button class="report-button" onclick="scrollToFormTitle()">Report Now</button>
            </div>
        </div>
        <br />
        <br />


        <div class="container">
            <div class="form-container">
            
                <div class="form-section">
                    

                    <div class="form-image">
                        <img src="images/straydog.jpeg" alt="Stray Dog Image">
                    </div>

                    <!-- Form for reporting a stray dog -->
                    <form id="reportForm" action="ReportStray.php" method="POST" enctype="multipart/form-data">
                        
                        <h2 id="formTitle">Report Form</h2>
                        <p>Please provide detailed information about the stray dog.</p>
                        <label for="description">Dog's Description</label>
                        <input type="text" id="description" name="description" required>
                        <p class="description-note">
                            <strong>Tip:</strong> Include details like size, color, breed (if known), visible injuries, and behavior (e.g., limping, scared).
                        </p>

                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" required>
                        <button type="button" onclick="getLocation()">Use My Current Location</button>

                        <div id="map"></div>

                       

                        <label for="photos">Photos</label>
                        <input type="file" id="photos" name="photos[]" multiple accept="image/*">

                        <label>Behaviour</label>
                        <div class="radio-group">
                            <label><input type="radio" name="behaviour" value="Aggressive" required> Aggressive</label>
                            <label><input type="radio" name="behaviour" value="Friendly" required> Friendly</label>
                        </div>

                        <button type="submit">Submit Report</button>
                    </form>
                </div>
            </div>
            <div class="reports-section">
                <h2>Stray Dog Reports</h2>
                <?php
                // Fetch reports from the database
                require_once 'utils/connect.php';

                $sql = "SELECT id, description, location, photos, behaviour, status FROM reportstray";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $photos = unserialize($row['photos']);
                        echo '<div class="report">';
                        if (!empty($photos)) {
                            echo '<img src="' . htmlspecialchars($photos[0]) . '" alt="Dog Photo">';
                        }
                        echo '<div class="report-details">';
                        echo '<h3>Description: ' . htmlspecialchars($row['description']) . '</h3>';
                        echo '<p>Location: ' . htmlspecialchars($row['location']) . '</p>';
                        echo '<p>Behaviour: ' . htmlspecialchars($row['behaviour']) . '</p>';
                        echo '</div>';
                        echo '<div class="status-container">';
                        echo '<div class="status-dropdown">';
                        echo '<select class="status-select" data-report-id="' . htmlspecialchars($row['id']) . '">';
                        echo '<option value="status" ' . ($row['status'] == 'status' ? 'selected' : '') . '>Status</option>';
                        echo '<option value="rescued" ' . ($row['status'] == 'rescued' ? 'selected' : '') . '>Rescued</option>';
                        echo '<option value="rescue in progress" ' . ($row['status'] == 'rescue in progress' ? 'selected' : '') . '>Rescue in Progress</option>';
                        echo '</select>';
                        echo '</div>';
                        echo '<button class="save-status-button" data-report-id="' . htmlspecialchars($row['id']) . '">Save</button>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No reports available.</p>';
                }

                $conn->close();
                ?>
            </div>
           <div class="shelters">
                <h2>Nearest Shelters and Foster Homes</h2>
                <button class="search-button" onclick="searchShelters()">Search for Shelters</button>
                <div class="shelter">
                    <img src="images/map.jpg" alt="Dummy Map">
                      <p>Click the button above to search for nearest shelters and foster homes.</p>                      
                </div>
            </div>
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
        <div class="footer-bottom">
            <p>&copy; 2024 StraySaver. All rights reserved.</p>
        </div>
    </div>
</footer>


<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
 
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handling
    document.querySelector('#reportForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('ReportStray.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            displayMessage(data.message, data.success ? 'success' : 'error');
        })
        .catch(error => {
            console.error('Error:', error);
            displayMessage('Failed to save details. Please try again.', 'error');
        });
    });
 
    // Status update handling
    document.querySelectorAll('.save-status-button').forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.getAttribute('data-report-id');
            const statusSelect = this.previousElementSibling.querySelector('.status-select');
            const status = statusSelect.value;

            if (status && reportId) {
                updateStatus(reportId, status);
            }
        });
    });



    function updateStatus(reportId, status) {
        fetch('ReportStray.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `report_id=${encodeURIComponent(reportId)}&status=${encodeURIComponent(status)}`
        })
        .then(response => response.json())
        .then(data => {
            displayMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                const statusSelect = document.querySelector(`select[data-report-id="${reportId}"]`);
                if (statusSelect) {
                    statusSelect.value = status;  // Update the UI to reflect the new status
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayMessage('Failed to update status. Please try again.', 'error');
    });

    }
});

function displayMessage(message, type) {
        const notification = document.getElementById('notification');
        if (!notification) {
            const newNotification = document.createElement('div');
            newNotification.id = 'notification';
            newNotification.className = `notification ${type}`;
            document.body.appendChild(newNotification);
        } else {
            notification.className = `notification ${type}`;
        }
        const notificationElement = document.getElementById('notification');
        notificationElement.textContent = message;
        notificationElement.style.display = 'block';

        setTimeout(() => {
            notificationElement.style.display = 'none';
        }, 3000);
    }

function searchShelters() {
    alert('Searching for shelters...');
}

    



        function scrollToFormTitle() {
    document.getElementById('formTitle').scrollIntoView({ behavior: 'smooth' });
}



var map;
    var marker;

    function initMap(lat = 6.9271, lon = 79.8612) {
        map = L.map('map').setView([lat, lon], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        marker = L.marker([lat, lon], {draggable: true}).addTo(map);
        marker.on('moveend', function(e) {
            var latLng = e.target.getLatLng();
            reverseGeocode(latLng.lat, latLng.lng);
        });
    }

    function reverseGeocode(lat, lon) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`)
            .then(response => response.json())
            .then(data => {
                var locationName = data.display_name;
                document.getElementById("location").value = locationName;
            })
            .catch(error => console.error('Error:', error));
    }

    function getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError);
        } else {
            alert("Geolocation is not supported by this browser.");
        }
    }

    function showPosition(position) {
        var lat = position.coords.latitude;
        var lon = position.coords.longitude;
        updateLocationFields(lat, lon);
        map.setView([lat, lon], 15);
        marker.setLatLng([lat, lon]);
    }

    function updateLocationFields(lat, lon) {
        reverseGeocode(lat, lon);
    }

    function showError(error) {
        switch(error.code) {
            case error.PERMISSION_DENIED:
                alert("User denied the request for Geolocation.");
                break;
            case error.POSITION_UNAVAILABLE:
                alert("Location information is unavailable.");
                break;
            case error.TIMEOUT:
                alert("The request to get user location timed out.");
                break;
            case error.UNKNOWN_ERROR:
                alert("An unknown error occurred.");
                break;
        }
    }

    window.onload = function() {
        initMap();
    };
    </script>
</body>
</html>
