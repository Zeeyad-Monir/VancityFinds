// Restaurant data for each cuisine type (placeholders)
const japaneseRestaurants = [
  {
      name: "Miku Vancouver",
      cuisine: "Japanese",
      description: "Upscale sushi restaurant known for its Aburi (flame-seared) sushi with waterfront views.",
      image: "/api/placeholder/400/300",
      rating: 4.7,
      priceRange: "$$$"
  },
  {
      name: "Kingyo Izakaya",
      cuisine: "Japanese",
      description: "Lively izakaya serving creative Japanese tapas and cocktails in a vibrant atmosphere.",
      image: "/api/placeholder/400/300",
      rating: 4.5,
      priceRange: "$$"
  },
  {
      name: "Sushi Mura",
      cuisine: "Japanese",
      description: "Casual sushi spot offering fresh, traditional rolls and sashimi at reasonable prices.",
      image: "/api/placeholder/400/300",
      rating: 4.3,
      priceRange: "$$"
  },
  {
      name: "Raisu",
      cuisine: "Japanese",
      description: "Modern Japanese restaurant specializing in beautifully presented bento boxes and seasonal dishes.",
      image: "/api/placeholder/400/300",
      rating: 4.6,
      priceRange: "$$$"
  },
  {
      name: "Sushi Bar Maumi",
      cuisine: "Japanese",
      description: "Intimate omakase experience with fish flown in from Japan, limited seating available.",
      image: "/api/placeholder/400/300",
      rating: 4.9,
      priceRange: "$$$$"
  },
  {
      name: "Tojo's Restaurant",
      cuisine: "Japanese",
      description: "Iconic Vancouver sushi restaurant run by Master Chef Hidekazu Tojo, inventor of the California Roll.",
      image: "/api/placeholder/400/300",
      rating: 4.8,
      priceRange: "$$$$"
  }
];

const mexicanRestaurants = [
  {
      name: "La Taqueria",
      cuisine: "Mexican",
      description: "Authentic taco shop offering traditional Mexican street tacos made with locally sourced ingredients.",
      image: "/api/placeholder/400/300",
      rating: 4.6,
      priceRange: "$$"
  },
  {
      name: "Sal y Limon",
      cuisine: "Mexican",
      description: "Casual counter-service spot serving traditional tacos, tortas, and quesadillas with homemade salsas.",
      image: "/api/placeholder/400/300",
      rating: 4.4,
      priceRange: "$"
  },
  {
      name: "La Mezcaleria",
      cuisine: "Mexican",
      description: "Modern Mexican restaurant featuring regional specialties and an extensive mezcal selection.",
      image: "/api/placeholder/400/300",
      rating: 4.5,
      priceRange: "$$$"
  },
  {
      name: "Tacofino",
      cuisine: "Mexican",
      description: "Baja-inspired Mexican fare with Pacific Northwest influences, known for their fish tacos.",
      image: "/api/placeholder/400/300",
      rating: 4.7,
      priceRange: "$$"
  },
  {
      name: "Las Margaritas",
      cuisine: "Mexican",
      description: "Long-standing Mexican eatery with colorful decor, serving classic dishes and strong margaritas.",
      image: "/api/placeholder/400/300",
      rating: 4.2,
      priceRange: "$$"
  },
  {
      name: "Chancho Tortilleria",
      cuisine: "Mexican",
      description: "Minimalist spot focusing on house-made tortillas and slow-cooked pork carnitas.",
      image: "/api/placeholder/400/300",
      rating: 4.8,
      priceRange: "$$"
  }
];

const italianRestaurants = [
  {
      name: "Ask For Luigi",
      cuisine: "Italian",
      description: "Intimate Italian restaurant specializing in fresh, handmade pasta in a casual setting.",
      image: "/api/placeholder/400/300",
      rating: 4.8,
      priceRange: "$$$"
  },
  {
      name: "Savio Volpe",
      cuisine: "Italian",
      description: "Rustic Italian osteria focusing on wood-fired cooking and house-made pastas.",
      image: "/api/placeholder/400/300",
      rating: 4.7,
      priceRange: "$$$"
  },
  {
      name: "Pizzeria Farina",
      cuisine: "Italian",
      description: "Simple, no-frills pizzeria serving thin-crust Neapolitan-style pizzas until sold out.",
      image: "/api/placeholder/400/300",
      rating: 4.5,
      priceRange: "$$"
  },
  {
      name: "CinCin Ristorante",
      cuisine: "Italian",
      description: "Upscale Italian dining with a wood-fired grill, extensive wine list, and elegant atmosphere.",
      image: "/api/placeholder/400/300",
      rating: 4.6,
      priceRange: "$$$$"
  },
  {
      name: "La Quercia",
      cuisine: "Italian",
      description: "Cozy neighborhood restaurant offering regional Italian cuisine with a focus on seasonal ingredients.",
      image: "/api/placeholder/400/300",
      rating: 4.7,
      priceRange: "$$$"
  },
  {
      name: "Autostrada Osteria",
      cuisine: "Italian",
      description: "Modern Italian eatery known for expertly crafted pasta dishes and approachable wine list.",
      image: "/api/placeholder/400/300",
      rating: 4.6,
      priceRange: "$$$"
  }
];

// Function to populate restaurant carousels
function populateRestaurantCarousel(containerID, restaurants) {
  const container = document.getElementById(containerID);
  
  restaurants.forEach(restaurant => {
      const card = document.createElement('div');
      card.className = 'restaurant-card';
      
      // Generate stars based on rating
      const fullStars = Math.floor(restaurant.rating);
      const hasHalfStar = restaurant.rating % 1 >= 0.5;
      let starsHTML = '';
      
      for (let i = 0; i < fullStars; i++) {
          starsHTML += '★';
      }
      
      if (hasHalfStar) {
          starsHTML += '½';
      }
      
      card.innerHTML = `
          <div class="spot-image" style="background-image: url('${restaurant.image}')"></div>
          <div class="spot-content">
              <span class="cuisine-tag">${restaurant.cuisine}</span>
              <h3 class="spot-title">${restaurant.name} <span class="price-range">${restaurant.priceRange}</span></h3>
              <div class="rating">
                  <span class="rating-stars">${starsHTML}</span>
                  <span>${restaurant.rating}</span>
              </div>
              <p class="spot-description">${restaurant.description}</p>
              <button class="learn-more">View Details</button>
          </div>
      `;
      
      container.appendChild(card);
  });
}

// Function to set up restaurant carousels
function setupRestaurantCarousels() {
  setupSingleCarousel('japanese-container', 'japanese-prev', 'japanese-next');
  setupSingleCarousel('mexican-container', 'mexican-prev', 'mexican-next');
  setupSingleCarousel('italian-container', 'italian-prev', 'italian-next');
}

function setupSingleCarousel(containerId, prevBtnId, nextBtnId) {
  const carousel = document.getElementById(containerId);
  const prevButton = document.getElementById(prevBtnId);
  const nextButton = document.getElementById(nextBtnId);
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

// Add event listeners for restaurant detail buttons
function setupDetailButtons() {
  document.addEventListener('click', function(event) {
      if (event.target && event.target.classList.contains('learn-more')) {
          const restaurantName = event.target.closest('.restaurant-card').querySelector('.spot-title').textContent.split(' ')[0];
          alert(`You clicked on ${restaurantName}. Detail page coming soon!`);
      }
  });
}

// Initialize everything when DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
  // Populate the restaurant carousels
  populateRestaurantCarousel('japanese-container', japaneseRestaurants);
  populateRestaurantCarousel('mexican-container', mexicanRestaurants);
  populateRestaurantCarousel('italian-container', italianRestaurants);
  
  // Set up carousel functionality
  setupRestaurantCarousels();
  
  // Set up detail buttons
  setupDetailButtons();
});