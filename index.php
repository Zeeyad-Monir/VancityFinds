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
                  <div class="logged-out-buttons">
                      <a href="auth.html?mode=login" class="btn login-btn">Log In</a>
                      <a href="auth.html?mode=signup" class="btn signup-btn">Sign Up</a>
                  </div>
                  <!-- Button and indicator shown when logged in -->
                  <div class="logged-in-buttons" style="display: none;">
                      <span class="user-greeting">Hello, <span class="username">User</span>!</span>
                      <a href="#" class="btn logout-btn">Sign Out</a>
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
              
              <a href="restaurants.html" style="text-decoration: none; color: inherit;">
                  <div class="category-card">
                      <div class="category-icon" style="background-image: url('/api/placeholder/300/200')"></div>
                      <div class="category-name">Restaurants & Dining</div>
                  </div>
              </a>
              
              <div class="category-card">
                  <div class="category-icon" style="background-image: url('/api/placeholder/300/200')"></div>
                  <div class="category-name">Outdoor Activities</div>
              </div>
              
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
  
  <!-- Firebase SDK & Config -->
  <script type="module">
    // Import Firebase modules
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-app.js";
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-analytics.js";
    import { getAuth, signInWithEmailAndPassword, signOut, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.0/firebase-auth.js";

    // Your Firebase configuration object
    const firebaseConfig = {
  apiKey: "AIzaSyDItRW84PjUVkhrrKZwJS8fZJg6NwG_nXc",
  authDomain: "vanfinds-24006.firebaseapp.com",
  projectId: "vanfinds-24006",
  storageBucket: "vanfinds-24006.appspot.com", 
  messagingSenderId: "946012957250",
  appId: "1:946012957250:web:366f574d63b3bdd31cdbdb",
  measurementId: "G-XQ3MD366CB"
};

    // Initialize Firebase
    const app = initializeApp(firebaseConfig);
    const analytics = getAnalytics(app);
    const auth = getAuth(app);

    // Expose Firebase Auth functions globally for script.js to use
    window.firebaseAuth = auth;
    window.signInWithEmailAndPassword = signInWithEmailAndPassword;
    window.signOut = signOut;
    window.onAuthStateChanged = onAuthStateChanged;
  </script>
  
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
  </script>
</body>
</html>