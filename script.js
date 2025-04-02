// JavaScript for dynamic functionality

// Sample data for featured spots
const featuredSpots = [
  {
      name: "Revolver Coffee",
      category: "Café",
      description: "Specialty coffee shop with rotating selection of beans from around the world.",
      image: "/api/placeholder/400/300"
  },
  {
      name: "Lynn Canyon Park",
      category: "Outdoor",
      description: "Beautiful park with suspension bridge and natural swimming pools.",
      image: "/api/placeholder/400/300"
  },
  {
      name: "Nemesis Coffee",
      category: "Café",
      description: "Modern café serving artisanal coffee and house-made pastries.",
      image: "/api/placeholder/400/300"
  },
  {
      name: "The Acorn",
      category: "Restaurant",
      description: "Award-winning vegetarian restaurant with seasonal menu.",
      image: "/api/placeholder/400/300"
  },
  {
      name: "Kitsilano Beach",
      category: "Outdoor",
      description: "Popular beach with stunning views of downtown and mountains.",
      image: "/api/placeholder/400/300"
  },
  {
      name: "JJ Bean Coffee",
      category: "Café",
      description: "Local coffee roaster with multiple locations around Vancouver.",
      image: "/api/placeholder/400/300"
  }
];

// Sample data for categories
const categories = [
  {
      name: "Cafés & Coffee Shops",
      icon: "/api/placeholder/300/200"
  },
  {
      name: "Restaurants & Dining",
      icon: "/api/placeholder/300/200"
  },
  {
      name: "Outdoor Activities",
      icon: "/api/placeholder/300/200"
  },
  {
      name: "Arts & Culture",
      icon: "/api/placeholder/300/200"
  },
  {
      name: "Shopping & Markets",
      icon: "/api/placeholder/300/200"
  },
  {
      name: "Nightlife",
      icon: "/api/placeholder/300/200"
  }
];

// Function to populate featured spots
function populateSpots() {
  const spotsContainer = document.getElementById('spots-container');
  
  // Double the spots array to have more items for carousel
  const trendingSpots = [...featuredSpots, ...featuredSpots];
  
  trendingSpots.forEach(spot => {
      const spotCard = document.createElement('div');
      spotCard.className = 'spot-card';
      
      spotCard.innerHTML = `
          <div class="spot-image" style="background-image: url('${spot.image}')"></div>
          <div class="spot-content">
              <span class="spot-category">${spot.category}</span>
              <h3 class="spot-title">${spot.name}</h3>
              <p class="spot-description">${spot.description}</p>
              <button class="learn-more">Learn More</button>
          </div>
      `;
      
      spotsContainer.appendChild(spotCard);
  });
}

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

// Initialize everything when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
  // --- Firebase Authentication Integration ---
  // Make sure your HTML has a Sign In button with the id "auth-button"
  const authButton = document.getElementById('auth-button');
  if (authButton) {
    // Sign In function using Email/Password authentication
    function signIn() {
      const email = prompt("Enter your email:");
      const password = prompt("Enter your password:");
      if (email && password) {
        window.signInWithEmailAndPassword(window.firebaseAuth, email, password)
          .then(userCredential => {
            alert("Successfully signed in!");
            updateAuthUI(userCredential.user);
          })
          .catch(error => {
            alert("Error signing in: " + error.message);
          });
      }
    }
  
    // Sign Out function
    function signOutUser() {
      window.signOut(window.firebaseAuth)
        .then(() => {
          alert("You have been signed out.");
          updateAuthUI(null);
        })
        .catch(error => {
          alert("Error signing out: " + error.message);
        });
    }
  
    // Update the Sign In/Sign Out button based on auth state
    function updateAuthUI(user) {
      if (user) {
        authButton.textContent = "Sign Out";
        authButton.onclick = (e) => {
          e.preventDefault();
          signOutUser();
        };
      } else {
        authButton.textContent = "Sign In";
        authButton.onclick = (e) => {
          e.preventDefault();
          signIn();
        };
      }
    }
  
    // Listen for auth state changes and update UI accordingly
    window.onAuthStateChanged(window.firebaseAuth, (user) => {
      updateAuthUI(user);
    });
  }
  
  // --- End of Firebase Auth Integration ---
  
  // Content population and UI enhancements
  populateSpots();
  populateCategories();
  setupHeaderScroll();
  setupMobileNav();
  setupSmoothScroll();
  setupCarousel();
});
