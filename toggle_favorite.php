<?php
// Include authentication system
require_once("auth_system.php");

// Get current user if logged in
$current_user = get_current_user_app();
$is_logged_in = ($current_user !== null);

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'is_favorite' => false
);

// Check if user is logged in
if (!$is_logged_in) {
    $response['message'] = 'You must be logged in to manage favorites';
    echo json_encode($response);
    exit;
}

// Check if park_id is provided
if (!isset($_POST['park_id']) || empty($_POST['park_id'])) {
    $response['message'] = 'Park ID is required';
    echo json_encode($response);
    exit;
}

// Get user ID and park ID
$user_id = $current_user['id'];
$park_id = $_POST['park_id'];

// Establish connection to the database
require_once("./database/db_credentials.php");
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check if the connection was successful
if (mysqli_connect_errno()) {
    $response['message'] = 'Database connection error: ' . mysqli_connect_error();
    echo json_encode($response);
    exit;
}

// Check if the park is already in favorites
$check_query = "SELECT * FROM user_favorites WHERE user_id = ? AND park_id = ?";
$check_stmt = mysqli_prepare($connection, $check_query);
mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $park_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

// If park is already in favorites, remove it
if (mysqli_num_rows($check_result) > 0) {
    $delete_query = "DELETE FROM user_favorites WHERE user_id = ? AND park_id = ?";
    $delete_stmt = mysqli_prepare($connection, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "ii", $user_id, $park_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $response['success'] = true;
        $response['message'] = 'Park removed from favorites';
        $response['is_favorite'] = false;
    } else {
        $response['message'] = 'Error removing park from favorites: ' . mysqli_error($connection);
    }
    
    mysqli_stmt_close($delete_stmt);
} 
// If park is not in favorites, add it
else {
    $insert_query = "INSERT INTO user_favorites (user_id, park_id, created_at) VALUES (?, ?, NOW())";
    $insert_stmt = mysqli_prepare($connection, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "ii", $user_id, $park_id);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        $response['success'] = true;
        $response['message'] = 'Park added to favorites';
        $response['is_favorite'] = true;
    } else {
        $response['message'] = 'Error adding park to favorites: ' . mysqli_error($connection);
    }
    
    mysqli_stmt_close($insert_stmt);
}

mysqli_stmt_close($check_stmt);
mysqli_close($connection);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
