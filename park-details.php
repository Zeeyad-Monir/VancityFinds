<?php
// Include database credentials
require("db_credentials.php");

// Include authentication system
require_once("auth_system.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user if logged in
$current_user = get_current_user_app();
$is_logged_in = ($current_user !== null);
$is_guest = is_guest();

// Function to check if user is logged in
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function get_current_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Function to get current user display name
function get_current_user_display_name() {
    return isset($_SESSION['display_name']) ? $_SESSION['display_name'] : (isset($_SESSION['email']) ? explode('@', $_SESSION['email'])[0] : 'User');
}

// Google Custom Search API Integration for park images
$google_api_key = 'AIzaSyBaGXSgmFYdoYL4WVuNuhzLAqDPV8m3OA8';  
$search_engine_id = '65a27083bf3aa48dd'; 

// Establish connection to the database
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check if the connection was successful
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Get the parkID from the URL
$parkID = isset($_GET['id']) ? $_GET['id'] : '';

// If parkID is not set or invalid, redirect to parks page
if (empty($parkID) || !is_numeric($parkID)) {
    header("Location: parks.php"); // Redirect to the parks listing page
    exit();
}

// Query to fetch details of the selected park
$select_park_query = "SELECT * FROM parks WHERE ParkID = '$parkID'";
$park_result = mysqli_query($connection, $select_park_query);

// Check for query errors
if (!$park_result || mysqli_num_rows($park_result) == 0) {
    echo "No park found with the provided ID.";
    exit();
}

// Fetch the park details
$park = mysqli_fetch_assoc($park_result);

// Check if the park is in user's favorites
$is_favorite = false;
if ($is_logged_in) {
    $user_id = $current_user['id'];
    $favorites_query = "SELECT * FROM user_favorites WHERE user_id = ? AND park_id = ?";
    $favorites_stmt = mysqli_prepare($connection, $favorites_query);
    mysqli_stmt_bind_param($favorites_stmt, "ii", $user_id, $parkID);
    mysqli_stmt_execute($favorites_stmt);
    $favorites_result = mysqli_stmt_get_result($favorites_stmt);
    $is_favorite = mysqli_num_rows($favorites_result) > 0;
    mysqli_stmt_close($favorites_stmt);
}

// Fetch image from Google Custom Search API
$query = urlencode($park['Name']);
$google_search_url = "https://www.googleapis.com/customsearch/v1?q=$query&key=$google_api_key&cx=$search_engine_id&searchType=image&num=1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $google_search_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$images = json_decode($response, true);
if (isset($images['items'][0]['link'])) {
    $image_url = $images['items'][0]['link'];
} else {
    $image_url = './photos/default-image.jpg';  // Fallback to default image if none found
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($park['Name']) ?> - Park Details</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --light-bg: #f8fafc;
            --dark-bg: #1e293b;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --text-white: #f8fafc;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        /* Header Styling */
        header {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .logo:hover {
            color: var(--secondary-color);
        }
        
        /* Navigation Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }
        
        .hamburger div {
            width: 25px;
            height: 3px;
            background-color: var(--primary-color);
            margin: 3px 0;
            transition: var(--transition);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            align-items: center;
        }
        
        .nav-menu li {
            margin-left: 1.5rem;
        }
        
        .nav-menu a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .nav-menu a:hover {
            color: var(--primary-color);
        }
        
        /* Auth Buttons */
        .auth-buttons {
            display: flex;
            align-items: center;
        }
        
        .logged-out-buttons, .logged-in-buttons {
            display: flex;
            align-items: center;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .login-btn {
            color: var(--primary-color);
            background-color: transparent;
            border: 1px solid var(--primary-color);
            margin-right: 0.5rem;
        }
        
        .login-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .signup-btn, .logout-btn {
            color: white;
            background-color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .signup-btn:hover, .logout-btn:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .user-greeting {
            margin-right: 1rem;
            font-size: 0.9rem;
            color: var(--text-light);
        }
        
        .username {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        /* Responsive Navigation */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }
            
            .nav-menu {
                position: fixed;
                top: 60px;
                left: -100%;
                flex-direction: column;
                background-color: white;
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
                padding: 2rem 0;
                z-index: 99;
            }
            
            .nav-menu.active {
                left: 0;
            }
            
            .nav-menu li {
                margin: 1.5rem 0;
            }
            
            .auth-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .logged-out-buttons, .logged-in-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .btn {
                width: 80%;
                margin: 0.5rem auto;
                text-align: center;
            }
            
            .login-btn {
                margin-right: 0;
            }
            
            .user-greeting {
                margin-right: 0;
                margin-bottom: 0.5rem;
            }
        }
        
        /* Park Details Section Styling */
        .park-details-section {
            padding: 3rem 0;
            background-color: white;
        }
        
        .park-details-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        @media (min-width: 768px) {
            .park-details-container {
                grid-template-columns: 1fr 1fr;
                align-items: start;
            }
        }
        
        .park-info {
            padding: 2rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .park-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 0.5rem;
        }
        
        .park-neighborhood {
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .park-details-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .park-detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .park-detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-icon {
            width: 40px;
            height: 40px;
            background-color: #ebf5ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--primary-color);
            flex-shrink: 0;
        }
        
        .detail-content {
            flex: 1;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
            display: block;
        }
        
        .detail-value {
            color: var(--text-light);
        }
        
        .detail-value.yes {
            color: #10b981;
            font-weight: 500;
        }
        
        .detail-value.no {
            color: #ef4444;
            font-weight: 500;
        }
        
        /* Park Image Styling */
        .park-image-container {
            position: relative;
        }
        
        .park-image {
            width: 100%;
            height: 450px;
            overflow: hidden;
            position: relative;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .park-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.5s ease;
        }
        
        .park-image:hover img {
            transform: scale(1.05);
        }
        
        .image-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.7), transparent);
            padding: 1.5rem;
            color: white;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }
        
        .image-overlay h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
        }
        
        .image-overlay p {
            margin: 0;
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Heart icon styles */
        .heart-icon {
            cursor: pointer;
            width: 24px;
            height: 24px;
            transition: all 0.3s ease;
        }
        
        .heart-icon svg {
            width: 100%;
            height: 100%;
        }
        
        .heart-icon svg path {
            fill: transparent;
            stroke: var(--dark-color);
            stroke-width: 2;
            transition: all 0.3s ease;
        }
        
        .heart-icon.active svg path {
            fill: var(--accent-color);
            stroke: var(--accent-color);
        }
        
        .heart-icon:hover svg path {
            stroke: var(--accent-color);
        }
        
        /* Animation for heart when clicked */
        @keyframes heartPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .heart-pulse {
            animation: heartPulse 0.3s ease;
        }
        
        .favorite-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
            z-index: 10;
        }
        
        .favorite-btn i {
            color: #ef4444;
            font-size: 1.25rem;
        }
        
        .favorite-btn:hover {
            transform: scale(1.1);
        }
        
        /* Park Features */
        .park-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .feature-badge {
            background-color: #ebf5ff;
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .feature-badge i {
            margin-right: 0.5rem;
        }
        
        /* Review Section Styles */
        .reviews-section {
            margin-top: 2rem;
            padding: 2rem 0;
            background-color: #f7fafc;
        }
        
        .review-form {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 2rem;
        }
        
        .review-form h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            cursor: pointer;
            font-size: 2rem;
            color: #ddd;
            transition: color 0.3s ease;
            margin-right: 0.25rem;
        }
        
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #ffc107;
        }
        
        .review-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: var(--border-radius);
            font-family: var(--body-font);
            font-size: 1rem;
            margin-bottom: 1rem;
            resize: vertical;
            min-height: 100px;
        }
        
        .review-textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .submit-review-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        
        .submit-review-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .reviews-list {
            margin-top: 2rem;
        }
        
        .review-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--box-shadow);
            margin-bottom: 1rem;
            position: relative;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .review-user {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .review-date {
            font-size: 0.85rem;
            color: #718096;
        }
        
        .review-rating {
            display: flex;
            margin-bottom: 0.5rem;
        }
        
        .review-star {
            color: #ffc107;
            margin-right: 0.25rem;
        }
        
        .review-content {
            line-height: 1.6;
        }
        
        .delete-review {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.25rem;
            color: #e53e3e;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }
        
        .delete-review:hover {
            opacity: 1;
        }
        
        .login-prompt {
            text-align: center;
            padding: 1.5rem;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .login-prompt a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-prompt a:hover {
            text-decoration: underline;
        }
        
        .no-reviews {
            text-align: center;
            padding: 2rem;
            color: #718096;
        }
        
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
            width: 100%;
            pointer-events: none;
        }

        .toast {
            display: flex;
            align-items: center;
            background-color: white;
            border-left: 4px solid #38a169;
            border-radius: 6px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            margin-bottom: 16px;
            padding: 16px;
            transform: translateX(120%);
            transition: transform 0.3s ease-in-out;
            pointer-events: auto;
            opacity: 0;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast-success {
            border-left-color: #38a169;
        }

        .toast-error {
            border-left-color: #e53e3e;
        }

        .toast-icon {
            color: #38a169;
            flex-shrink: 0;
            margin-right: 12px;
        }

        .toast-error .toast-icon {
            color: #e53e3e;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 4px;
            color: #1a202c;
        }

        .toast-message {
            font-size: 0.85rem;
            color: #4a5568;
        }

        .toast-close {
            background: transparent;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            padding: 4px;
            margin-left: 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, color 0.2s;
        }

        .toast-close:hover {
            background-color: #f7fafc;
            color: #718096;
        }

        /* Progress Bar for Auto-dismiss */
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background-color: rgba(66, 153, 225, 0.5);
            width: 100%;
            border-radius: 0 0 6px 6px;
            transform-origin: left;
        }

        /* Toast animation */
        @keyframes progress {
            from { transform: scaleX(1); }
            to { transform: scaleX(0); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .park-title {
                font-size: 2rem;
            }
            
            .park-image {
                height: 350px;
            }
            
            .park-info, .park-image-container {
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header with Navigation from index.php -->
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">Vancity Finds</a>
            
            <div class="hamburger">
                <div></div>
                <div></div>
                <div></div>
            </div>
            
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="parks.php">Browse Spots</a></li>
                <li><a href="favourites.php" id="favourites-link">Favourites</a></li>
                <li><a href="#footer">Contact</a></li>
                <!-- Auth buttons container -->
                <li class="auth-buttons">
                    <!-- Button shown when logged out -->
                    <div class="logged-out-buttons" <?php if ($is_logged_in) echo 'style="display: none;"'; ?>>
                        <a href="auth.php?mode=login" class="btn login-btn">Log In</a>
                        <a href="auth.php?mode=signup" class="btn signup-btn">Sign Up</a>
                    </div>
                    <!-- Button and indicator shown when logged in -->
                    <div class="logged-in-buttons" <?php if (!$is_logged_in) echo 'style="display: none;"'; ?>>
                        <span class="user-greeting">Hello, <span class="username"><?php echo $is_logged_in ? htmlspecialchars($current_user['display_name']) : 'User'; ?></span>!</span>
                        <a href="#" class="btn logout-btn">Sign Out</a>
                    </div>
                </li>
            </ul>
        </div>
    </header>

    <section class="park-details-section">
        <div class="container">
            <div class="park-details-container">
                <div class="park-info">
                    <h1 class="park-title"><?= htmlspecialchars($park['Name']) ?></h1>
                    <div class="park-neighborhood"><?= htmlspecialchars($park['NeighbourhoodName']) ?></div>
                    
                    <ul class="park-details-list">
                        <li class="park-detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Official Name</span>
                                <span class="detail-value <?= $park['Official'] == 1 ? 'yes' : 'no' ?>">
                                    <?= $park['Official'] == 1 ? 'Yes' : 'No' ?>
                                </span>
                            </div>
                        </li>
                        
                        <li class="park-detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Advisories</span>
                                <span class="detail-value">
                                    <?= htmlspecialchars($park['Advisories']) ?: 'None' ?>
                                </span>
                            </div>
                        </li>
                        
                        <li class="park-detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Special Features</span>
                                <span class="detail-value <?= htmlspecialchars($park['SpecialFeatures']) == 'Y' ? 'yes' : 'no' ?>">
                                    <?= htmlspecialchars($park['SpecialFeatures']) == 'Y' ? 'Yes' : 'No' ?>
                                </span>
                            </div>
                        </li>
                        
                        <li class="park-detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Facilities Available</span>
                                <span class="detail-value <?= htmlspecialchars($park['Facilities']) == 'Y' ? 'yes' : 'no' ?>">
                                    <?= htmlspecialchars($park['Facilities']) == 'Y' ? 'Yes' : 'No' ?>
                                </span>
                            </div>
                        </li>
                        
                        <li class="park-detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-toilet"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Washrooms Available</span>
                                <span class="detail-value <?= htmlspecialchars($park['Washrooms']) == 'Y' ? 'yes' : 'no' ?>">
                                    <?= htmlspecialchars($park['Washrooms']) == 'Y' ? 'Yes' : 'No' ?>
                                </span>
                            </div>
                        </li>
                        
                        <li class="park-detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Location</span>
                                <span class="detail-value">
                                    <?= htmlspecialchars($park['StreetNumber']) ?> <?= htmlspecialchars($park['StreetName']) ?>, <?= htmlspecialchars($park['NeighbourhoodName']) ?>
                                </span>
                            </div>
                        </li>
                        
                        <li class="park-detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-ruler-combined"></i>
                            </div>
                            <div class="detail-content">
                                <span class="detail-label">Area</span>
                                <span class="detail-value">
                                    <?= htmlspecialchars($park['Hectare']) ?> hectares
                                </span>
                            </div>
                        </li>
                    </ul>
                    
                    <div class="park-features">
                        <?php if ($park['Facilities'] == 'Y'): ?>
                            <div class="feature-badge">
                                <i class="fas fa-volleyball-ball"></i> Sports Facilities
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($park['Washrooms'] == 'Y'): ?>
                            <div class="feature-badge">
                                <i class="fas fa-restroom"></i> Washrooms
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($park['SpecialFeatures'] == 'Y'): ?>
                            <div class="feature-badge">
                                <i class="fas fa-star"></i> Special Features
                            </div>
                        <?php endif; ?>
                        
                        <div class="feature-badge">
                            <i class="fas fa-tree"></i> Green Space
                        </div>
                    </div>
                </div>
                
                <div class="park-image-container">
                    <!-- Heart icon for favorites -->
                    <div class="heart-icon <?= $is_favorite ? 'active' : '' ?>" data-park-id="<?= $parkID ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </div>
                    
                    <div class="park-image">
                        <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($park['Name']) ?>">
                        
                        <div class="image-overlay">
                            <h3>Visit <?= htmlspecialchars($park['Name']) ?></h3>
                            <p>Explore this beautiful park in <?= htmlspecialchars($park['NeighbourhoodName']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews-section">
        <div class="container">
            <h2>Reviews</h2>
            
            <?php if (is_user_logged_in()): ?>
            <!-- Review Form for logged-in users -->
            <div class="review-form">
                <h3>Write a Review</h3>
                <form id="review-form">
                    <input type="hidden" name="park_id" value="<?= $parkID ?>">
                    
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1">★</label>
                    </div>
                    
                    <textarea class="review-textarea" name="comment" placeholder="Share your experience at this park..." required></textarea>
                    
                    <button type="submit" class="submit-review-btn">Post Review</button>
                </form>
            </div>
            <?php else: ?>
            <!-- Login prompt for visitors -->
            <div class="login-prompt">
                <p>Please <a href="auth.php">log in</a> to write a review.</p>
            </div>
            <?php endif; ?>
            
            <!-- Reviews List -->
            <div class="reviews-list" id="reviews-container">
                <!-- Reviews will be loaded here via JavaScript -->
                <div class="no-reviews">Loading reviews...</div>
            </div>
        </div>
    </section>

    <footer id="footer">
        <div class="container">
            <div class="footer-content">
                <div class="contact-info">
                    <h3>Contact Us</h3>
                    <p>Email: info@vancityfinds.com</p>
                    <p>Phone: (604) 555-1234</p>
                    <p>123 Vancouver Street, Vancouver, BC</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container"></div>

    <script src="script.js"></script>
    <script>
        /***** Toast Notification System *****/
        class ToastNotification {
            constructor() {
                this.init();
            }

            init() {
                this.container = document.getElementById('toast-container');
                if (!this.container) {
                    this.container = document.createElement('div');
                    this.container.id = 'toast-container';
                    this.container.className = 'toast-container';
                    document.body.appendChild(this.container);
                }
            }

            show({ title = 'Success!', message = '', type = 'success', duration = 5000 }) {
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                
                // Create progress bar for auto-dismiss
                const progressBar = document.createElement('div');
                progressBar.className = 'toast-progress';
                
                // Add content to toast
                toast.innerHTML = `
                    <div class="toast-icon">
                        ${type === 'success' ? 
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>' : 
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'
                        }
                    </div>
                    <div class="toast-content">
                        <div class="toast-title">${title}</div>
                        <div class="toast-message">${message}</div>
                    </div>
                    <button class="toast-close" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                `;
                
                // Add progress bar for auto-dismiss
                toast.appendChild(progressBar);
                
                // Add to container
                this.container.appendChild(toast);
                
                // Close button functionality
                const closeBtn = toast.querySelector('.toast-close');
                closeBtn.addEventListener('click', () => {
                    this.dismiss(toast);
                });
                
                // Animation for progress bar
                progressBar.style.animation = `progress ${duration}ms linear forwards`;
                
                // Show toast with animation
                setTimeout(() => {
                    toast.classList.add('show');
                }, 10);
                
                // Auto dismiss
                this.autoClose = setTimeout(() => {
                    this.dismiss(toast);
                }, duration);
            }
            
            dismiss(toast) {
                // Remove show class to trigger hide animation
                toast.classList.remove('show');
                
                // Remove from DOM after animation completes
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
            
            success(message, title = 'Success!') {
                this.show({ title, message, type: 'success' });
            }
            
            error(message, title = 'Error') {
                this.show({ title, message, type: 'error' });
            }
        }

        // Initialize toast notification system
        const toast = new ToastNotification();

        // Replace alert with toast
        window.showToast = (message, type = 'success', title) => {
            if (type === 'success') {
                toast.success(message, title);
            } else if (type === 'error') {
                toast.error(message, title);
            }
        };

        // Current user information
        const currentUser = {
            isLoggedIn: <?= is_user_logged_in() ? 'true' : 'false' ?>,
            id: <?= is_user_logged_in() ? get_current_user_id() : 'null' ?>,
            displayName: "<?= is_user_logged_in() ? get_current_user_display_name() : '' ?>"
        };

        // Function to load reviews
        function loadReviews() {
            const parkId = <?= $parkID ?>;
            const reviewsContainer = document.getElementById('reviews-container');
            
            // Create form data
            const formData = new FormData();
            formData.append('action', 'get_reviews');
            formData.append('park_id', parkId);
            
            // Fetch reviews from server
            fetch('park_reviews.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.reviews.length === 0) {
                        reviewsContainer.innerHTML = '<div class="no-reviews">No reviews yet. Be the first to write a review!</div>';
                        return;
                    }
                    
                    // Clear container
                    reviewsContainer.innerHTML = '';
                    
                    // Add each review
                    data.reviews.forEach(review => {
                        const reviewCard = document.createElement('div');
                        reviewCard.className = 'review-card';
                        reviewCard.dataset.reviewId = review.id;
                        
                        // Create stars HTML
                        let starsHtml = '';
                        for (let i = 0; i < review.rating; i++) {
                            starsHtml += '<span class="review-star">★</span>';
                        }
                        
                        // Add delete button if it's the user's own review
                        const deleteButton = review.is_own_review ? 
                            `<button class="delete-review" data-review-id="${review.id}" aria-label="Delete review">×</button>` : '';
                        
                        reviewCard.innerHTML = `
                            ${deleteButton}
                            <div class="review-header">
                                <span class="review-user">${review.display_name || review.email.split('@')[0]}</span>
                                <span class="review-date">${review.formatted_date}</span>
                            </div>
                            <div class="review-rating">
                                ${starsHtml}
                            </div>
                            <div class="review-content">
                                ${review.comment}
                            </div>
                        `;
                        
                        reviewsContainer.appendChild(reviewCard);
                    });
                    
                    // Add event listeners to delete buttons
                    document.querySelectorAll('.delete-review').forEach(button => {
                        button.addEventListener('click', handleDeleteReview);
                    });
                } else {
                    showToast(data.message, 'error', 'Error Loading Reviews');
                }
            })
            .catch(error => {
                console.error('Error loading reviews:', error);
                showToast('Failed to load reviews. Please try again later.', 'error', 'Error');
            });
        }

        // Function to handle review submission
        function handleReviewSubmit(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'submit_review');
            
            fetch('park_reviews.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success', 'Review Submitted');
                    
                    // Reset form
                    form.reset();
                    
                    // Reload reviews
                    loadReviews();
                } else {
                    showToast(data.message, 'error', 'Submission Error');
                }
            })
            .catch(error => {
                console.error('Error submitting review:', error);
                showToast('Failed to submit review. Please try again later.', 'error', 'Error');
            });
        }

        // Function to handle review deletion
        function handleDeleteReview(event) {
            if (!confirm('Are you sure you want to delete this review?')) {
                return;
            }
            
            const reviewId = event.target.dataset.reviewId;
            const formData = new FormData();
            formData.append('action', 'delete_review');
            formData.append('review_id', reviewId);
            
            fetch('park_reviews.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success', 'Review Deleted');
                    
                    // Remove the review card from DOM
                    const reviewCard = document.querySelector(`.review-card[data-review-id="${reviewId}"]`);
                    if (reviewCard) {
                        reviewCard.remove();
                    }
                    
                    // If no reviews left, show message
                    const reviewsContainer = document.getElementById('reviews-container');
                    if (reviewsContainer.children.length === 0) {
                        reviewsContainer.innerHTML = '<div class="no-reviews">No reviews yet. Be the first to write a review!</div>';
                    }
                } else {
                    showToast(data.message, 'error', 'Deletion Error');
                }
            })
            .catch(error => {
                console.error('Error deleting review:', error);
                showToast('Failed to delete review. Please try again later.', 'error', 'Error');
            });
        }

        // Add event listener to review form
        const reviewForm = document.getElementById('review-form');
        if (reviewForm) {
            reviewForm.addEventListener('submit', handleReviewSubmit);
        }

        // Mobile navigation toggle
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');
        
        if (hamburger) {
            hamburger.addEventListener('click', function() {
                navMenu.classList.toggle('active');
            });
        }

        // Load reviews when page loads
        document.addEventListener('DOMContentLoaded', () => {
            loadReviews();
        });
    </script>
    
    <!-- Include favorites.js for heart icon functionality -->
    <script src="favorites.js"></script>
</body>
</html>

<?php
// Free the result set
mysqli_free_result($park_result);

// Close the connection
mysqli_close($connection);
?>
