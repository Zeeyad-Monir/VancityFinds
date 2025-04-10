<?php
// Include database credentials
require_once("./database/db_credentials.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function is_user_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function get_current_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    
    // Check connection
    if (mysqli_connect_errno()) {
        echo json_encode(['success' => false, 'message' => 'Failed to connect to MySQL: ' . mysqli_connect_error()]);
        exit();
    }
    
    $action = $_POST['action'];
    
    // Submit a new review
    if ($action === 'submit_review') {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a review.']);
            exit();
        }
        
        // Get form data
        $park_id = isset($_POST['park_id']) ? intval($_POST['park_id']) : 0;
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
        $user_id = get_current_user_id();
        
        // Validate data
        if ($park_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid park ID.']);
            exit();
        }
        
        if ($rating < 1 || $rating > 5) {
            echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5.']);
            exit();
        }
        
        if (empty($comment)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a comment.']);
            exit();
        }
        
        // Insert review into database
        $stmt = mysqli_prepare($connection, "INSERT INTO park_reviews (park_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiis", $park_id, $user_id, $rating, $comment);
        
        if (mysqli_stmt_execute($stmt)) {
            $review_id = mysqli_insert_id($connection);
            
            // Get the newly created review with user information
            $review_query = "SELECT r.*, u.display_name, u.email 
                            FROM park_reviews r 
                            JOIN users u ON r.user_id = u.id 
                            WHERE r.id = ?";
            
            $review_stmt = mysqli_prepare($connection, $review_query);
            mysqli_stmt_bind_param($review_stmt, "i", $review_id);
            mysqli_stmt_execute($review_stmt);
            $review_result = mysqli_stmt_get_result($review_stmt);
            $review = mysqli_fetch_assoc($review_result);
            
            // Format the date
            $review['formatted_date'] = date('F j, Y', strtotime($review['created_at']));
            
            // Add a flag to indicate if this review belongs to the current user
            $review['is_own_review'] = true;
            
            echo json_encode(['success' => true, 'message' => 'Review submitted successfully.', 'review' => $review]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review: ' . mysqli_error($connection)]);
        }
        
        mysqli_stmt_close($stmt);
        exit();
    }
    
    // Delete a review
    if ($action === 'delete_review') {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a review.']);
            exit();
        }
        
        $review_id = isset($_POST['review_id']) ? intval($_POST['review_id']) : 0;
        $user_id = get_current_user_id();
        
        // Validate data
        if ($review_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid review ID.']);
            exit();
        }
        
        // Check if the review belongs to the current user
        $check_stmt = mysqli_prepare($connection, "SELECT id FROM park_reviews WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($check_stmt, "ii", $review_id, $user_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) === 0) {
            echo json_encode(['success' => false, 'message' => 'You can only delete your own reviews.']);
            mysqli_stmt_close($check_stmt);
            exit();
        }
        
        mysqli_stmt_close($check_stmt);
        
        // Delete the review
        $delete_stmt = mysqli_prepare($connection, "DELETE FROM park_reviews WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($delete_stmt, "ii", $review_id, $user_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            echo json_encode(['success' => true, 'message' => 'Review deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete review: ' . mysqli_error($connection)]);
        }
        
        mysqli_stmt_close($delete_stmt);
        exit();
    }
    
    // Get reviews for a park
    if ($action === 'get_reviews') {
        $park_id = isset($_POST['park_id']) ? intval($_POST['park_id']) : 0;
        $user_id = get_current_user_id();
        
        // Validate data
        if ($park_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid park ID.']);
            exit();
        }
        
        // Get all reviews for the park
        $reviews_query = "SELECT r.*, u.display_name, u.email 
                         FROM park_reviews r 
                         JOIN users u ON r.user_id = u.id 
                         WHERE r.park_id = ? 
                         ORDER BY r.created_at DESC";
        
        $reviews_stmt = mysqli_prepare($connection, $reviews_query);
        mysqli_stmt_bind_param($reviews_stmt, "i", $park_id);
        mysqli_stmt_execute($reviews_stmt);
        $reviews_result = mysqli_stmt_get_result($reviews_stmt);
        
        $reviews = [];
        while ($review = mysqli_fetch_assoc($reviews_result)) {
            // Format the date
            $review['formatted_date'] = date('F j, Y', strtotime($review['created_at']));
            
            // Add a flag to indicate if this review belongs to the current user
            $review['is_own_review'] = ($user_id && $review['user_id'] == $user_id);
            
            $reviews[] = $review;
        }
        
        echo json_encode(['success' => true, 'reviews' => $reviews]);
        mysqli_stmt_close($reviews_stmt);
        exit();
    }
    
    // Close the connection
    mysqli_close($connection);
}

// If no action is specified or not a POST request, return an error
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
exit();
?>
