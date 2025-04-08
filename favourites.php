<?php
// Include authentication system
require_once("auth_system.php");

// Get current user if logged in
$current_user = get_current_user_app();
$is_logged_in = ($current_user !== null);
$is_guest = is_guest();

// Check if user has access to this page
// For favourites page, we'll only allow logged-in users
$has_access = $is_logged_in;

// Establish connection to the database
require_once("db_credentials.php");
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check if the connection was successful
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Get user's favorite parks if logged in
$favorite_parks = array();
if ($is_logged_in) {
    $user_id = $current_user['id'];
    $favorites_query = "SELECT p.* FROM parks p 
                        JOIN user_favorites uf ON p.ParkID = uf.park_id 
                        WHERE uf.user_id = ?
                        ORDER BY p.Name";
    $favorites_stmt = mysqli_prepare($connection, $favorites_query);
    mysqli_stmt_bind_param($favorites_stmt, "i", $user_id);
    mysqli_stmt_execute($favorites_stmt);
    $favorites_result = mysqli_stmt_get_result($favorites_stmt);
    
    // Fetch all favorite parks
    while ($park = mysqli_fetch_assoc($favorites_result)) {
        $favorite_parks[] = $park;
    }
    
    mysqli_stmt_close($favorites_stmt);
}

// If user is not logged in, we'll redirect them after showing a toast message
// The redirection will be handled by JavaScript
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vancity Finds - Favourites</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Additional styles for the favourites page */
        .favourites-section {
            padding-top: calc(var(--spacing-lg) * 1.5);
            padding-bottom: calc(var(--spacing-lg) * 1.5);
            margin-top: 60px;
        }
        
        .favourites-title {
            position: relative;
            padding-bottom: 10px;
            text-align: center;
            margin-bottom: var(--spacing-md);
        }
        
        .favourites-title::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 0;
            height: 3px;
            width: 130px;
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color), var(--secondary-color));
            background-size: 200% auto;
            animation: gradientAnimation 3s linear infinite;
            border-radius: 2px;
            transform: translateX(-50%);
        }
        
        .favourites-description {
            text-align: center;
            max-width: 800px;
            margin: 0 auto var(--spacing-lg);
        }
        
        .empty-favourites {
            text-align: center;
            padding: var(--spacing-lg);
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            margin: var(--spacing-md) auto;
            max-width: 600px;
        }
        
        .empty-favourites p {
            margin-bottom: var(--spacing-md);
            color: var(--dark-color);
            font-size: 1.1rem;
        }
        
        .empty-favourites .btn {
            display: inline-block;
            margin-top: var(--spacing-sm);
        }
        
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
    </style>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="index.php" class="logo">Vancity Finds</a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="parks.php">Parks</a></li>
                <li><a href="favourites.php" class="active">Favourites</a></li>
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

    <!-- Favourites Section -->
    <section class="favourites-section" id="favourites">
        <div class="container">
            <h2 class="favourites-title">My Favourite Parks</h2>
            <p class="favourites-description">View all your saved park spots in one place. Add parks to your favourites while browsing to keep track of places you want to visit.</p>

            <?php if ($has_access): ?>
                <!-- Display favourites if user is logged in -->
                <div class="parks-grid">
                    <?php if (count($favorite_parks) > 0): ?>
                        <?php foreach ($favorite_parks as $park): ?>
                            <div class="park-card">
                                <div class="park-image" style="background-image:url('/api/placeholder/300/200')"></div>
                                <div class="park-info">
                                    <div class="park-header">
                                        <h3><?= htmlspecialchars($park['Name']) ?></h3>
                                        <div class="heart-icon active" data-park-id="<?= $park['ParkID'] ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                        </div>
                                    </div>
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
                                    <a href="park-details.php?id=<?= $park['ParkID'] ?>" class="learn-more">Learn More</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Show empty state if no favorites -->
                        <div class="empty-favourites">
                            <p>You haven't added any parks to your favourites yet.</p>
                            <p>Browse our parks collection and click the heart icon to add parks to your favourites.</p>
                            <a href="parks.php" class="btn">Browse Parks</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- This section won't be visible as non-logged in users will be redirected -->
                <!-- But we'll include it as a fallback -->
                <div class="empty-favourites">
                    <p>Please log in to view and manage your favourite parks.</p>
                    <a href="auth.php?mode=login" class="btn">Log In</a>
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
                
                <div class="social-icons">
                    <a href="#">FB</a>
                    <a href="#">IG</a>
                    <a href="#">TW</a>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2025 Vancity Finds. All rights reserved.</p>
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

    // Check if user is logged in
    const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    
    // If not logged in, show toast and redirect
    if (!isLoggedIn) {
      // Show toast message
      toast.error('Please log in to view your favourites', 'Access Restricted');
      
      // Redirect to home page after a short delay
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 3000);
    }

    // Add event listeners to heart icons
    document.addEventListener('DOMContentLoaded', function() {
      const heartIcons = document.querySelectorAll('.heart-icon');
      heartIcons.forEach(icon => {
        icon.addEventListener('click', function() {
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
              // If we're on the favourites page, remove the park card
              const parkCard = heartIcon.closest('.park-card');
              if (window.location.pathname.includes('favourites.php')) {
                parkCard.style.opacity = '0';
                setTimeout(() => {
                  parkCard.remove();
                  
                  // Check if there are any parks left
                  const remainingCards = document.querySelectorAll('.park-card');
                  if (remainingCards.length === 0) {
                    // Show empty state if no parks left
                    const parksGrid = document.querySelector('.parks-grid');
                    parksGrid.innerHTML = `
                      <div class="empty-favourites">
                        <p>You haven't added any parks to your favourites yet.</p>
                        <p>Browse our parks collection and click the heart icon to add parks to your favourites.</p>
                        <a href="parks.php" class="btn">Browse Parks</a>
                      </div>
                    `;
                  }
                }, 300);
              } else {
                heartIcon.classList.remove('active');
              }
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
