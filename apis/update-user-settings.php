<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';
$db = new MysqliDb();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        throw new Exception('Please log in to update settings.');
    }

    if (!isset($_POST['type'])) {
        throw new Exception('Invalid request type.');
    }

    $userId = $_SESSION['userid'];
    $type = $_POST['type'];

    switch ($type) {
        case 'profile':
            updateProfile($db, $userId);
            break;
        case 'addresses':
            updateAddresses($db, $userId);
            break;
        case 'password':
            updatePassword($db, $userId);
            break;
        case 'preferences':
            updatePreferences($db, $userId);
            break;
        default:
            throw new Exception('Invalid update type.');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$db->disconnect();

function updateProfile($db, $userId) {
    global $response;
    
    $updateData = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
        'gender' => !empty($_POST['gender']) ? $_POST['gender'] : null,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $updated = $db->where('user_id', $userId)->update('user_profiles', $updateData);
    
    if ($updated) {
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully.';
    } else {
        throw new Exception('Failed to update profile.');
    }
}

function updateAddresses($db, $userId) {
    global $response;
    
    $updateData = [
        'billing_company' => $_POST['billing_company'] ?? '',
        'billing_address_line_1' => $_POST['billing_address_line_1'] ?? '',
        'billing_address_line_2' => $_POST['billing_address_line_2'] ?? '',
        'billing_city' => $_POST['billing_city'] ?? '',
        'billing_state' => $_POST['billing_state'] ?? '',
        'billing_postal_code' => $_POST['billing_postal_code'] ?? '',
        'billing_country' => $_POST['billing_country'] ?? 'Bangladesh',
        'billing_phone' => $_POST['billing_phone'] ?? '',
        'same_as_billing' => isset($_POST['same_as_billing']) ? 1 : 0,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // Only update shipping address if not same as billing
    if (!isset($_POST['same_as_billing'])) {
        $updateData['shipping_company'] = $_POST['shipping_company'] ?? '';
        $updateData['shipping_address_line_1'] = $_POST['shipping_address_line_1'] ?? '';
        $updateData['shipping_address_line_2'] = $_POST['shipping_address_line_2'] ?? '';
        $updateData['shipping_city'] = $_POST['shipping_city'] ?? '';
        $updateData['shipping_state'] = $_POST['shipping_state'] ?? '';
        $updateData['shipping_postal_code'] = $_POST['shipping_postal_code'] ?? '';
        $updateData['shipping_country'] = $_POST['shipping_country'] ?? 'Bangladesh';
        $updateData['shipping_phone'] = $_POST['shipping_phone'] ?? '';
    } else {
        // Copy billing to shipping
        $updateData['shipping_company'] = $updateData['billing_company'];
        $updateData['shipping_address_line_1'] = $updateData['billing_address_line_1'];
        $updateData['shipping_address_line_2'] = $updateData['billing_address_line_2'];
        $updateData['shipping_city'] = $updateData['billing_city'];
        $updateData['shipping_state'] = $updateData['billing_state'];
        $updateData['shipping_postal_code'] = $updateData['billing_postal_code'];
        $updateData['shipping_country'] = $updateData['billing_country'];
        $updateData['shipping_phone'] = $updateData['billing_phone'];
    }

    $updated = $db->where('user_id', $userId)->update('user_profiles', $updateData);
    
    if ($updated) {
        $response['success'] = true;
        $response['message'] = 'Addresses updated successfully.';
    } else {
        throw new Exception('Failed to update addresses.');
    }
}

function updatePassword($db, $userId) {
    global $response;
    
    if (empty($_POST['current_password']) || empty($_POST['new_password'])) {
        throw new Exception('All password fields are required.');
    }

    if (strlen($_POST['new_password']) < 6) {
        throw new Exception('New password must be at least 6 characters long.');
    }

    // Get current user password
    $user = $db->where('id', $userId)->getOne('users', 'password');
    if (!$user) {
        throw new Exception('User not found.');
    }

    // Verify current password
    if (!password_verify($_POST['current_password'], $user['password'])) {
        throw new Exception('Current password is incorrect.');
    }

    // Update password
    $newPasswordHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $updated = $db->where('id', $userId)->update('users', [
        'password' => $newPasswordHash,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($updated) {
        $response['success'] = true;
        $response['message'] = 'Password changed successfully.';
    } else {
        throw new Exception('Failed to update password.');
    }
}

function updatePreferences($db, $userId) {
    global $response;
    
    $updateData = [
        'newsletter_subscription' => isset($_POST['newsletter_subscription']) ? 1 : 0,
        'sms_notifications' => isset($_POST['sms_notifications']) ? 1 : 0,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $updated = $db->where('user_id', $userId)->update('user_profiles', $updateData);
    
    if ($updated) {
        $response['success'] = true;
        $response['message'] = 'Preferences updated successfully.';
    } else {
        throw new Exception('Failed to update preferences.');
    }
}
?>