<?php
// Include authentication system
require_once("auth_system.php");

// Get current user if logged in
$current_user = get_current_user_app();
$is_logged_in = ($current_user !== null);

// Check if user is logged in
if (!$is_logged_in) {
    // Return error if not logged in
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to favorite parks'
    ]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get park ID from POST data
$park_id = isset($_POST['park_id']) ? intval($_POST['park_id']) : 0;

// Validate park ID
if ($park_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid park ID'
    ]);
    exit;
}

// Establish connection to the database
require_once("db_credentials.php");
$connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check if the connection was successful
if (mysqli_connect_errno()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

// Get user ID
$user_id = $current_user['id'];

// Check if park exists
$check_park_query = "SELECT ParkID FROM parks WHERE ParkID = ?";
$check_park_stmt = mysqli_prepare($connection, $check_park_query);
mysqli_stmt_bind_param($check_park_stmt, "i", $park_id);
mysqli_stmt_execute($check_park_stmt);
mysqli_stmt_store_result($check_park_stmt);

if (mysqli_stmt_num_rows($check_park_stmt) === 0) {
    mysqli_stmt_close($check_park_stmt);
    mysqli_close($connection);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Park not found'
    ]);
    exit;
}

mysqli_stmt_close($check_park_stmt);

// Check if park is already in favorites
$check_favorite_query = "SELECT id FROM user_favorites WHERE user_id = ? AND park_id = ?";
$check_favorite_stmt = mysqli_prepare($connection, $check_favorite_query);
mysqli_stmt_bind_param($check_favorite_stmt, "ii", $user_id, $park_id);
mysqli_stmt_execute($check_favorite_stmt);
mysqli_stmt_store_result($check_favorite_stmt);

$is_favorite = (mysqli_stmt_num_rows($check_favorite_stmt) > 0);
mysqli_stmt_close($check_favorite_stmt);

if ($is_favorite) {
    // Remove from favorites
    $remove_query = "DELETE FROM user_favorites WHERE user_id = ? AND park_id = ?";
    $remove_stmt = mysqli_prepare($connection, $remove_query);
    mysqli_stmt_bind_param($remove_stmt, "ii", $user_id, $park_id);
    $result = mysqli_stmt_execute($remove_stmt);
    
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'is_favorite' => false,
            'message' => 'Park removed from favorites'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove park from favorites: ' . mysqli_error($connection)
        ]);
    }
    
    mysqli_stmt_close($remove_stmt);
} else {
    // Add to favorites
    $add_query = "INSERT INTO user_favorites (user_id, park_id) VALUES (?, ?)";
    $add_stmt = mysqli_prepare($connection, $add_query);
    mysqli_stmt_bind_param($add_stmt, "ii", $user_id, $park_id);
    $result = mysqli_stmt_execute($add_stmt);
    
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'is_favorite' => true,
            'message' => 'Park added to favorites'
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to add park to favorites: ' . mysqli_error($connection)
        ]);
    }
    
    mysqli_stmt_close($add_stmt);
}

mysqli_close($connection);
?>
