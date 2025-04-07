<?php
// Check authentication status
require_once("auth_functions.php");

// Get current user if logged in
$current_user = get_current_user();
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

// Set content type to JSON
header('Content-Type: application/json');
echo json_encode($response);
