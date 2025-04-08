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

// Get user's favorite parks if logged in
$user_favorites = array();
if ($is_logged_in) {
    $user_id = $current_user['id'];
    $favorites_query = "SELECT park_id FROM user_favorites WHERE user_id = ?";
    $favorites_stmt = mysqli_prepare($connection, $favorites_query);
    mysqli_stmt_bind_param($favorites_stmt, "i", $user_id);
    mysqli_stmt_execute($favorites_stmt);
    $favorites_result = mysqli_stmt_get_result($favorites_stmt);
    
    while ($row = mysqli_fetch_assoc($favorites_result)) {
        $user_favorites[] = $row['park_id'];
    }
    
    mysqli_stmt_close($favorites_stmt);
}

// Fetch filter options for Neighborhood, Facilities, and Washrooms
$neighborhood_query = "SELECT DISTINCT NeighbourhoodName FROM parks ORDER BY NeighbourhoodName";
$facilities_query = "SELECT DISTINCT Facilities FROM parks";
$washrooms_query = "SELECT DISTINCT Washrooms FROM parks";

$neighborhood_result = mysqli_query($connection, $neighborhood_query);
$facilities_result = mysqli_query($connection, $facilities_query);
$washrooms_result = mysqli_query($connection, $washrooms_query);

// Default category selection
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$where_clauses = [];

// Category-based queries
if ($category == 'small') {
    $where_clauses[] = "Hectare < 2";
} elseif ($category == 'medium') {
    $where_clauses[] = "Hectare BETWEEN 2 AND 5";
} elseif ($category == 'large') {
    $where_clauses[] = "Hectare > 5";
}

// Search filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($connection, $_GET['search']);
    $where_clauses[] = "Name LIKE '%$search%'";
}

// Neighborhood filter
if (isset($_GET['neighborhood']) && $_GET['neighborhood'] != '') {
    $neighborhood = mysqli_real_escape_string($connection, $_GET['neighborhood']);
    $where_clauses[] = "NeighbourhoodName = '$neighborhood'";
}

// Facilities filter
if (isset($_GET['facilities']) && $_GET['facilities'] != '') {
    $facilities = mysqli_real_escape_string($connection, $_GET['facilities']);
    $where_clauses[] = "Facilities = '$facilities'";
}

// Washrooms filter
if (isset($_GET['washrooms']) && $_GET['washrooms'] != '') {
    $washrooms = mysqli_real_escape_string($connection, $_GET['washrooms']);
    $where_clauses[] = "Washrooms = '$washrooms'";
}

// Build the WHERE SQL clause
$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch parks based on applied filters
$select_all_parks_query = "SELECT * FROM parks $where_sql";
$all_parks_result = mysqli_query($connection, $select_all_parks_query);

// Check for query errors
if (!$all_parks_result) {
    echo "Error with database query: " . mysqli_error($connection);
    exit();
}

// Google Custom Search API Integration for park images
$google_api_key = 'AIzaSyAxiNMGiHju-pnUEtYGDluBQlRfTZXhrZc';  
$search_engine_id = '1479f15fc18ec497a'; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vancity Finds - Parks</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Heart icon styles */
        .park-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }
        
        .heart-icon {
            cursor: pointer;
            width: 24px;
            height: 24px;
            transition: all 0.3s ease;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            padding: 5px;
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
        
        /* Park card positioning */
        .park-card-container {
            position: relative;
        }
        
        .park-card {
            display: block;
            text-decoration: none;
            color: inherit;
        }
    </style>
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

    <!-- Filter Section -->
    <section class="filters-section">
        <div class="container">
            <form id="filter-form" method="GET" action="parks.php">
                <input type="hidden" name="category" value="<?= isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'all' ?>">
                
                <!-- Search Bar -->
                <div class="filter-group search-bar">
                    <input type="text" placeholder="Type a name..." name="search" id="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                
                <!-- Filters Group: Neighborhood, Facilities, Washrooms -->
                <div class="filter-group filters">
                    <div class="filter-item">
                        <label for="neighborhood">Neighborhood</label>
                        <select name="neighborhood" id="neighborhood">
                            <option value="">Select Neighborhood</option>
                            <?php while ($neighborhood = mysqli_fetch_assoc($neighborhood_result)): ?>
                                <option value="<?= htmlspecialchars($neighborhood['NeighbourhoodName']) ?>" <?= (isset($_GET['neighborhood']) && $_GET['neighborhood'] == $neighborhood['NeighbourhoodName']) ? 'selected' : '' ?>><?= htmlspecialchars($neighborhood['NeighbourhoodName']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="facilities">Facilities</label>
                        <select name="facilities" id="facilities">
                            <option value="">Select Facilities</option>
                            <option value="Y" <?= (isset($_GET['facilities']) && $_GET['facilities'] == 'Y') ? 'selected' : '' ?>>Available</option>
                            <option value="N" <?= (isset($_GET['facilities']) && $_GET['facilities'] == 'N') ? 'selected' : '' ?>>Not Available</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label for="washrooms">Washrooms</label>
                        <select name="washrooms" id="washrooms">
                            <option value="">Select Washrooms</option>
                            <option value="Y" <?= (isset($_GET['washrooms']) && $_GET['washrooms'] == 'Y') ? 'selected' : '' ?>>Available</option>
                            <option value="N" <?= (isset($_GET['washrooms']) && $_GET['washrooms'] == 'N') ? 'selected' : '' ?>>Not Available</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn">Search</button>
            </form>
        </div>
    </section>

    <!-- Parks Grid Section -->
    <section class="parks-section" id="all-parks">
        <div class="container">
            <!-- Dynamically change the title and description based on the category -->
            <h2 class="parks-title">
                <?php 
                    if ($category == 'small') {
                        echo 'Small Parks';
                    } elseif ($category == 'medium') {
                        echo 'Medium Parks';
                    } elseif ($category == 'large') {
                        echo 'Large Parks';
                    } else {
                        echo 'All Parks';
                    }
                ?>
            </h2>
            <p class="parks-description">
                <?php 
                    if ($category == 'small') {
                        echo 'Explore small parks with an area of less than 2 hectares in Vancouver.';
                    } elseif ($category == 'medium') {
                        echo 'Explore medium-sized parks with an area ranging from 2 to 5 hectares in Vancouver.';
                    } elseif ($category == 'large') {
                        echo 'Explore large parks with an area greater than 5 hectares in Vancouver.';
                    } else {
                        echo 'Explore all parks in Vancouver, from large green spaces to small neighborhood parks.';
                    }
                ?>
            </p>
            <!-- Park Cards Container -->
            <div class="parks-grid">
                <?php if (mysqli_num_rows($all_parks_result) > 0): ?>
                    <?php while ($park = mysqli_fetch_assoc($all_parks_result)): ?>
                        <?php
                        // Google Custom Search API Integration for park images
                        $query = urlencode($park['Name']);  // Search query (park name)
                        $google_search_url = "https://www.googleapis.com/customsearch/v1?q=$query&key=$google_api_key&cx=$search_engine_id&searchType=image&num=1";

                        // Use curl to fetch image data from Google Custom Search API
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $google_search_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $response = curl_exec($ch);
                        curl_close($ch);

                        // Decode the JSON response
                        $images = json_decode($response, true);
                        $image_url = '';

                        // Check if images are found and set the image URL
                        if (isset($images['items'][0]['link'])) {
                            $image_url = $images['items'][0]['link'];
                        } else {
                            $image_url = './photos/default-image.jpg';  // Fallback to default image if none found
                        }
                        ?>
                        <div class="park-card-container">
                            <!-- Heart icon for favorites -->
                            <div class="heart-icon <?= in_array($park['ParkID'], $user_favorites) ? 'active' : '' ?>" data-park-id="<?= $park['ParkID'] ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </div>
                            
                            <!-- Park Card with Link to Details -->
                            <a href="park-details.php?id=<?= $park['ParkID'] ?>" class="park-card">
                                <!-- Park Image -->
                                <div class="park-image">
                                    <img src="<?= $image_url ? $image_url : 'default-image.jpg' ?>" alt="<?= htmlspecialchars($park['Name']) ?>">
                                </div>

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
          if (toast.parentNode) {
            this.container.removeChild(toast);
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

    // Check if user is logged in
    const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    
    // Add event listener to favourites link
    document.addEventListener('DOMContentLoaded', function() {
      const favouritesLink = document.getElementById('favourites-link');
      if (favouritesLink && !isLoggedIn) {
        favouritesLink.addEventListener('click', function(e) {
          e.preventDefault();
          toast.error('Please log in to view your favourites', 'Access Restricted');
        });
      }
      
      // Add event listeners to heart icons
      const heartIcons = document.querySelectorAll('.heart-icon');
      heartIcons.forEach(icon => {
        icon.addEventListener('click', function(e) {
          // Prevent the click from propagating to the card link
          e.stopPropagation();
          
          if (!isLoggedIn) {
            toast.error('Please log in to add parks to your favourites', 'Login Required');
            return;
          }
          
          const parkId = this.getAttribute('data-park-id');
          toggleFavorite(parkId, this);
        });
      });
      
      // Function to toggle favorite status
      function toggleFavorite(parkId, heartIcon) {
        // Add pulse animation
        heartIcon.classList.add('heart-pulse');
        
        // Create form data
        const formData = new FormData();
        formData.append('park_id', parkId);
        
        // Send request to toggle favorite
        fetch('toggle_favorite.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            if (data.is_favorite) {
              heartIcon.classList.add('active');
              toast.success('Park added to your favourites');
            } else {
              heartIcon.classList.remove('active');
              toast.success('Park removed from your favourites');
            }
          } else {
            toast.error(data.message);
          }
          
          // Remove pulse animation after a delay
          setTimeout(() => {
            heartIcon.classList.remove('heart-pulse');
          }, 300);
        })
        .catch(error => {
          console.error('Error:', error);
          toast.error('An error occurred while updating your favourites');
          
          // Remove pulse animation
          heartIcon.classList.remove('heart-pulse');
        });
      }
      
      // Logout functionality
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
              toast.success('You have been successfully logged out', 'Logged Out');
              
              // Redirect to home page after a short delay
              setTimeout(() => {
                window.location.href = 'index.php?logout=success';
              }, 1500);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            toast.error('An error occurred while logging out', 'Error');
          });
        });
      }
    });
    </script>
</body>
</html>