<?php
// Authentication functions for MySQL-based authentication system

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
        
        // Assign default 'user' role
        $role_stmt = mysqli_prepare($connection, "INSERT INTO user_roles (user_id, role_id) 
                                                 SELECT ?, id FROM roles WHERE name = 'user'");
        mysqli_stmt_bind_param($role_stmt, "i", $user_id);
        mysqli_stmt_execute($role_stmt);
        mysqli_stmt_close($role_stmt);
        
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
 * @return array|null User information or null if not logged in
 */
function get_current_user() {
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
        "session_id" => $session_id,
        "expires_at" => $expires_at
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
