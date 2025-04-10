<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include authentication system
require_once("auth_system.php");

// Get current user if logged in
$current_user = get_current_user_app();
$is_logged_in = ($current_user !== null);
$is_guest = is_guest();

// Google Custom Search API Integration for park images
$google_api_key = 'AIzaSyAxiNMGiHju-pnUEtYGDluBQlRfTZXhrZc';  
$search_engine_id = '65a27083bf3aa48dd'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Discover Vancouver's hidden parks and local favorites with Vancity Parks.">
  <meta name="keywords" content="Discover Vancouver's hidden parks and local favorites with Vancity Parks">
  <title>Vancity Parks</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* Park image styling */
    .spot-image {
        width: 100%;
        height: 200px;
        overflow: hidden;
        position: relative;
        border-radius: 8px 8px 0 0;
    }
    
    .spot-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
    }
    
    .spot-card:hover .spot-image img {
        transform: scale(1.05);
    }
    
    /* Category image styling */
    .category-icon {
        width: 100%;
        height: 150px;
        overflow: hidden;
        position: relative;
        border-radius: 8px 8px 0 0;
    }
    
    .category-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.3s ease;
    }
    
    .category-card:hover .category-icon img {
        transform: scale(1.05);
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header>
      <div class="container header-container">
          <a href="#home" class="logo">Vancity Parks</a>
          
          <div class="hamburger">
              <div></div>
              <div></div>
              <div></div>
          </div>
          
          <ul class="nav-menu">
              <li><a href="#home">Home</a></li>
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
          
          <!-- <div class="search-bar">
              <input type="text" placeholder="Search...">
              <button>Search</button>
          </div> -->
      </div>
  </header>
  
  <!-- Hero Section -->
  <section class="hero" id="home">
    <div class="hero-background"></div>
    <div class="hero-white-layer"></div>
    <div class="hero-content">
        <h1>Discover Vancouver's Parks</h1>
        <p>Explore unique local Parks curated by Vancouverites who know the city best.</p>
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
                  <?php
                  // Include the function to get trending parks
                  require_once("get_trending_parks.php");
                  
                  // Get trending parks (limit to 9)
                  $trending_parks = get_trending_parks(9);
                  
                  // Display trending parks
                  foreach ($trending_parks as $park): 
                      // Get features for description
                      $features = [];
                      if ($park['Facilities'] == 'Y') $features[] = 'Facilities';
                      if ($park['Washrooms'] == 'Y') $features[] = 'Washrooms';
                      if ($park['SpecialFeatures'] == 'Y') $features[] = 'Special Features';
                      $description = !empty($features) ? implode(', ', $features) : 'Park';
                      
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
                  <div class="spot-card">
                      <div class="spot-image">
                          <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($park['Name']) ?>">
                      </div>
                      <div class="spot-content">
                          <span class="spot-category"><?= htmlspecialchars($park['NeighbourhoodName']) ?></span>
                          <h3 class="spot-title"><?= htmlspecialchars($park['Name']) ?></h3>
                          <p class="spot-description"><?= $description ?></p>
                          <a href="park-details.php?id=<?= $park['ParkID'] ?>" class="learn-more">Learn More</a>
                      </div>
                  </div>
                  <?php endforeach; ?>
              </div>
              <button class="carousel-button next-button">&gt;</button>
          </div>
      </div>
  </section>
  
  <?php
  // Only show favorites section for logged-in users
  if ($is_logged_in): 
      // Include the function to get user favorites
      require_once("get_user_favorites.php");
      
      // Get user's favorite parks (limit to 3)
      $favorite_parks = get_user_favorite_parks($current_user['id'], 3);
  ?>
  <!-- User Favorites Section -->
  <section class="trending-spots user-favorites" id="favorites">
      <div class="container">
          <h2>Your Favorites</h2>
          <div class="carousel-container">
              <?php if (count($favorite_parks) > 0): ?>
                  <div class="spots-carousel" id="favorites-container">
                      <?php foreach ($favorite_parks as $park): 
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
                          <div class="spot-card">
                              <div class="spot-image">
                                  <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($park['Name']) ?>">
                              </div>
                              <div class="spot-content">
                                  <span class="spot-category"><?= htmlspecialchars($park['NeighbourhoodName']) ?></span>
                                  <h3 class="spot-title"><?= htmlspecialchars($park['Name']) ?></h3>
                                  <p class="spot-description">
                                      <?php 
                                      $features = [];
                                      if ($park['Facilities'] == 'Y') $features[] = 'Facilities';
                                      if ($park['Washrooms'] == 'Y') $features[] = 'Washrooms';
                                      if ($park['SpecialFeatures'] == 'Y') $features[] = 'Special Features';
                                      echo !empty($features) ? implode(', ', $features) : 'Park';
                                      ?>
                                  </p>
                                  <a href="park-details.php?id=<?= $park['ParkID'] ?>" class="learn-more">Learn More</a>
                              </div>
                          </div>
                      <?php endforeach; ?>
                  </div>
              <?php else: ?>
                  <div class="no-favorites-message">
                      <p>No Favourites Currently</p>
                      <a href="parks.php" class="btn">Browse Parks</a>
                  </div>
              <?php endif; ?>
          </div>
      </div>
  </section>
  <?php endif; ?>
      
  <!-- Categories Section -->
  <section class="categories" id="categories">
      <div class="container">
          <h2>Browse by Category</h2>
          <div class="categories-grid">
              <a href="parks.php" style="text-decoration: none; color: inherit;">
                  <div class="category-card">
                      <div class="category-icon">
                          <!-- Using specific local image for All Parks -->
                          <img src="./photos/all-parks.jpg" alt="All Parks">
                      </div>
                      <div class="category-name">All Parks</div>
                  </div>
              </a>

              <a href="parks.php?category=small" style="text-decoration: none; color: inherit;">
                  <div class="category-card">
                      <div class="category-icon">
                          <!-- Using specific local image for Small Parks -->
                          <img src="./photos/small-park.jpeg" alt="Small Parks">
                      </div>
                      <div class="category-name">Small Parks</div>
                  </div>
              </a>

              <a href="parks.php?category=medium" style="text-decoration: none; color: inherit;">
                  <div class="category-card">
                      <div class="category-icon">
                          <!-- Using specific local image for Medium Parks -->
                          <img src="./photos/medium-park.webp" alt="Medium Parks">
                      </div>
                      <div class="category-name">Medium Parks</div>
                  </div>
              </a>

              <a href="parks.php?category=large" style="text-decoration: none; color: inherit;">
                  <div class="category-card">
                      <div class="category-icon">
                          <!-- Using specific local image for Large Parks -->
                          <img src="./photos/large-park.jpg" alt="Large Parks">
                      </div>
                      <div class="category-name">Large Parks</div>
                  </div>
              </a>
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
  
  <!-- Main JavaScript files -->
  <script src="./javascript/script.js"></script>
  <script src="favourites.js"></script>
  <script>
    // Override the populateCategories function if needed
    function populateCategories() {
      return;
    }
    
    // Override the populateSpots function to prevent placeholder data
    function populateSpots() {
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
      
      // Check if user is logged in
      const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
      
      // Add event listener to favourites link
      const favouritesLink = document.getElementById('favourites-link');
      if (favouritesLink && !isLoggedIn) {
        favouritesLink.addEventListener('click', function(e) {
          e.preventDefault();
          toast.error('Please log in to view your favourites', 'Access Restricted');
        });
      }
    });
    
    // Handle logout button
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
            } else {
              toast.error(data.message || 'Logout failed');
            }
          } catch (error) {
            toast.error('An error occurred during logout');
            console.error('Logout error:', error);
          }
        });
      }
    });
  </script>
</body>
</html>
