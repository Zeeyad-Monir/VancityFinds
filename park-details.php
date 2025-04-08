<?php

// Include authentication system
require_once("auth_system.php");

// Get current user if logged in
$current_user = get_current_user_app();
$is_logged_in = ($current_user !== null);
$is_guest = is_guest();

// Check if user has access to this page
// For parks page, we'll allow all visitors (no authentication required)
$has_access = true; // Modified to allow all visitors

// If no access, redirect to auth page
if (!$has_access) {
    header("Location: auth.php?mode=login");
    exit;
}

// Include database credentials
require("db_credentials.php");

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($park['Name']) ?> - Park Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
            <div class="container header-container">
                <a href="index.php" class="logo">Vancity Finds</a>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#categories">Browse Spots</a></li>
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
            <div class="park-details">
                <h1><?= htmlspecialchars($park['Name']) ?></h1>
                <p><strong>Official Name:</strong> <?= $park['Official'] == 1 ? 'Yes' : 'No' ?></p>
                <p><strong>Advisories:</strong> <?= htmlspecialchars($park['Advisories']) ?: 'N/A' ?></p>
                <p><strong>Special Features:</strong> <?= htmlspecialchars($park['SpecialFeatures']) == 'Y' ? 'Yes' : 'No' ?></p>
                <p><strong>Facilities Available:</strong> <?= htmlspecialchars($park['Facilities']) == 'Y' ? 'Yes' : 'No' ?></p>
                <p><strong>Washrooms Available:</strong> <?= htmlspecialchars($park['Washrooms']) == 'Y' ? 'Yes' : 'No' ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($park['StreetNumber']) ?> <?= htmlspecialchars($park['StreetName']) ?>, <?= htmlspecialchars($park['NeighbourhoodName']) ?></p>
                <p><strong>Area:</strong> <?= htmlspecialchars($park['Hectare']) ?> hectares</p>

                <!-- Park Image -->
                <div class="park-image">
                    <img src="./photos/default-image.jpg" alt="<?= htmlspecialchars($park['Name']) ?>">
                </div>
            </div>
        </div>
    </section>

    <footer>
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

    <script src="script.js"></script>
</body>
</html>

<?php
// Free the result set
mysqli_free_result($park_result);

// Close the connection
mysqli_close($connection);
?>
