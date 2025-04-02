<?php
// Include database credentials
require("db_credentials.php");

// Establish connection to the database
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check if the connection was successful
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Fetch filter options for Neighborhood, Facilities, and Washrooms
$neighborhood_query = "SELECT DISTINCT NeighbourhoodName FROM parks ORDER BY NeighbourhoodName";
$facilities_query = "SELECT DISTINCT Facilities FROM parks";
$washrooms_query = "SELECT DISTINCT Washrooms FROM parks";

$neighborhood_result = mysqli_query($connection, $neighborhood_query);
$facilities_result = mysqli_query($connection, $facilities_query);
$washrooms_result = mysqli_query($connection, $washrooms_query);

// Query to fetch all parks with filters
$where_clauses = [];
if (isset($_GET['neighborhood']) && $_GET['neighborhood'] != '') {
    $neighborhood = mysqli_real_escape_string($connection, $_GET['neighborhood']);
    $where_clauses[] = "NeighbourhoodName = '$neighborhood'";
}
if (isset($_GET['facilities']) && $_GET['facilities'] != '') {
    $facilities = mysqli_real_escape_string($connection, $_GET['facilities']);
    $where_clauses[] = "Facilities = '$facilities'";
}
if (isset($_GET['washrooms']) && $_GET['washrooms'] != '') {
    $washrooms = mysqli_real_escape_string($connection, $_GET['washrooms']);
    $where_clauses[] = "Washrooms = '$washrooms'";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Query to fetch parks with the applied filters
$select_all_parks_query = "SELECT * FROM parks $where_sql";
$all_parks_result = mysqli_query($connection, $select_all_parks_query);

// Check for query errors
if (!$all_parks_result) {
    echo "Error with database query: " . mysqli_error($connection);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vancity Finds - Parks</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">Vancity Finds</a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="#categories">Browse Spots</a></li>
                <li><a href="#footer">Contact</a></li>
                <li><a href="#" class="btn">Sign In</a></li>
            </ul>
        </div>
    </header>

    <!-- Filter Section -->
    <section class="filters-section">
        <div class="container">
            <form method="GET" action="parks.php">
                <div class="filter-group">
                    <label for="neighborhood">Neighborhood:</label>
                    <select name="neighborhood" id="neighborhood">
                        <option value="">Select Neighborhood</option>
                        <?php while ($neighborhood = mysqli_fetch_assoc($neighborhood_result)): ?>
                            <option value="<?= htmlspecialchars($neighborhood['NeighbourhoodName']) ?>" <?= (isset($_GET['neighborhood']) && $_GET['neighborhood'] == $neighborhood['NeighbourhoodName']) ? 'selected' : '' ?>><?= htmlspecialchars($neighborhood['NeighbourhoodName']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="facilities">Facilities:</label>
                    <select name="facilities" id="facilities">
                        <option value="">Select Facilities</option>
                        <option value="Y" <?= (isset($_GET['facilities']) && $_GET['facilities'] == 'Y') ? 'selected' : '' ?>>Available</option>
                        <option value="N" <?= (isset($_GET['facilities']) && $_GET['facilities'] == 'N') ? 'selected' : '' ?>>Not Available</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="washrooms">Washrooms:</label>
                    <select name="washrooms" id="washrooms">
                        <option value="">Select Washrooms</option>
                        <option value="Y" <?= (isset($_GET['washrooms']) && $_GET['washrooms'] == 'Y') ? 'selected' : '' ?>>Available</option>
                        <option value="N" <?= (isset($_GET['washrooms']) && $_GET['washrooms'] == 'N') ? 'selected' : '' ?>>Not Available</option>
                    </select>
                </div>
                <button type="submit" class="btn">Apply Filters</button>
            </form>
        </div>
    </section>


    <!-- Parks Grid Section -->
    <section class="parks-section" id="all-parks">
        <div class="container">
            <h2 class="parks-title">All Parks</h2>
            <p class="parks-description">Explore all parks in Vancouver, from large green spaces to small neighborhood parks.</p>

            <!-- Park Cards Container -->
            <div class="parks-grid">
                <?php if (mysqli_num_rows($all_parks_result) > 0): ?>
                    <?php while ($park = mysqli_fetch_assoc($all_parks_result)): ?>
                        <div class="park-card">
                            <div class="park-image" style="background-image:url('/api/placeholder/300/200')"></div>
                            <div class="park-info">
                                <h3><?= htmlspecialchars($park['Name']) ?></h3>
                                <p><?= htmlspecialchars($park['NeighbourhoodName']) ?></p>
                                <div class="park-features">
                                    <?php if ($park['Facilities'] == 'Y'): ?>
                                        <span class="feature">Facilities</span>
                                    <?php endif; ?>
                                    <?php if ($park['Washrooms'] == 'Y'): ?>
                                        <span class="feature">Washrooms</span>
                                    <?php endif; ?>
                                    <?php if ($park['SpecialFeatures'] == 'Y'): ?>
                                        <span class="feature">Special Features</span>
                                    <?php endif; ?>
                                </div>
                                <div class="park-size"><?= htmlspecialchars($park['Hectare']) ?> hectares</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No parks available at the moment.</p>
                <?php endif; ?>
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

    <script src="script.js"></script>
</body>
</html>

<?php
// Free the result set
mysqli_free_result($all_parks_result);
mysqli_free_result($neighborhood_result);
mysqli_free_result($facilities_result);
mysqli_free_result($washrooms_result);

// Close the connection
mysqli_close($connection);
?>
