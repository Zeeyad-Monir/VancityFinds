<?php
// Include authentication functions
require_once("auth_functions.php");

// Get current user if logged in
$current_user = get_current_user();
$is_logged_in = ($current_user !== null);
$is_guest = is_guest();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Discover Vancouver's hidden gems and local favorites with Vancity Finds.">
  <meta name="keywords" content="Vancouver, local spots, hidden gems, cafes, restaurants, outdoor activities">
  <title>Vancity Finds</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Header -->
  <header>
      <div class="container header-container">
          <a href="#home" class="logo">Vancity Finds</a>
          
          <div class="hamburger">
              <div></div>
              <div></div>
              <div></div>
          </div>
          
          <ul class="nav-menu">
              <li><a href="#home">Home</a></li>
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
                      <a href="logout.php" class="btn logout-btn">Sign Out</a>
                  </div>
              </li>
          </ul>
          
          <div class="search-bar">
              <input type="text" placeholder="Search...">
              <button>Search</button>
          </div>
      </div>
  </header>
  
  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="hero-background"></div>
    <div class="hero-white-layer"></div>
    <div class="hero-content">
        <h1>Discover Vancouver's Hidden Gems</h1>
        <p>Explore unique local spots curated by Vancouverites who know the city best.</p>
        <a href="#trending" class="btn cta-button">Explore Spots</a>
    </div>
  </section>
      
  <!-- Trending Spots Section -->
  <section class="trending-spots" id="trending">
      <div class="container">
          <h2>Trending Spots</h2>
          <div class="carousel-container">
              <button class="carousel-button prev-button">&lt;</button>
              <div class="spots-carousel" id="spots-container">
                  <!-- Spots will be dynamically generated here -->
              </div>
              <button class="carousel-button next-button">&gt;</button>
          </div>
      </div>
  </section>
      
  <!-- Categories Section -->
  <section class="categories" id="categories">
      <div class="container">
          <h2>Browse by Category</h2>
          <div class="categories-grid">
              <!-- Categories in HTML -->
              <div class="category-card">
                  <div class="category-icon" style="background-image: url('/api/placeholder/300/200')"></div>
                  <div class="category-name">Cafés & Coffee Shops</div>
              </div>
              
              <a href="restaurants.php" style="text-decoration: none; color: inherit;">
                  <div class="category-card">
                      <div class="category-icon" style="background-image: url('/api/placeholder/300/200')"></div>
                      <div class="category-name">Restaurants & Dining</div>
                  </div>
              </a>

                <a href="parks.php" style="text-decoration: none; color: inherit;">
                    <div class="category-card">
                        <div class="category-icon" style="background-image: url('/api/placeholder/300/200')"></div>
                        <div class="category-name">Parks</div>
                    </div>
                </a>
              
              <div class="category-card">
                  <div class="category-icon" style="background-image: url('/api/placeholder/300/200')"></div>
                  <div class="category-name">Arts & Culture</div>
              </div>
              
              <div class="category-card">
                  <div class="category-icon" style="background-image: url('/api/placeholder/300/200')"></div>
                  <div class="category-name">Shopping & Markets</div>
              </div>
              
              <div class="category-card">
                  <div class="category-icon" style="background-image: url('/api/placeholder/300/200')"></div>
                  <div class="category-name">Nightlife</div>
              </div>
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

    /**
     * Show a toast notification
     * @param {Object} options - Toast options
     * @param {string} options.title - Toast title
     * @param {string} options.message - Toast message
     * @param {string} options.type - Toast type (success, error)
     * @param {number} options.duration - Duration in ms
     */
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
    
    /**
     * Dismiss a toast notification
     * @param {HTMLElement} toast - Toast element to dismiss
     */
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
    
    /**
     * Show a success toast
     * @param {string} message - Toast message
     * @param {string} title - Toast title
     */
    success(message, title = 'Success!') {
      this.show({ title, message, type: 'success' });
    }
    
    /**
     * Show an error toast
     * @param {string} message - Toast message
     * @param {string} title - Toast title
     */
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
  
  <!-- Main JavaScript file -->
  <script src="script.js"></script>
  <script>
    // Override the populateCategories function if needed
    function populateCategories() {
      return;
    }
    
    // Check for auth parameter in URL
    document.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const authStatus = urlParams.get('auth');
      
      if (authStatus === 'success') {
        showToast('You have successfully logged in!', 'success', 'Welcome!');
      } else if (authStatus === 'guest') {
        showToast('You are browsing as a guest', 'success', 'Guest Access');
      } else if (authStatus === 'logout') {
        showToast('You have been logged out', 'success', 'Goodbye!');
      }
    });
    
    // Handle logout button
    document.addEventListener('DOMContentLoaded', () => {
      const logoutBtn = document.querySelector('.logout-btn');
      if (logoutBtn) {
        logoutBtn.addEventListener('click', async (e) => {
          e.preventDefault();
          try {
            const response = await fetch('logout.php');
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
