<?php
// Logout handler for MySQL-based authentication

// Include authentication functions
require_once("auth_functions.php");

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
