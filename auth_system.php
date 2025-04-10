<?php


// Include database credentials
require_once("db_credentials.php");

/**
 * Establish database connection
 * @return mysqli Database connection object
 */
function get_db_connection() {
    global $dbhost, $dbuser, $dbpass, $dbname;
    
    $connection = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    
    if (mysqli_connect_errno()) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    
    return $connection;
}

/**
 * Get current user information
 * @return array|null User information or null if not logged in
 */
function get_current_user_app() {
    if (isset($_SESSION['user_id'])) {
        $connection = get_db_connection();
        
        $stmt = mysqli_prepare($connection, "SELECT id, email, display_name FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            mysqli_close($connection);
            return $user;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
    }
    
    return null;
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is a guest
 * @return bool True if user is a guest, false otherwise
 */
function is_guest() {
    return isset($_SESSION['is_guest']) && $_SESSION['is_guest'] === true;
}

/**
 * Register a new user
 * @param string $email User email
 * @param string $password User password (plain text)
 * @param string $display_name User display name (optional)
 * @return array Result with status and message
 */
function register_user($email, $password, $display_name = null) {
    $connection = get_db_connection();
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Invalid email format"];
    }
    
    // Check if email already exists
    $stmt = mysqli_prepare($connection, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return ["success" => false, "message" => "Email already in use"];
    }
    
    mysqli_stmt_close($stmt);
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = mysqli_prepare($connection, "INSERT INTO users (email, password, display_name) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $email, $hashed_password, $display_name);
    
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        $user_id = mysqli_insert_id($connection);
        
        // Assign default 'user' role
        $role_stmt = mysqli_prepare($connection, "INSERT INTO user_roles (user_id, role_id) 
                                               SELECT ?, id FROM roles WHERE name = 'user'");
        mysqli_stmt_bind_param($role_stmt, "i", $user_id);
        mysqli_stmt_execute($role_stmt);
        mysqli_stmt_close($role_stmt);
        
        // Create session for the new user
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['display_name'] = $display_name;
        
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        
        return [
            "success" => true, 
            "message" => "Registration successful", 
            "user_id" => $user_id
        ];
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return ["success" => false, "message" => "Registration failed: " . mysqli_error($connection)];
    }
}

/**
 * Login a user
 * @param string $email User email
 * @param string $password User password (plain text)
 * @return array Result with status and message
 */
function login_user($email, $password) {
    $connection = get_db_connection();
    
    // Check if user exists
    $stmt = mysqli_prepare($connection, "SELECT id, email, password, display_name FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['display_name'] = $user['display_name'];
            
            // Update last login time
            $update_stmt = mysqli_prepare($connection, "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
            
            mysqli_stmt_close($stmt);
            mysqli_close($connection);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'display_name' => $user['display_name']
                ]
            ];
        } else {
            mysqli_stmt_close($stmt);
            mysqli_close($connection);
            return [
                'success' => false,
                'message' => 'Invalid email or password'
            ];
        }
    } else {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return [
            'success' => false,
            'message' => 'Invalid email or password'
        ];
    }
}

/**
 * Create a guest session
 * @return array Guest session information
 */
function create_guest_session() {
    $_SESSION['is_guest'] = true;
    
    return [
        "success" => true,
        "message" => "Guest access granted",
        "is_guest" => true
    ];
}

/**
 * End user session (logout)
 * @return bool True if successful, false otherwise
 */
function end_session() {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    return true;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Alias function to support calls to get_current_user()
 * (Avoids conflicting with PHP's built-in get_current_user())
 */
if (!function_exists('get_current_user')) {
  function get_current_user() {
      return get_current_user_app();
  }
}

// Route requests based on action parameter
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'register':
            // Set content type to JSON
            header('Content-Type: application/json');
            
            // Check if request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request method'
                ]);
                exit;
            }
            
            // Get POST data
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $password2 = isset($_POST['password2']) ? $_POST['password2'] : '';
            $display_name = isset($_POST['display_name']) ? trim($_POST['display_name']) : null;
            
            // Validate input
            if (empty($email) || empty($password) || empty($password2)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Please fill in all required fields'
                ]);
                exit;
            }
            
            // Check if passwords match
            if ($password !== $password2) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Passwords do not match'
                ]);
                exit;
            }
            
            // Validate password strength
            if (strlen($password) < 8) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Password must be at least 8 characters long'
                ]);
                exit;
            }
            
            // If display name is not provided, extract it from email
            if (empty($display_name)) {
                $display_name = explode('@', $email)[0];
            }
            
            // Register user
            $result = register_user($email, $password, $display_name);
            
            // Return result
            echo json_encode($result);
            exit;
            break;
            
        case 'login':
            // Set content type to JSON
            header('Content-Type: application/json');
            
            // Check if request method is POST
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid request method'
                ]);
                exit;
            }
            
            // Get POST data
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            
            // Validate input
            if (empty($email) || empty($password)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Please fill in all required fields'
                ]);
                exit;
            }
            
            // Login user
            $result = login_user($email, $password);
            
            // Return result
            echo json_encode($result);
            exit;
            break;
            
        case 'guest':
            // Set content type to JSON
            header('Content-Type: application/json');
            
            // Create a guest session
            $result = create_guest_session();
            
            // Return result
            echo json_encode($result);
            exit;
            break;
            
        case 'logout':
            // Set content type to JSON
            header('Content-Type: application/json');
            
            // End user session
            $result = end_session();
            
            echo json_encode([
                'success' => true,
                'message' => 'Logout successful'
            ]);
            exit;
            break;
            
        case 'check':
            // Set content type to JSON
            header('Content-Type: application/json');
            
            // Get current user if logged in
            $current_user = get_current_user_app();
            $is_logged_in = ($current_user !== null);
            $is_guest_user = is_guest();
            
            // Prepare response
            $response = [
                'is_authenticated' => $is_logged_in,
                'is_guest' => $is_guest_user
            ];
            
            // Add user info if logged in
            if ($is_logged_in) {
                $response['user'] = [
                    'id' => $current_user['id'],
                    'email' => $current_user['email'],
                    'display_name' => $current_user['display_name']
                ];
            }
            
            echo json_encode($response);
            exit;
            break;
    }
}
