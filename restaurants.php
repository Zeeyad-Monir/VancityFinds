<?php
// Include authentication system
require_once("auth_system.php");

// Get current user if logged in
$current_user = get_current_user_app();
$is_logged_in = ($current_user !== null);
$is_guest = is_guest();

// Check if user has access to this page
// For restaurants page, we'll allow both logged in users and guests
$has_access = $is_logged_in || $is_guest;

// If no access, redirect to auth page
if (!$has_access) {
    header("Location: auth.php?mode=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
        name="description"
        content="Discover Vancouver's best restaurants and eateries with Vancity Finds."
    />
    <meta
        name="keywords"
        content="Vancouver, restaurants, dining, Japanese food, Mexican food, Italian food, local eats"
    />
    <title>Vancity Finds - Restaurants</title>

    <!-- Existing styles -->
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="restaurants.css" />

    <!-- Lottie Web Component (needed to use <lottie-player>) -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
</head>
<body>
    <!-- Header -->
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
            
            <div class="search-bar">
                <input type="text" placeholder="Search restaurants..." />
                <button>Search</button>
            </div>
        </div>
    </header>
    
    <!-- Restaurant Hero Section -->
    <section class="restaurant-hero" id="restaurant-home">
        <div class="restaurant-hero-background"></div>
        <div class="hero-white-layer"></div>
        <div class="hero-content">
            <h1>Vancouver's Finest Restaurants</h1>
            <p>
                From authentic sushi to classic Italian, discover the best culinary
                experiences Vancouver has to offer.
            </p>
            <a href="#japanese" class="btn cta-button">Explore Restaurants</a>
        </div>
    </section>
    
    <!-- Japanese Cuisine Section with Lottie background -->
    <section class="cuisine-section lottie-section" id="japanese">
        <!-- Lottie container for background animation -->
        <div class="lottie-background">
            <!-- 
                lottie player, adjust settings here
            -->
            <lottie-player
      src="/json/japanThree.json"
      background="transparent"
      speed="0.3"
      preserveAspectRatio="none"
      style="width: 100%; height: 140%; opacity: 0.070;"
      loop
      autoplay
    ></lottie-player>
        </div>

        <div class="container">
            <h2 class="cuisine-title">Japanese Cuisine</h2>
            <p class="cuisine-description">
                Vancouver is renowned for its exceptional Japanese restaurants, offering
                everything from traditional sushi to modern fusion creations.
            </p>
            
            <div class="carousel-container">
                <button class="carousel-button prev-button" id="japanese-prev">&lt;</button>
                <div class="spots-carousel" id="japanese-container">
                    <!-- Japanese restaurants will be dynamically generated here -->
                </div>
                <button class="carousel-button next-button" id="japanese-next">&gt;</button>
            </div>
        </div>
    </section>
    
    <!-- Mexican Cuisine Section -->
    <section class="cuisine-section" id="mexican">
        <div class="container">
            <h2 class="cuisine-title">Mexican Cuisine</h2>
            <p class="cuisine-description">
                Enjoy authentic tacos, fresh guacamole, and spicy salsas at Vancouver's
                vibrant Mexican eateries.
            </p>
            
            <div class="carousel-container">
                <button class="carousel-button prev-button" id="mexican-prev">&lt;</button>
                <div class="spots-carousel" id="mexican-container">
                    <!-- Mexican restaurants will be dynamically generated here -->
                </div>
                <button class="carousel-button next-button" id="mexican-next">&gt;</button>
            </div>
        </div>
    </section>
    
    <!-- Italian Cuisine Section -->
    <section class="cuisine-section" id="italian">
        <div class="container">
            <h2 class="cuisine-title">Italian Cuisine</h2>
            <p class="cuisine-description">
                Savor handmade pasta, wood-fired pizzas, and decadent desserts at these
                local Italian favorites.
            </p>
            
            <div class="carousel-container">
                <button class="carousel-button prev-button" id="italian-prev">&lt;</button>
                <div class="spots-carousel" id="italian-container">
                    <!-- Italian restaurants will be dynamically generated here -->
                </div>
                <button class="carousel-button next-button" id="italian-next">&gt;</button>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
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

    // Replace alert with toast
    window.showToast = (message, type = 'success', title) => {
      if (type === 'success') {
        toast.success(message, title);
      } else if (type === 'error') {
        toast.error(message, title);
      }
    };
    </script>

    <script src="script.js"></script>
    <script src="restaurants.js"></script>
    
    <!-- Handle logout button -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
      const logoutBtn = document.querySelector('.logout-btn');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
          e.preventDefault();
          try {
            const response = await fetch('auth_system.php?action=logout');
            const data = await response.json();
            
            if (data.success) {
              window.location.href = 'index.php?auth=logout';
            }
          } catch (err) {
            console.error('Logout error:', err);
          }
        });
      }
    });
    </script>
</body>
</html>
