<?php
// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Load configuration
require_once __DIR__ . '/../config.php';

// Load database connection
require_once __DIR__ . '/../conn.php';

try {
    // Validate ID
    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid user ID.');
    }

    // Prepare and execute delete query
    $stmt = $conn->prepare('DELETE FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Check if deletion was successful
    if ($stmt->affected_rows === 1) {
        $_SESSION['message'] = ['type' => 'success', 'text' => "User (ID: {$id}) deleted successfully."];
    } else {
        throw new Exception('User not found or already deleted.');
    }

    $stmt->close();
} catch (Exception $e) {
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
} finally {
    $conn->close();
}

// Redirect to user list page
header('Location: ' . BASE_URL . 'user_select.php');
exit;
?>