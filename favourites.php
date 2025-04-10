<?php
// Include authentication system
require_once("auth_system.php");

// Get current user if logged in
$current_user = get_current_user_app();
$is_logged_in = ($current_user !== null);
$is_guest = is_guest();

// Check if user has access to this page
// For favorites page, we'll require authentication
$has_access = $is_logged_in;

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

// Get user's favorite parks
$user_favorites = array();
$user_id = $current_user['id'];
$favorites_query = "SELECT p.* FROM parks p 
                    JOIN user_favorites uf ON p.ParkID = uf.park_id 
                    WHERE uf.user_id = ?";
$favorites_stmt = mysqli_prepare($connection, $favorites_query);
mysqli_stmt_bind_param($favorites_stmt, "i", $user_id);
mysqli_stmt_execute($favorites_stmt);
$favorites_result = mysqli_stmt_get_result($favorites_stmt);

// Google Custom Search API Integration for park images
$google_api_key = 'AIzaSyDynFyVOku8SS67VrTNBBKEOE9aLBVq4TY';  
$search_engine_id = '65a27083bf3aa48dd'; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vancity Finds - My Favorites</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        
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
            background-color: #f8f8f8;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .park-card-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .park-card {
            display: block;
            text-decoration: none;
            color: inherit;
        }
        
        /* Park image styling - FIXED */
        .park-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .park-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.3s ease;
        }
        
        .park-card:hover .park-image img {
            transform: scale(1.05);
        }
        
        /* Park info styling */
        .park-info {
            padding: 15px;
        }
        
        .park-info h3 {
            margin: 0 0 5px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .park-info p {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        /* Park features styling */
        .park-features {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .feature {
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        /* Park size styling */
        .park-size {
            font-size: 0.9rem;
            color: #555;
            margin-top: 5px;
        }
        
        /* Parks grid layout */
        .parks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        /* Empty favorites message */
        .empty-favorites {
            text-align: center;
            padding: 3rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .empty-favorites h3 {
            margin-bottom: 1rem;
            color: #343a40;
        }
        
        .empty-favorites p {
            margin-bottom: 1.5rem;
            color: #6c757d;
        }
        
        .browse-parks-btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .browse-parks-btn:hover {
            background-color: #0069d9;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .parks-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .park-image {
                height: 180px;
            }
        }
        
        @media (max-width: 480px) {
            .parks-grid {
                grid-template-columns: 1fr;
            }
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
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">Vancity Finds</a>
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

    <!-- Favorites Section -->
    <section class="favorites-section">
        <div class="container">
            <h2>My Favorite Parks</h2>
            <p>Here are the parks you've added to your favorites.</p>
            
            <?php if (mysqli_num_rows($favorites_result) > 0): ?>
                <!-- Park Cards Container -->
                <div class="parks-grid" id="favorites-container">
                    <?php while ($park = mysqli_fetch_assoc($favorites_result)): ?>
                        <?php
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
                        <div class="park-card-container" data-park-id="<?= $park['ParkID'] ?>">
                            <!-- Heart icon for favorites (always active in favorites page) -->
                            <div class="heart-icon active" data-park-id="<?= $park['ParkID'] ?>">
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
                </div>
            <?php else: ?>
                <!-- Empty favorites message -->
                <div class="empty-favorites">
                    <h3>No Favorites Yet</h3>
                    <p>You haven't added any parks to your favorites yet. Browse parks and click the heart icon to add them to your favorites.</p>
                    <a href="parks.php" class="browse-parks-btn">Browse Parks</a>
                </div>
            <?php endif; ?>
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
    
    <!-- Toast Notification System -->
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
            isLoggedIn: <?= $is_logged_in ? 'true' : 'false' ?>,
            id: <?= $is_logged_in ? $current_user['id'] : 'null' ?>
        };
    </script>
    
    <!-- Include favorites.js for heart icon functionality -->
    <script src="favorites.js"></script>
    
    <!-- Favorites page specific JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listener for heart icons in favorites page
            const heartIcons = document.querySelectorAll('.heart-icon');
            
            heartIcons.forEach(heart => {
                heart.addEventListener('click', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Get park ID from data attribute
                    const parkId = this.dataset.parkId;
                    const parkCard = this.closest('.park-card-container');
                    
                    // Toggle favorite via AJAX
                    const formData = new FormData();
                    formData.append('park_id', parkId);
                    
                    fetch('toggle_favorite.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // If removed from favorites, remove the card with animation
                            if (!data.is_favorite) {
                                parkCard.style.opacity = '0';
                                parkCard.style.transform = 'scale(0.8)';
                                parkCard.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                
                                setTimeout(() => {
                                    parkCard.remove();
                                    
                                    // Check if there are any favorites left
                                    const favoritesContainer = document.getElementById('favorites-container');
                                    if (favoritesContainer && favoritesContainer.children.length === 0) {
                                        // Replace with empty favorites message
                                        const emptyMessage = `
                                            <div class="empty-favorites">
                                                <h3>No Favorites Yet</h3>
                                                <p>You haven't added any parks to your favorites yet. Browse parks and click the heart icon to add them to your favorites.</p>
                                                <a href="parks.php" class="browse-parks-btn">Browse Parks</a>
                                            </div>
                                        `;
                                        
                                        const favoritesSection = document.querySelector('.favorites-section .container');
                                        favoritesSection.innerHTML = `
                                            <h2>My Favorite Parks</h2>
                                            <p>Here are the parks you've added to your favorites.</p>
                                            ${emptyMessage}
                                        `;
                                    }
                                    
                                    showToast('Park removed from favorites', 'success');
                                }, 300);
                            }
                        } else {
                            showToast(data.message, 'error', 'Error');
                        }
                    })
                    .catch(error => {
                        console.error('Error toggling favorite:', error);
                        showToast('Failed to update favorites. Please try again.', 'error', 'Error');
                    });
                });
            });
        });
    </script>
</body>
</html>

<?php
// Free the result set
mysqli_free_result($favorites_result);

// Close the connection
mysqli_close($connection);
?>
