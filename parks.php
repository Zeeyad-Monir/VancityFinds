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

// Establish connection to the database
require_once("db_credentials.php");
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
                    
                    <a href="park-details.php?id=<?= $park['ParkID'] ?>" class="park-card">
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
                    </a>
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

    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container"></div>
    
    <!-- Toast Notification Script -->
    <script>
    /**
     * Toast Notification System
     */
    class ToastNotification {
      constructor() {
        this.init();
      }

      init() {
        this.container = document.getElementById('toast-container');
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
        setTimeout(() => {
          this.dismiss(toast);
        }, duration);
      }

      dismiss(toast) {
        toast.classList.add('hide');
        setTimeout(() => {
          this.container.removeChild(toast);
        }, 300);
      }
    }

    // Initialize toast notification system
    const toast = new ToastNotification();

    // Logout functionality
    document.addEventListener('DOMContentLoaded', function() {
      const logoutBtn = document.querySelector('.logout-btn');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Send logout request
          fetch('auth_system.php?action=logout', {
            method: 'POST'
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Show success toast
              toast.show({
                title: 'Logged Out',
                message: 'You have been successfully logged out.',
                type: 'success'
              });
              
              // Redirect to home page after a short delay
              setTimeout(() => {
                window.location.href = 'index.php?logout=success';
              }, 1500);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            toast.show({
              title: 'Error',
              message: 'An error occurred while logging out.',
              type: 'error'
            });
          });
        });
      }
    });
    </script>
</body>
</html>
