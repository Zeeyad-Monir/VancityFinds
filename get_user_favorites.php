<?php
/**
 * Function to get a limited number of user's favorite parks
 * 
 * @param int $user_id The ID of the user
 * @param int $limit The maximum number of favorites to return (default: 3)
 * @return array Array of favorite parks
 */
function get_user_favorite_parks($user_id, $limit = 3) {
    // Establish connection to the database
    require_once("./database/db_credentials.php");
    global $dbhost, $dbuser, $dbpass, $dbname;
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    
    // Check if the connection was successful
    if (mysqli_connect_errno()) {
        return array();
    }
    
    // Query to get user's favorite parks with limit
    $favorites_query = "SELECT p.* FROM parks p 
                        JOIN user_favorites uf ON p.ParkID = uf.park_id 
                        WHERE uf.user_id = ?
                        ORDER BY uf.created_at DESC
                        LIMIT ?";
    
    $favorites_stmt = mysqli_prepare($connection, $favorites_query);
    mysqli_stmt_bind_param($favorites_stmt, "ii", $user_id, $limit);
    mysqli_stmt_execute($favorites_stmt);
    $favorites_result = mysqli_stmt_get_result($favorites_stmt);
    
    // Fetch all favorite parks
    $favorite_parks = array();
    while ($park = mysqli_fetch_assoc($favorites_result)) {
        $favorite_parks[] = $park;
    }
    
    mysqli_stmt_close($favorites_stmt);
    mysqli_close($connection);
    
    return $favorite_parks;
}
