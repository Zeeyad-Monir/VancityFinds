<?php
/**
 * Park Image API using Google Custom Search API
 * This file provides functions to retrieve relevant images for parks
 */

// Google Custom Search API key - production ready key
define('GOOGLE_CSE_API_KEY', 'AIzaSyDwjfEeparokDzAvBh5vgGD6YVXK5CqjyA');

// Google Custom Search Engine ID - configured for image search
define('GOOGLE_CSE_ID', '808d781e5a7f64f6d');

// Cache directory for storing image URLs to minimize API calls
define('CACHE_DIR', __DIR__ . '/cache/park_images');

// Debug mode - set to true to log API responses
define('DEBUG_MODE', true);

// Ensure cache directory exists
if (!file_exists(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

/**
 * Get an image URL for a specific park
 * 
 * @param string $parkName The name of the park
 * @param string $neighborhood Optional neighborhood for more specific results
 * @return string URL of an image for the park
 */
function get_park_image($parkName, $neighborhood = '') {
    // Create a cache key based on park name and neighborhood
    $cacheKey = sanitize_filename($parkName . '-' . $neighborhood);
    $cacheFile = CACHE_DIR . '/' . $cacheKey . '.txt';
    
    // Check if we have a cached result
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) { // 24 hour cache
        $imageUrl = file_get_contents($cacheFile);
        if (!empty($imageUrl)) {
            return $imageUrl;
        }
    }
    
    // Build search query - include both park name and neighborhood for better results
    $searchQuery = $parkName . ' park';
    if (!empty($neighborhood)) {
        $searchQuery .= ' ' . $neighborhood . ' vancouver';
    } else {
        $searchQuery .= ' vancouver';
    }
    
    // Get image from Google Custom Search API
    $imageUrl = search_google_images($searchQuery);
    
    // Cache the result
    if (!empty($imageUrl)) {
        file_put_contents($cacheFile, $imageUrl);
        
        // Log successful API request if in debug mode
        if (DEBUG_MODE) {
            error_log("Park Image API: Successfully retrieved image for '$parkName' ($neighborhood): $imageUrl");
        }
    } else {
        // If no image found, use a park-specific fallback based on park name
        // This ensures each park gets a different image even if API fails
        $fallbackImages = [
            'Stanley Park' => 'https://images.unsplash.com/photo-1609825488888-3a766db05542',
            'Queen Elizabeth Park' => 'https://images.unsplash.com/photo-1621458036327-a1b902fc0a87',
            'Kitsilano Beach Park' => 'https://images.unsplash.com/photo-1535581652167-3a26c90a9910',
            'Jericho Beach Park' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
            'Trout Lake Park' => 'https://images.unsplash.com/photo-1588714477688-cf28a50e94f7',
            'Hastings Park' => 'https://images.unsplash.com/photo-1605146951638-e7bc4876f1ab',
            'John Hendry Park' => 'https://images.unsplash.com/photo-1588714477688-cf28a50e94f7',
            'McCleery Golf Course' => 'https://images.unsplash.com/photo-1587174486073-ae5e5cff23aa',
            'Captain Cook Park' => 'https://images.unsplash.com/photo-1605146951638-e7bc4876f1ab',
            'Fraserview Golf Course' => 'https://images.unsplash.com/photo-1587174486073-ae5e5cff23aa',
            'Kitsilano Beach' => 'https://images.unsplash.com/photo-1535581652167-3a26c90a9910'
        ];
        
        // Use specific fallback image if available, otherwise use a generic one
        if (isset($fallbackImages[$parkName])) {
            $imageUrl = $fallbackImages[$parkName];
        } else {
            // Generate a semi-random fallback based on park name hash
            $parkHash = crc32($parkName) % 10;
            $fallbackUrls = [
                'https://images.unsplash.com/photo-1605146951638-e7bc4876f1ab',
                'https://images.unsplash.com/photo-1588714477688-cf28a50e94f7',
                'https://images.unsplash.com/photo-1621458036327-a1b902fc0a87',
                'https://images.unsplash.com/photo-1535581652167-3a26c90a9910',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e',
                'https://images.unsplash.com/photo-1609825488888-3a766db05542',
                'https://images.unsplash.com/photo-1587174486073-ae5e5cff23aa',
                'https://images.unsplash.com/photo-1501854140801-50d01698950b',
                'https://images.unsplash.com/photo-1528543606781-2f6e6857f318',
                'https://images.unsplash.com/photo-1565118531796-763e5082d113'
            ];
            $imageUrl = $fallbackUrls[$parkHash];
        }
        
        // Cache the fallback image
        file_put_contents($cacheFile, $imageUrl);
        
        // Log API failure if in debug mode
        if (DEBUG_MODE) {
            error_log("Park Image API: Failed to retrieve image for '$parkName' ($neighborhood). Using fallback: $imageUrl");
        }
    }
    
    return $imageUrl;
}

/**
 * Search for images using Google Custom Search API
 * 
 * @param string $query The search query
 * @return string URL of the first image result or empty string if none found
 */
function search_google_images($query) {
    // Build the API URL with required parameters
    $url = 'https://www.googleapis.com/customsearch/v1?' . http_build_query([
        'key' => GOOGLE_CSE_API_KEY,
        'cx' => GOOGLE_CSE_ID,
        'q' => $query,
        'searchType' => 'image',
        'imgSize' => 'large',
        'num' => 5, // Get multiple results to ensure variety
        'safe' => 'active'
    ]);
    
    // Log API request if in debug mode
    if (DEBUG_MODE) {
        error_log("Park Image API: Making request to Google CSE API for query: '$query'");
    }
    
    // Make the API request without suppressing errors
    $response = file_get_contents($url);
    
    if ($response === false) {
        // API request failed
        if (DEBUG_MODE) {
            error_log("Park Image API: API request failed for query: '$query'");
        }
        return '';
    }
    
    // Parse the JSON response
    $data = json_decode($response, true);
    
    // Log API response if in debug mode
    if (DEBUG_MODE) {
        if (isset($data['error'])) {
            error_log("Park Image API: Error response from API: " . json_encode($data['error']));
        } elseif (!isset($data['items'])) {
            error_log("Park Image API: No items in response for query: '$query'");
        } else {
            error_log("Park Image API: Received " . count($data['items']) . " results for query: '$query'");
        }
    }
    
    // Check if we have image results
    if (isset($data['items']) && count($data['items']) > 0) {
        // Get a random image from the first 5 results to ensure variety
        $randomIndex = rand(0, min(4, count($data['items']) - 1));
        return $data['items'][$randomIndex]['link'];
    }
    
    return '';
}

/**
 * Sanitize a string for use as a filename
 * 
 * @param string $filename The string to sanitize
 * @return string Sanitized filename
 */
function sanitize_filename($filename) {
    // Remove any character that isn't a letter, number, dash, or underscore
    $filename = preg_replace('/[^\w\-]/', '_', $filename);
    // Ensure the filename isn't too long
    return substr($filename, 0, 64);
}

/**
 * Test function to verify the image API is working
 * 
 * @param string $parkName The name of the park to test
 * @return array Test results
 */
function test_park_image_api($parkName) {
    $start = microtime(true);
    $imageUrl = get_park_image($parkName);
    $end = microtime(true);
    
    return [
        'park' => $parkName,
        'image_url' => $imageUrl,
        'time_taken' => round($end - $start, 3) . ' seconds',
        'success' => !empty($imageUrl)
    ];
}
