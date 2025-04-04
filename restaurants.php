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
                <li><a href="#" class="btn">Sign In</a></li>
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

    <script src="script.js"></script>
    <script src="restaurants.js"></script>
</body>
</html>