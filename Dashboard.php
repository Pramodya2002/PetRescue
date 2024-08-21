


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stray Saver - Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
  }
      
               
        /* Header */
            .header {
                background: linear-gradient(135deg, #9e571d, #6f4501);
                padding: 20px 0; /* Increase padding for a larger header */
                color: white;
                border-bottom: 4px solid #5a2f02;
                display: flex; /* Use flexbox for alignment */
                align-items: center; /* Vertically center content */
                height: 150px; /* Increased height */
                justify-content: flex-start; /* Align items to the left */
                width: 100%;
            }
            
            .header-container {
                display: flex;
                align-items: center;
                width: 100%;
                width:100%;
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
                justify-content: flex-end; /* Align items to the right */
            }
            
            .nav-list {
                display: flex;
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
            }
            
            .nav-link:hover {
                background-color: rgba(255, 255, 255, 0.2);
            }
  
        .profile-button {
            display: flex;
            align-items: center;
            background-color: #af874c;
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: background-color 0.3s, color 0.3s, transform 0.3s;
            border-radius: 25px;
            padding: 12px 20px;
            margin-top: 30px;
        }

        .profile-button i {
            margin-right: 10px; /* Space between icon and text */
        }

        .profile-button:hover {
            background-color: #a05a45;
            transform: scale(1.05);
        }
        
        .dashboard-section {
            background-image: url('images/puppy.jpg'); /* Replace with your image URL */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 100px 20px; /* Increased top and bottom padding for a taller section */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            width: 98.9vw; /* Ensure it fills the width of the viewport */
            height: 500px; /* Adjust height as needed */
        }

        .dashboard-text h2 {
            color: #ffffff; /* White text for contrast */
            background-color: rgba(0, 0, 0, 0.6); /* Semi-transparent background */
            padding: 30px 40px; /* Increased padding for more emphasis */
            border-radius: 15px; /* Larger border-radius for a more rounded look */
            font-size: 36px; /* Larger font size for better visibility */
            font-family: 'Georgia', serif; /* Stylish serif font */
            font-weight: bold; /* Bold text for emphasis */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Subtle text shadow for added depth */
            line-height: 1.4; /* Spacing between lines for readability */
            letter-spacing: 1px; /* Slightly increased letter spacing */
            max-width: 100%; /* Responsive max-width to adapt to different screen sizes */
        }

        .dashboard-text {
            max-width: 800px; /* Increased max-width for better text block size */
        }

        .services-section {
                text-align: center;
                padding: 50px 20px;
                background-color: #f5f5f5;
            }

            .services-section h2 {
                font-size: 2.5rem;
                color: #333;
                margin-bottom: 30px;
            }

            .services-buttons {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 20px;
            }

            .services-item {
                background-color: #fff;
                border-radius: 15px;
                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                width: 200px;
                text-align: center;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .services-item:hover {
                transform: translateY(-10px);
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            }

            .services-item img {
                width: 100px;
                height: 100px;
                margin: 20px 0;
                transition: transform 0.3s ease;
            }

            .services-item:hover img {
                transform: scale(1.1);
            }

            .services-item h3 {
                font-size: 1.5rem;
                color: #333;
                margin: 15px 0;
            }

            .services-item p {
                color: #666;
                margin-bottom: 20px;
            }

            .services-item a {
                text-decoration: none;
                display: block;
                padding: 20px;
                background-color: #ffa200;
                color: #fff;
                border-radius: 10px;
                transition: background-color 0.3s ease;
            }

            .services-item a:hover {
                background-color: #d0a584;
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

            .emergency-container {
  display: flex;
  flex-direction: column; /* Stack icon and text vertically */
  align-items: center;
  margin-left: 20px;
}

.emergency-nav-button {
  background-color: #ffffff;
  border-radius: 50%;
  padding: 20px;
  font-size: 32px; /* Font size for the icon */
  color: #ff4d4d;
  font-weight: bold;
  text-align: center;
  line-height: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
  text-decoration: none;
  border: 4px solid #ff4d4d;
  height: 80px;
  width: 80px;
  box-sizing: border-box;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
}

.emergency-text {
  margin-top: 10px; /* Space between the button and text */
  font-size: 16px;
  color: white;
  font-weight: bold;
  text-align: center;
}

.emergency-nav-button:hover {
  background-color: #ff4d4d;
  color: #ffffff;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
  transform: scale(1.05);
}

.emergency-nav-button:active {
  background-color: #e60000;
  color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
  transform: scale(0.95);
}

.emergency-section {
  background: linear-gradient(135deg, #ffe3e3, #ffd1d1); /* Gradient background for modern look */
  border-radius: 12px; /* More rounded corners for a contemporary feel */
  padding: 30px; /* Increased padding for a spacious look */
  margin: 30px auto; /* Centered with margin around */
  max-width: 700px; /* Slightly wider for better visibility */
  text-align: center; /* Center-align text */
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Deeper shadow for a floating effect */
  border: 1px solid #ff4d4d; /* Border to complement button */
}

.emergency-section h2 {
  font-size: 32px; /* Larger font size for emphasis */
  color: #ff4d4d; /* Color to match the emergency button */
  margin-bottom: 20px; /* Space below the heading */
  font-family: 'Arial', sans-serif; /* Clean, modern font */
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1); /* Subtle shadow for text */
}

.emergency-section p {
  font-size: 18px; /* Larger font size for better readability */
  color: #333; /* Darker color for text readability */
  margin-bottom: 25px; /* Space below the paragraph */
  font-family: 'Arial', sans-serif; /* Consistent font */
  line-height: 1.6; /* Improved line spacing for readability */
}

.emergency-button {
  display: inline-flex; /* Align icon and text in a row */
  align-items: center; /* Center-align icon and text vertically */
  background: linear-gradient(145deg, #ff4d4d, #ff1a1a); /* Gradient background for the button */
  color: #ffffff; /* White text for contrast */
  font-size: 20px; /* Larger font size for button text */
  padding: 15px 30px; /* More padding for a larger button */
  border-radius: 8px; /* Rounded corners for the button */
  text-decoration: none; /* Remove underline from link */
  transition: background 0.3s, box-shadow 0.3s, transform 0.2s; /* Smooth transitions */
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3); /* Shadow for depth */
  font-weight: bold; /* Bold text for emphasis */
  border: 2px solid transparent; /* Transparent border for hover effect */
  cursor: pointer; /* Change cursor to pointer on hover */
  position: relative; /* For positioning the pseudo-element */
}

.emergency-button::before {
  content: ''; /* Empty content for the pseudo-element */
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.2); /* Light overlay effect */
  border-radius: 8px; /* Match button border-radius */
  opacity: 0; /* Hidden by default */
  transition: opacity 0.3s; /* Smooth transition for the overlay */
  z-index: 0; /* Place behind the button text */
}

.emergency-button:hover::before {
  opacity: 1; /* Show overlay on hover */
}

.emergency-button:hover {
  background: linear-gradient(145deg, #e60000, #ff1a1a); /* Darker gradient on hover */
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4); /* Enhanced shadow on hover */
  transform: scale(1.05); /* Slightly enlarge button on hover */
}

.emergency-button:active {
  background: linear-gradient(145deg, #d60000, #ff1a1a); /* Darker gradient on click */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5); /* Deeper shadow on click */
  transform: scale(0.95); /* Slightly shrink button on click */
}

.emergency-icon {
  font-size: 28px; /* Larger icon size for prominence */
  margin-right: 12px; /* Space between icon and text */
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
                        <a href="profile.php" class="nav-link profile-button">
                            <i class="fas fa-user"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item emergency-container">
                        <a href="Emergency.php?from=dashboard" class="emergency-nav-button">
                            <span class="icon">🚨</span>
                        </a>
                        <span class="emergency-text">Emergency Rescue</span>
                    </li>
                    
                </ul>
            </nav>
        </div>
    </header>
    <main>

        
        <div class="dashboard-section">
            <div class="dashboard-text">
                <h2>In every stray, a story untold;  In every click, a life unfolds.❣</h2>
            </div>
        </div>
        
        <div class="services-section">
            <h2>Explore Our Services</h2>
            <div class="services-buttons">
                <div class="services-item">
                    <a href="ReportStray.php"><img src="images/speaker.png" alt="Report a Stray"></a>
                    <h3>Report a Stray</h3>
                    <p>Help a dog in need</p>
                </div>
                <div class="services-item">
                    <a href="Vetclinic.php"><img src="images/vet.png" alt="Vet Clinic"></a>
                    <h3>Vet Clinics</h3>
                    <p>Immediate assistance</p>
                </div>
                <div class="services-item">
                <a href="lostfound.php"><img src="images/search.png" alt="Reunite Paws"></a>
                <h3>Reunite Paws</h3>
                    <p>Find lost dogs</p>
                </div>
                <div class="services-item">
                    <a href="Volunteer.php"><img src="images/volunteer.png" alt="Volunteer"></a>
                    <h3>Volunteer</h3>
                    <p>Join our cause</p>
                </div>
                <div class="services-item">
                    <a href="ShelterFoster.php"><img src="images/shelter.png" alt="Adoption"></a>
                    <h3>Shelters and Fosters</h3>
                    <p>Provide a home</p>
                </div>
            </div>
        </div>
        <div class="map-section">
            <img src="images/banner.jpg" alt="Dog Icons">
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

   <script>
    const slides = document.querySelectorAll('.slide');
const totalSlides = slides.length;
let currentSlide = 0;

function showSlide(index) {
    const offset = -index * 100;
    document.querySelector('.slider').style.transform = `translateX(${offset}%)`;
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % totalSlides;
    showSlide(currentSlide);
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
    showSlide(currentSlide);
}

document.addEventListener('DOMContentLoaded', () => {
    showSlide(currentSlide);
    setInterval(nextSlide, 3000); // Change slide every 3 seconds

    document.querySelector('.next').addEventListener('click', nextSlide);
    document.querySelector('.prev').addEventListener('click', prevSlide);
});

    </script>

</body>
</html>
