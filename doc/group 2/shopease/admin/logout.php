<?php
// Initialize configuration
require_once __DIR__ . '/../config.php';

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
session_unset();

// Destroy the session
session_destroy();

// Prevent header tampering
header_remove();

// Redirect to root URL
header('Location: ' . ROOT_URL);
exit;
?>