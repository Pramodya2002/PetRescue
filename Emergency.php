<?php
session_start();
require_once 'utils/connect.php';  // Ensure this path is correct

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = [
    'success' => false,
    'message' => 'Failed to save details'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['description'], $_POST['location'], $_POST['priority'])) {
        $description = trim($_POST['description']);
        $location = trim($_POST['location']);
        $priority = trim($_POST['priority']);

        // File upload handling
        $photos = [];
        if (!empty($_FILES['file']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES['file']['name']);
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                $photos = [$target_file];
            } else {
                $response['message'] = 'Failed to upload file';
                echo json_encode($response);
                exit;
            }
        }

        $photos_serialized = serialize($photos);

        // Insert data into database
        $stmt = $conn->prepare("INSERT INTO emergencyreport (description, location, photos, priority) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            $response['message'] = 'Prepare failed: ' . htmlspecialchars($conn->error);
            echo json_encode($response);
            exit;
        }

        $stmt->bind_param("ssss", $description, $location, $photos_serialized, $priority);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Details successfully saved!';
        } else {
            $response['message'] = 'Failed to save details: ' . htmlspecialchars($stmt->error);
        }

        $stmt->close();
    } elseif (isset($_POST['report_id'], $_POST['status'])) {
        $report_id = intval($_POST['report_id']);
        $status = trim($_POST['status']);

         // Check if report_id and status are valid
         if ($report_id > 0 && !empty($status)) {

        // Update status in the database
        $stmt = $conn->prepare("UPDATE emergencyreport SET status = ? WHERE id = ?");
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
            $response['message'] = 'Failed to update status: ' . htmlspecialchars($stmt->error);
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
    <title>Emergency Rescue Form</title>
    <link rel="stylesheet" href="css/emergency.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">



    <style>

        body{
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        /* Header */
            .header {
                background: linear-gradient(135deg, #9e571d, #6f4501);
                padding: 20px 0; /* Increase padding for a larger header */
                color: white;
                border-bottom: 4px solid #5a2f02;
                display: flex; /* Use flexbox for alignment */
                height: 150px; /* Increased height */
                width: 100%;
                
            }
            
            .header-container {
                display: flex;
                align-items: center;
                width: 100%;
                max-width: 2000px;
                margin: 0 auto;
                padding: 0 20px;
                justify-content: space-between; /* Align logo/title to the left, buttons to the right */
            
            }
            
            .logo {
                display: flex;
                align-items:center; /* Vertically align items */
                margin-right: 100px; /* Push logo and title to the left */
                height: 100%;
            }
            
            .logo img {
                width: 120px;
                border-radius: 50%;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            }
            
            .title {
                font-family: 'Trebuchet MS', sans-serif;
                font-size: 80px; /* Increased font size */
                color: #f7d1aa;
                font-weight: 700;
                position: relative;
                text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.5);
                letter-spacing: 2px;
                margin-right: 100px;
            }
            
            .nav {
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }
            
            .nav-list {
                display: flex;
                align-items: center;
                list-style: none;
                padding: 0;
                margin: 0;
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
                font-weight: 700;
                text-transform: uppercase;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
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

            .emergency-rescue-container {
                width: 100%;
                max-width: 700px;
                margin: 30px auto;
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 15px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                }

                .tabs {
                display: flex;
                background-color: #007bff;
                border-bottom: 1px solid #ddd;
                }

                .tab-button {
                flex: 1;
                padding: 15px;
                cursor: pointer;
                text-align: center;
                background-color: #007bff;
                color: #fff;
                border: none;
                outline: none;
                transition: background-color 0.3s ease, color 0.3s ease;
                font-weight: bold;
                }

                .tab-button.active {
                background-color: #0056b3;
                color: #fff;
                }

                .tab-button:hover {
                background-color: #0056b3;
                }

                .content {
                padding: 30px 20px;
                background-color: #fff;
                }

                .content-section {
                display: none;
                }

                .content-section.active {
                display: block;
                }

                form {
                display: flex;
                flex-direction: column;
                }

                form h2 {
                margin-bottom: 20px;
                color: #007bff;
                font-size: 24px;
                text-align: center;
                border-bottom: 2px solid #007bff;
                padding-bottom: 10px;
                }

                form label {
                margin-top: 15px;
                font-weight: bold;
                color: #333;
                }

                form input,
                form textarea {
                padding: 12px 15px;
                margin-top: 8px;
                border-radius: 10px;
                border: 1px solid #ddd;
                background-color: #f5f5f5;
                font-size: 16px;
                }

                form input:focus,
                form textarea:focus {
                border-color: #007bff;
                outline: none;
                background-color: #fff;
                }

                form button {
                margin-top: 25px;
                padding: 12px 20px;
                background-color: #28a745;
                color: white;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                font-size: 16px;
                transition: background-color 0.3s ease;
                }

                form button:hover {
                background-color: #218838;
                }

                .vet-clinic {
                margin-bottom: 25px;
                padding: 15px;
                background-color: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 10px;
                }

                .contact-button {
                display: inline-block;
                margin-top: 10px;
                padding: 10px 15px;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s ease;
                }

                .contact-button:hover {
                background-color: #0056b3;
                }


                .page-header {
                    text-align: center;
                    background: linear-gradient(135deg, #ff6f61, #d32f2f); /* Gradient background with urgent red tones */
                    color: white;
                    padding: 40px 20px; /* Increased padding for a more substantial header */
                    border-radius: 10px;
                    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3); /* Added shadow for depth */
                    position: relative;
                    overflow: hidden;
                }

                .page-header h1 {
                    margin: 0;
                    font-size: 48px; /* Larger, bolder font for the title */
                    font-weight: 700;
                    text-transform: uppercase; /* Capitalized letters for emphasis */
                    letter-spacing: 3px; /* Increased spacing between letters */
                    z-index: 2;
                    position: relative;
                }

                .page-header p {
                    margin-top: 15px;
                    font-size: 22px; /* Slightly larger and more readable subtitle */
                    font-weight: 300;
                    font-style: italic;
                    opacity: 0.9;
                    z-index: 2;
                    position: relative;
                }

                .page-header::before {
                    content: '';
                    position: absolute;
                    top: -50%;
                    left: -50%;
                    width: 200%;
                    height: 200%;
                    background: rgba(255, 255, 255, 0.1);
                    transform: rotate(45deg);
                    z-index: 1;
                }

                .page-header::after {
                    content: '\f0e7'; /* FontAwesome lightning bolt icon */
                    font-family: 'Font Awesome 5 Free';
                    font-weight: 900;
                    color: rgba(255, 255, 255, 0.05);
                    font-size: 150px;
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 0;
                    opacity: 0.3;
                }

                .page-header h1, .page-header p {
                    z-index: 2;
                    position: relative;
                }

                .location-wrapper {
                display: flex;
                align-items: center;
                gap: 10px;
                }

                
#location {
    flex: 1; /* Takes up available space */
    padding: 8px; /* Adds padding inside the text input */
    font-size: 16px; /* Matches the font size of the button */
}

#mapBtn {
    padding: 8px 16px; /* Adds padding inside the button */
    font-size: 16px; /* Matches the font size of the text input */
    cursor: pointer; /* Changes the cursor to a pointer on hover */
}

/* Optional: Add some basic styling for better visuals */
#mapBtn {
    background-color: #007bff; /* Blue background color */
    color: white; /* White text color */
    border: none; /* Removes default border */
    border-radius: 4px; /* Rounded corners */
    margin-bottom: 40px; /* Adds space below the button */
}

#mapBtn:hover {
    background-color: #0056b3; /* Darker blue on hover */
}
                .map-container {
                height: 400px;
                width: 100%;
                margin-top: 10px;
                border: 1px solid #ddd;
                border-radius: 5px;
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
.emergency-reports {
    font-family: 'Roboto', sans-serif;
    background-color: #f0f2f5;
    padding: 25px;
    border-radius: 10px;
    max-width: 900px;
    margin: 0 auto;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

/* Header styling */
.emergency-reports h2 {
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

/* Styling for no reports message */
.emergency-reports p {
    text-align: center;
    color: #0e1113;
    font-size: 18px;
    font-style: italic;
}

/* Styling for the priority level selection */
.priority-wrapper {
    margin: 15px 0;
}

.priority-wrapper select {
    padding: 10px 15px;
    border: 2px solid #ccc;
    border-radius: 8px;
    background-color: #ffffff;
    font-size: 16px;
    color: #333;
    outline: none;
    cursor: pointer;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    width: 100%; /* Full width for better alignment */
}

/* Styling for the dropdown options */
.priority-wrapper option[value="low"] {
    background-color: #d4edda;
    color: #155724;
}

.priority-wrapper option[value="medium"] {
    background-color: #fff3cd;
    color: #856404;
}

.priority-wrapper option[value="high"] {
    background-color: #ffe5e5;
    color: #721c24;
}

.priority-wrapper option[value="urgent"] {
    background-color: #f8d7da;
    color: #721c24;
}

.priority-wrapper select:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Styling for the dropdown options */
.priority-wrapper option {
    padding: 10px;
}

/* Styling for the report priority */
.report-details .priority {
    font-size: 18px;
    color: #e67e22; /* A bright color for visibility */
    margin: 10px 0;
    font-weight: bold;
    text-transform: uppercase;
}

.priority.urgent, .priority.high {
    color: red;
    font-weight: bold;
}

.priority.medium {
    color: orange; /* Choose another color for medium priority */
    font-weight: bold;
}

.priority.low {
    color: green; /* Choose another color for low priority */
    font-weight: bold;
}

/* General section styling */
.advice-section {
    background-color: #f4f9f9;
    padding: 30px;
    margin: 0 auto;
    width: 85%;
    max-width: 700px;
    border-radius: 12px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
}

/* Heading styling */
.advice-section h2 {
    font-size: 2.2rem;
    color: #333;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
    text-transform: uppercase;
}

/* Content and items styling */
.advice-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.advice-item {
    display: flex;
    align-items: flex-start;
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s, box-shadow 0.3s;
    border: 1px solid #e0e0e0;
}

.advice-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.icon-container {
    flex-shrink: 0;
    margin-right: 20px;
}

.icon-container img {
    width: 70px; /* Increased icon size */
    height: 70px; /* Maintain aspect ratio */
    object-fit: cover;
    transition: transform 0.3s;
}

.icon-container img:hover {
    transform: scale(1.1);
}

.text-container {
    flex: 1;
}

.text-container h3 {
    font-size: 1.4rem;
    color: #007bff;
    margin: 0 0 10px;
    font-weight: 600;
}

.text-container p {
    font-size: 1.1rem;
    color: #555;
    line-height: 1.6;
}

/* Additional content styling */
.additional-content {
    margin-top: 30px;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

.additional-content h2 {
    font-size: 1.7rem;
    color: #333;
    margin-bottom: 15px;
    font-weight: 600;
}

.additional-content p {
    font-size: 1.1rem;
    color: #555;
    line-height: 1.6;
    margin-bottom: 15px;
}

.header-buttons {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.header-button {
    display: inline-block;
    padding: 15px 30px;
    margin: 0 10px;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    background: linear-gradient(45deg, #ff416c, #ff4b2b);
    border: none;
    border-radius: 50px;
    text-align: center;
    text-transform: uppercase;
    text-decoration: none;
    box-shadow: 0px 10px 20px rgba(255, 75, 43, 0.5);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.header-button:hover {
    background: linear-gradient(45deg, #ff4b2b, #ff416c);
    box-shadow: 0px 15px 25px rgba(255, 75, 43, 0.75);
    transform: translateY(-5px);
}

.header-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 300%;
    height: 300%;
    background: rgba(255, 255, 255, 0.3);
    opacity: 0;
    transition: all 0.5s ease;
    transform: rotate(45deg) translate(-300%, -300%);
    z-index: 0;
}

.header-button:hover::before {
    opacity: 1;
    transform: rotate(45deg) translate(0%, 0%);
}

.header-button span {
    position: relative;
    z-index: 1;
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
                    <li class="nav-item">
                        <?php
                        // Check the 'from' parameter in the URL
                        $from = isset($_GET['from']) ? $_GET['from'] : 'homepage';

                        // Determine the correct URL for the back button
                        if ($from === 'dashboard') {
                            $backUrl = 'dashboard.php';
                        } else {
                            $backUrl = 'index.php'; // Assuming index.php is your homepage
                        }
                        ?>
                        <!-- Output the back button with the dynamic URL -->
                        <a href="<?php echo $backUrl; ?>" class="nav-link home-button">Back</a>  
                    </li>
                    
                </ul>
                
            </nav>
        </div>
    </header>


    <div class="page-header">
        <h1>Emergency Rescue Assistance</h1>
        <p>Providing immediate help for animals in distress</p>

    </div>
    <div class="header-buttons">
        <button class="header-button" onclick="scrollToSection('formSection')">Emergency Rescue Form</button>
        <button class="header-button" onclick="scrollToSection('reportList')">Emergency Reports</button>
    </div>

    <br />
    <br />

    <div class="advice-section">
    <h2>What to Do in an Emergency Situation with a Stray Dog</h2>
    <div class="advice-content">
        <!-- Advice Item 1 -->
        <div class="advice-item">
            <div class="icon-container">
                <img src="images/calm.png" alt="Stay Calm">
            </div>
            <div class="text-container">
                <h3>Stay Calm</h3>
                <p>It’s important to remain calm and avoid sudden movements. Stray dogs can be unpredictable, and a calm demeanor can help keep the situation under control.</p>
            </div>
        </div>
        <!-- Advice Item 2 -->
        <div class="advice-item">
            <div class="icon-container">
                <img src="images/assess.png" alt="Assess the Dog">
            </div>
            <div class="text-container">
                <h3>Assess the Dog's Condition</h3>
                <p>Check if the dog is injured, aggressive, or scared. Look for signs of illness or distress, such as limping, bleeding, or visible fear.</p>
            </div>
        </div>
        <!-- Advice Item 3 -->
        <div class="advice-item">
            <div class="icon-container">
                <img src="images/contact.png" alt="Contact Authorities">
            </div>
            <div class="text-container">
                <h3>Contact Authorities</h3>
                <p>If the dog seems aggressive or injured, contact local animal control or a rescue organization immediately.</p>
            </div>
        </div>
    </div>
    <div class="additional-content">
        <h2>Additional Tips</h2>
        <p>Always carry a mobile phone with emergency numbers saved. If the dog is approachable, consider taking them to the nearest vet or shelter for further assistance.</p>
    </div>
</div>





            <div id="emergency-rescue-container" class="emergency-rescue-container">
        <div class="tabs">
            <button class="tab-button active" onclick="showSection('formSection')">Emergency Rescue Form</button>
            <button class="tab-button" onclick="showSection('vetContactSection')">Quick Vet Contact</button>
        </div>

        <div class="content">
            <!-- Emergency Rescue Form Section -->
            <div id="formSection" class="content-section">
            <form id="emergencyForm" method="POST" enctype="multipart/form-data">
            <h2>Emergency Rescue Form</h2>
                

                <label for="location">Location:</label>
                <div class="location-wrapper">
                    <input type="text" id="location" name="location" required placeholder="Enter location manually">
                    <button type="button" id="mapBtn">Select Location on Map</button>
                </div>
                
                <div id="map" class="map-container"></div>

                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>

                <label for="priority">Priority Level:</label>
                <div class="priority-wrapper">
                    <select id="priority" name="priority" required>
                        <option value="" disabled selected>Select priority level</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <label for="file">Upload Photo/Video:</label>
                <input type="file" id="file" name="file">

                <button type="submit">Submit</button>
            </form>
            </div>

    <!-- Quick Vet Contact Section -->
    <div id="vetContactSection" class="content-section" style="display: none;">
      <h2>Quick Vet Contact</h2>
      <div class="vet-clinic">
        <h3>City Vet Clinic</h3>
        <p>123 Main St, Springfield</p>
        <a href="tel:+1234567890" class="contact-button">Call Now</a>
        <a href="https://maps.google.com" target="_blank" class="contact-button">Get Directions</a>
      </div>

      <div class="vet-clinic">
        <h3>Happy Paws Veterinary</h3>
        <p>456 Elm St, Springfield</p>
        <a href="tel:+0987654321" class="contact-button">Call Now</a>
        <a href="https://maps.google.com" target="_blank" class="contact-button">Get Directions</a>
      </div>

      <!-- Add more vet clinics as needed -->
    </div>
  </div>
</div>

    <div class="emergency-reports">
    <h2>Emergency Reports</h2>
    <div id="reportList">
        <?php
        // Fetch reports from the database
        require_once 'utils/connect.php'; // Ensure the path is correct

        $sql = "SELECT id, description, location, photos, status, priority FROM emergencyreport"
            . " ORDER BY FIELD(priority, 'urgent', 'high', 'medium', 'low')"; // Order by priority level
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
                echo '<p class="priority ' . htmlspecialchars($row['priority']) . '">Priority: ' . htmlspecialchars($row['priority']) . '</p>';
                echo '<div class="status-update">';
                echo '<select class="status-select" data-report-id="' . htmlspecialchars($row['id']) . '">';
                echo '<option value="status" ' . ($row['status'] == 'status' ? 'selected' : '') . '>Status</option>';
                echo '<option value="rescued" ' . ($row['status'] == 'rescued' ? 'selected' : '') . '>Rescued</option>';
                echo '<option value="rescue in progress" ' . ($row['status'] == 'rescue in progress' ? 'selected' : '') . '>Rescue in Progress</option>';
                echo '</select>';
                echo '<button class="save-status-button" data-report-id="' . htmlspecialchars($row['id']) . '">Save</button>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No reports found.</p>';
        }

        $conn->close();
        ?>
    </div>
</div>


<div class="vet-clinics">
    <h2>Featured Vet Clinics</h2>
    <button>View Clinics</button>
    <div class="clinics">
        <div class="clinic">
            <img src="images/vetclinic.jpg" alt="Vet Clinic 1">
        </div>
        <div class="clinic">
            <img src="images/vetclinic2.jpg" alt="Vet Clinic 2">
        </div>
        <div class="clinic">
            <img src="images/vetclinic2.jpg" alt="Vet Clinic 3">
        </div>
        <div class="clinic">
            <img src="images/vetclinic2.jpg" alt="Vet Clinic 4">
        </div>
        <div class="clinic">
            <img src="images/vetclinic2.jpg" alt="Vet Clinic 5">
        </div>
    </div>
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


    <script>

document.addEventListener('DOMContentLoaded', function() {
    // Show the default section (Emergency Rescue Form) on page load
    showSection('formSection');

    document.querySelector('#emergencyForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);

        fetch('Emergency.php', {
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

    // Save status update button click event
    document.querySelectorAll('.save-status-button').forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.getAttribute('data-report-id');
            const statusSelect = this.previousElementSibling;
            const status = statusSelect.value;

            if (status && reportId) {
                updateStatus(reportId, status);
            }
        });
    });

    function updateStatus(reportId, status) {
        fetch('Emergency.php', {
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

 function showSection(sectionId) {
    // Hide all content sections
    document.querySelectorAll('.content-section').forEach(function(section) {
        section.style.display = 'none';
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(function(button) {
        button.classList.remove('active');
    });

    // Show the selected content section
    document.getElementById(sectionId).style.display = 'block';

    // Add active class to the selected tab button
    document.querySelector(`.tab-button[onclick="showSection('${sectionId}')"]`).classList.add('active');
}

        


           
            
    // Function to show a specific section
    function showSection(sectionId) {
        // Hide all content sections
        document.querySelectorAll('.content-section').forEach(function(section) {
            section.style.display = 'none';
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-button').forEach(function(button) {
            button.classList.remove('active');
        });

        // Show the selected content section
        document.getElementById(sectionId).style.display = 'block';

        // Add active class to the selected tab button
        document.querySelector(`.tab-button[onclick="showSection('${sectionId}')"]`).classList.add('active');
    }

    // Initially display the form section
    showSection('formSection');




    
    let map, marker;

    function initMap() {
        map = L.map('map').setView([6.9271, 79.8612], 13); // Default to Sri Lanka coordinates

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        marker = L.marker([6.9271, 79.8612]).addTo(map);

        map.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            
            marker.setLatLng([lat, lng]);

            // Reverse geocoding to get location name
            fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
                .then(response => response.json())
                .then(data => {
                    const locationName = data.display_name;
                    document.getElementById('location').value = locationName;
                })
                .catch(error => console.error('Error:', error));
        });
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        initMap();
    });


    function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}



    </script>
</body>
</html>
