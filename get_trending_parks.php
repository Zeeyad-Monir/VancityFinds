<?php
/**
 * Function to get trending parks based on review count
 * 
 * Sorting Logic:
 * 1. Display parks with the most reviews first
 * 2. If multiple parks have the same number of reviews, randomly select from those
 * 3. If there are fewer than 9 parks with reviews, randomly fill the remaining spots with parks that have no reviews
 * 
 * @param int $limit The number of parks to return (default: 9)
 * @return array Array of trending parks
 */
function get_trending_parks($limit = 9) {
    // Establish connection to the database
    require_once("./database/db_credentials.php");
    global $dbhost, $dbuser, $dbpass, $dbname;
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    
    // Check if the connection was successful
    if (mysqli_connect_errno()) {
        return array();
    }
    
    // Get parks with reviews, ordered by review count
    $parks_with_reviews_query = "
        SELECT p.*, COUNT(pr.id) as review_count 
        FROM parks p
        JOIN park_reviews pr ON p.ParkID = pr.park_id
        GROUP BY p.ParkID
        ORDER BY review_count DESC, RAND()
    ";
    
    $parks_with_reviews_stmt = mysqli_prepare($connection, $parks_with_reviews_query);
    mysqli_stmt_execute($parks_with_reviews_stmt);
    $parks_with_reviews_result = mysqli_stmt_get_result($parks_with_reviews_stmt);
    
    // Fetch parks with reviews
    $trending_parks = array();
    while ($park = mysqli_fetch_assoc($parks_with_reviews_result)) {
        $trending_parks[] = $park;
        
        // If we have enough parks, break the loop
        if (count($trending_parks) >= $limit) {
            break;
        }
    }
    
    mysqli_stmt_close($parks_with_reviews_stmt);
    
    // If we don't have enough parks with reviews, get random parks without reviews
    if (count($trending_parks) < $limit) {
        $remaining_count = $limit - count($trending_parks);
        
        // Get IDs of parks we already have
        $existing_park_ids = array_map(function($park) {
            return $park['ParkID'];
        }, $trending_parks);
        
        // Create placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($existing_park_ids), '?'));
        
        // If we have existing parks, exclude them from the query
        $parks_without_reviews_query = count($existing_park_ids) > 0 
            ? "
                SELECT p.* 
                FROM parks p
                LEFT JOIN park_reviews pr ON p.ParkID = pr.park_id
                WHERE pr.id IS NULL AND p.ParkID NOT IN ($placeholders)
                ORDER BY RAND()
                LIMIT ?
            "
            : "
                SELECT p.* 
                FROM parks p
                LEFT JOIN park_reviews pr ON p.ParkID = pr.park_id
                WHERE pr.id IS NULL
                ORDER BY RAND()
                LIMIT ?
            ";
        
        $parks_without_reviews_stmt = mysqli_prepare($connection, $parks_without_reviews_query);
        
        // Bind parameters
        if (count($existing_park_ids) > 0) {
            $types = str_repeat('s', count($existing_park_ids)) . 'i';
            $params = array_merge($existing_park_ids, array($remaining_count));
            
            // Create a reference array for bind_param
            $refs = array();
            $refs[0] = $types;
            for ($i = 0; $i < count($params); $i++) {
                $refs[$i + 1] = &$params[$i];
            }
            
            call_user_func_array(array($parks_without_reviews_stmt, 'bind_param'), $refs);
        } else {
            mysqli_stmt_bind_param($parks_without_reviews_stmt, 'i', $remaining_count);
        }
        
        mysqli_stmt_execute($parks_without_reviews_stmt);
        $parks_without_reviews_result = mysqli_stmt_get_result($parks_without_reviews_stmt);
        
        // Fetch parks without reviews and add them to the trending parks array
        while ($park = mysqli_fetch_assoc($parks_without_reviews_result)) {
            // Add review_count of 0 to match the structure of parks with reviews
            $park['review_count'] = 0;
            $trending_parks[] = $park;
        }
        
        mysqli_stmt_close($parks_without_reviews_stmt);
    }
    
    mysqli_close($connection);
    
    return $trending_parks;
}
