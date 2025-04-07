<?php
// Guest login handler for MySQL-based authentication

// Include authentication functions
require_once("auth_functions.php");

// Set content type to JSON
header('Content-Type: application/json');

// Create a guest session
$session = create_guest_session();

echo json_encode([
    'success' => true,
    'message' => 'Guest access granted',
    'session_id' => $session['session_id'],
    'is_guest' => true
]);
