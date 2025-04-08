<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * Consolidated Authentication System
 * 
 * This file combines all authentication functionality into a single file:
 * - Database connection
 * - User registration
 * - User login
 * - Guest access
 * - Session management
 * - Authentication status checking
 * - Logout functionality
 */

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
        
        // // Assign default 'user' role
        // $role_stmt = mysqli_prepare($connection, "INSERT INTO user_roles (user_id, role_id) 
        //                                        SELECT ?, id FROM roles WHERE name = 'user'");
        // mysqli_stmt_bind_param($role_stmt, "i", $user_id);
        // mysqli_stmt_execute($role_stmt);
        // mysqli_stmt_close($role_stmt);
        
        // Create session for the new user
        $session = create_session($user_id);
        
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        
        return [
            "success" => true, 
            "message" => "Registration successful", 
            "user_id" => $user_id,
            "session_id" => $session["session_id"]
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
            $session = create_session($user['id']);
            
            mysqli_stmt_close($stmt);
            mysqli_close($connection);
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'display_name' => $user['display_name']
                ],
                'session_id' => $session['session_id']
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
 * Create a new session for a user
 * @param int $user_id User ID
 * @return array Session information
 */
function create_session($user_id) {
    $connection = get_db_connection();
    
    // Generate a secure random session ID
    $session_id = bin2hex(random_bytes(64));
    
    // Get IP address and user agent
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // Set expiration time (30 days from now)
    $expires_at = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));
    
    // Insert session into database
    $stmt = mysqli_prepare($connection, "INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at) 
                                        VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sisss", $session_id, $user_id, $ip_address, $user_agent, $expires_at);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    // Update last login time for user
    $update_stmt = mysqli_prepare($connection, "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "i", $user_id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
    
    mysqli_close($connection);
    
    // Set session cookie
    setcookie('session_id', $session_id, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    
    return [
        "session_id" => $session_id,
        "expires_at" => $expires_at
    ];
}

/**
 * Get user information from session
 * @param string $session_id Session ID
 * @return array|null User information or null if session is invalid
 */
function get_user_from_session($session_id = null) {
    if ($session_id === null) {
        if (isset($_COOKIE['session_id'])) {
            $session_id = $_COOKIE['session_id'];
        } else {
            return null;
        }
    }
    
    $connection = get_db_connection();
    
    // Get user information from session
    $stmt = mysqli_prepare($connection, 
        "SELECT u.id, u.email, u.display_name, s.expires_at 
         FROM sessions s
         JOIN users u ON s.user_id = u.id
         WHERE s.id = ? AND s.expires_at > NOW()");
    
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        mysqli_close($connection);
        return $row;
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    return null;
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return get_user_from_session() !== null;
}

/**
 * Get current user information
 * (Renamed from get_current_user to avoid conflicts with PHP's built-in function.)
 * @return array|null User information or null if not logged in
 */
function get_current_user_app() {
    return get_user_from_session();
}

/**
 * Create a guest session
 * @return array Guest session information
 */
function create_guest_session() {
    // Generate a secure random session ID
    $session_id = 'guest_' . bin2hex(random_bytes(32));
    
    // Set expiration time (1 day from now)
    $expires_at = date('Y-m-d H:i:s', time() + (24 * 60 * 60));
    
    // Set session cookie
    setcookie('guest_session', $session_id, time() + (24 * 60 * 60), '/', '', false, true);
    
    return [
        "success" => true,
        "message" => "Guest access granted",
        "session_id" => $session_id,
        "is_guest" => true
    ];
}

/**
 * Check if user is a guest
 * @return bool True if user is a guest, false otherwise
 */
function is_guest() {
    return !is_logged_in() && isset($_COOKIE['guest_session']);
}

/**
 * End user session (logout)
 * @param string $session_id Session ID (optional, uses current session if not provided)
 * @return bool True if successful, false otherwise
 */
function end_session($session_id = null) {
    if ($session_id === null) {
        if (isset($_COOKIE['session_id'])) {
            $session_id = $_COOKIE['session_id'];
        } else {
            return false;
        }
    }
    
    $connection = get_db_connection();
    
    // Delete session from database
    $stmt = mysqli_prepare($connection, "DELETE FROM sessions WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "s", $session_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($connection);
    
    // Clear session cookie
    setcookie('session_id', '', time() - 3600, '/', '', false, true);
    
    return $result;
}

/**
 * End guest session
 * @return bool True if successful, false otherwise
 */
function end_guest_session() {
    // Clear guest session cookie
    setcookie('guest_session', '', time() - 3600, '/', '', false, true);
    return true;
}

/**
 * Handle registration request
 */
function handle_registration() {
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
}

/**
 * Handle login request
 */
function handle_login() {
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
}

/**
 * Handle guest login request
 */
function handle_guest_login() {
    // Set content type to JSON
    header('Content-Type: application/json');
    
    // Create a guest session
    $result = create_guest_session();
    
    // Return result
    echo json_encode($result);
    exit;
}

/**
 * Handle logout request
 */
function handle_logout() {
    // Set content type to JSON
    header('Content-Type: application/json');
    
    // End user session
    $result = end_session();
    
    // End guest session if it exists
    if (isset($_COOKIE['guest_session'])) {
        end_guest_session();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
    exit;
}

/**
 * Handle check authentication request
 */
function handle_check_auth() {
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

/* Route requests based on action parameter */
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'register':
            handle_registration();
            break;
        case 'login':
            handle_login();
            break;
        case 'guest':
            handle_guest_login();
            break;
        case 'logout':
            handle_logout();
            break;
        case 'check':
            handle_check_auth();
            break;
        default:
            // Invalid action
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            exit;
    }
}