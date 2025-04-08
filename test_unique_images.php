<?php
// Test script for park image API - verifies unique images for different parks
require_once("park_image_api.php");

// Set content type to plain text
header('Content-Type: text/plain');

// Sample parks to test
$parks = [
    ['name' => 'Stanley Park', 'neighborhood' => 'West End'],
    ['name' => 'Queen Elizabeth Park', 'neighborhood' => 'Riley Park'],
    ['name' => 'Kitsilano Beach Park', 'neighborhood' => 'Kitsilano'],
    ['name' => 'Jericho Beach Park', 'neighborhood' => 'West Point Grey'],
    ['name' => 'Trout Lake Park', 'neighborhood' => 'Grandview-Woodland'],
    ['name' => 'Hastings Park', 'neighborhood' => 'Hastings-Sunrise'],
    ['name' => 'John Hendry Park', 'neighborhood' => 'Kensington-Cedar Cottage'],
    ['name' => 'McCleery Golf Course', 'neighborhood' => 'Kerrisdale'],
    ['name' => 'Captain Cook Park', 'neighborhood' => 'Killarney'],
    ['name' => 'Fraserview Golf Course', 'neighborhood' => 'Killarney']
];

echo "PARK IMAGE API TEST - VERIFYING UNIQUE IMAGES\n";
echo "===========================================\n\n";

// Test results
$results = [];
$imageUrls = [];

// Test each park
foreach ($parks as $park) {
    $imageUrl = get_park_image($park['name'], $park['neighborhood']);
    $results[] = [
        'park' => $park['name'],
        'neighborhood' => $park['neighborhood'],
        'image_url' => $imageUrl
    ];
    $imageUrls[] = $imageUrl;
    
    echo "Park: " . $park['name'] . "\n";
    echo "Neighborhood: " . $park['neighborhood'] . "\n";
    echo "Image URL: " . $imageUrl . "\n";
    echo "-------------------------------------------\n";
}

// Check for uniqueness
$uniqueUrls = array_unique($imageUrls);
$uniqueCount = count($uniqueUrls);
$totalCount = count($imageUrls);

echo "\nUNIQUENESS ANALYSIS\n";
echo "===================\n";
echo "Total parks tested: $totalCount\n";
echo "Unique images found: $uniqueCount\n";
echo "Uniqueness percentage: " . round(($uniqueCount / $totalCount) * 100) . "%\n";

if ($uniqueCount == $totalCount) {
    echo "\nSUCCESS: All parks have unique images!\n";
} else {
    echo "\nWARNING: Some parks have duplicate images.\n";
    
    // Find duplicates
    $counts = array_count_values($imageUrls);
    echo "\nDuplicate images:\n";
    foreach ($counts as $url => $count) {
        if ($count > 1) {
            echo "- Image used $count times: $url\n";
            echo "  Used by parks: ";
            $parkNames = [];
            foreach ($results as $result) {
                if ($result['image_url'] == $url) {
                    $parkNames[] = $result['park'];
                }
            }
            echo implode(", ", $parkNames) . "\n\n";
        }
    }
}

// Check cache directory
echo "\nCACHE DIRECTORY ANALYSIS\n";
echo "=======================\n";
$cacheFiles = glob(CACHE_DIR . '/*.txt');
echo "Total cache files: " . count($cacheFiles) . "\n";

if (count($cacheFiles) > 0) {
    echo "\nCache files:\n";
    foreach ($cacheFiles as $file) {
        $filename = basename($file);
        $content = file_get_contents($file);
        echo "- $filename: " . substr($content, 0, 50) . "...\n";
    }
}
