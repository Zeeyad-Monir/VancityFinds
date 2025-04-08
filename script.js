// JavaScript for dynamic functionality

// Sample data for categories
const categories = [
 
];

// Function to populate categories
function populateCategories() {
  const categoriesContainer = document.getElementById('categories-container');
  
  categories.forEach(category => {
      const categoryCard = document.createElement('div');
      categoryCard.className = 'category-card';
      
      categoryCard.innerHTML = `
          <div class="category-icon" style="background-image: url('${category.icon}')"></div>
          <div class="category-name">${category.name}</div>
      `;
      
      categoryCard.addEventListener('click', () => {
          // Filter spots by category (to be implemented)
          alert(`Filtering by ${category.name}`);
      });
      
      categoriesContainer.appendChild(categoryCard);
  });
}

// Enhanced mobile navigation setup
function setupMobileNav() {
  const header = document.querySelector('header');
  const hamburger = document.querySelector('.hamburger');
  const navMenu = document.querySelector('.nav-menu');
  
  // Create search toggle button for mobile
  const searchToggle = document.createElement('button');
  searchToggle.className = 'search-toggle';
  searchToggle.setAttribute('aria-label', 'Toggle search');
  searchToggle.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>';
  
  // Only add search toggle for mobile
  if (window.innerWidth <= 992) {
      document.querySelector('.header-container').appendChild(searchToggle);
  }
  
  const searchBar = document.querySelector('.search-bar');
  
  // Hamburger menu toggle
  hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navMenu.classList.toggle('active');
      
      // Close search when menu opens
      searchBar.classList.remove('active');
      header.classList.remove('search-active');
  });
  
  // Search toggle for mobile
  if (searchToggle) {
      searchToggle.addEventListener('click', () => {
          searchBar.classList.toggle('active');
          header.classList.toggle('search-active');
          
          // Close menu when search opens
          hamburger.classList.remove('active');
          navMenu.classList.remove('active');
      });
  }
  
  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
      if (!e.target.closest('.nav-menu') && 
          !e.target.closest('.hamburger') && 
          !e.target.closest('.search-toggle') &&
          !e.target.closest('.search-bar')) {
          
          navMenu.classList.remove('active');
          hamburger.classList.remove('active');
          searchBar.classList.remove('active');
          header.classList.remove('search-active');
      }
  });
  
  // Handle window resize
  window.addEventListener('resize', () => {
      if (window.innerWidth > 992) {
          navMenu.classList.remove('active');
          hamburger.classList.remove('active');
          searchBar.classList.remove('active');
          header.classList.remove('search-active');
          
          // Remove search toggle if exists
          const existingToggle = document.querySelector('.search-toggle');
          if (existingToggle) {
              existingToggle.remove();
          }
      } else {
          // Add search toggle if not exists
          if (!document.querySelector('.search-toggle')) {
              document.querySelector('.header-container').appendChild(searchToggle);
          }
      }
  });
}

// Set up header scroll effect
function setupHeaderScroll() {
  const header = document.querySelector('header');
  
  window.addEventListener('scroll', () => {
      if (window.scrollY > 10) {
          header.classList.add('scrolled');
      } else {
          header.classList.remove('scrolled');
      }
  });
}

// Carousel functionality
function setupCarousel() {
  const carousel = document.querySelector('.spots-carousel');
  const prevButton = document.querySelector('.prev-button');
  const nextButton = document.querySelector('.next-button');
  const cardWidth = 300 + 16; // Card width + gap
  
  // Next button click
  nextButton.addEventListener('click', () => {
      carousel.scrollBy({
          left: cardWidth * 3,
          behavior: 'smooth'
      });
  });
  
  // Previous button click
  prevButton.addEventListener('click', () => {
      carousel.scrollBy({
          left: -cardWidth * 3,
          behavior: 'smooth'
      });
  });
}

// Smooth scrolling for anchor links
function setupSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
          e.preventDefault();
          
          const targetId = this.getAttribute('href');
          if (targetId === '#') return;
          
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
              window.scrollTo({
                  top: targetElement.offsetTop - 60, // Account for fixed header
                  behavior: 'smooth'
              });
          }
      });
  });
}

// Authentication UI Management
function setupAuthUI() {
  // Get DOM elements
  const loggedOutButtons = document.querySelector('.logged-out-buttons');
  const loggedInButtons = document.querySelector('.logged-in-buttons');
  const usernameSpan = document.querySelector('.username');
  const logoutBtn = document.querySelector('.logout-btn');
  
  // Make sure the elements exist
  if (!loggedOutButtons || !loggedInButtons || !usernameSpan || !logoutBtn) {
    console.error("Auth UI elements not found in the DOM");
    return;
  }
  
  // Sign Out function
  function signOutUser() {
    window.signOut(window.firebaseAuth)
      .then(() => {
        showToast("You've been successfully signed out.", "success", "Goodbye!");
      })
      .catch(error => {
        showToast(error.message, "error", "Sign Out Error");
      });
  }
  
  // Update UI based on auth state
  function updateAuthUI(user) {
    if (user) {
      // User is signed in
      loggedOutButtons.style.display = 'none';
      loggedInButtons.style.display = 'flex';
      
      // Display user's name or email
      const displayName = user.displayName || user.email.split('@')[0];
      usernameSpan.textContent = displayName;
      
      // Add active class to header to show logged in state
      document.querySelector('header').classList.add('user-logged-in');
      
      // Show welcome toast if coming from auth success
      const urlParams = new URLSearchParams(window.location.search);
      const authParam = urlParams.get('auth');
      if (authParam === 'success') {
        showToast(`Welcome to Vancity Finds, ${displayName}!`, "success", "Successfully Logged In");
        // Clear the URL parameter after handling
        history.replaceState(null, null, window.location.pathname);
      }
    } else {
      // User is signed out
      loggedOutButtons.style.display = 'flex';
      loggedInButtons.style.display = 'none';
      document.querySelector('header').classList.remove('user-logged-in');
    }
  }
  
  // Set up sign out button
  logoutBtn.addEventListener('click', (e) => {
    e.preventDefault();
    signOutUser();
  });
  
  // Listen for auth state changes
  window.onAuthStateChanged(window.firebaseAuth, (user) => {
    updateAuthUI(user);
  });
}

// Initialize everything when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
  // Add toast container if it doesn't exist
  if (!document.getElementById('toast-container')) {
    const toastContainer = document.createElement('div');
    toastContainer.id = 'toast-container';
    toastContainer.className = 'toast-container';
    document.body.appendChild(toastContainer);
  }
  
  // Content population and UI enhancements
  if (typeof populateCategories === 'function') {
    populateCategories();
  }
  setupHeaderScroll();
  setupMobileNav();
  setupSmoothScroll();
  setupCarousel();
  
  // Set up auth UI if Firebase is loaded
  if (window.firebaseAuth) {
    setupAuthUI();
  } else {
    console.error("Firebase Auth not initialized");
  }
});