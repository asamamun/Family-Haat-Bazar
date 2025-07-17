<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['message'] = "You must log in to access user settings.";
    header('Location: login.php');
    exit;
}

require __DIR__ . '/vendor/autoload.php';
$db = new MysqliDb();
$page = "User Settings";

$userId = $_SESSION['userid'];

// Get user information
$user = $db->where('id', $userId)->getOne('users');
if (!$user) {
    $_SESSION['message'] = "User not found.";
    header('Location: logout.php');
    exit;
}

// Get or create user profile
$profile = $db->where('user_id', $userId)->getOne('user_profiles');
if (!$profile) {
    // Create default profile
    $defaultProfile = [
        'user_id' => $userId,
        'first_name' => '',
        'last_name' => '',
        'phone' => '',
        'billing_country' => 'Bangladesh',
        'shipping_country' => 'Bangladesh',
        'same_as_billing' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    $db->insert('user_profiles', $defaultProfile);
    $profile = $db->where('user_id', $userId)->getOne('user_profiles');
}
?>

<?php require __DIR__ . '/components/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-user-cog me-2"></i>User Settings</h2>
            
            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                        <i class="fas fa-user me-2"></i>Profile Information
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button" role="tab">
                        <i class="fas fa-map-marker-alt me-2"></i>Addresses
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                        <i class="fas fa-cog me-2"></i>Preferences
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content mt-4" id="settingsTabContent">
                
                <!-- Profile Information Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <form id="profileForm">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($profile['first_name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($profile['last_name'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                        <small class="text-muted">Email cannot be changed</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?= $profile['date_of_birth'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-select" id="gender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" <?= ($profile['gender'] ?? '') == 'male' ? 'selected' : '' ?>>Male</option>
                                            <option value="female" <?= ($profile['gender'] ?? '') == 'female' ? 'selected' : '' ?>>Female</option>
                                            <option value="other" <?= ($profile['gender'] ?? '') == 'other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Profile
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Addresses Tab -->
                <div class="tab-pane fade" id="addresses" role="tabpanel">
                    <form id="addressForm">
                        <!-- Billing Address -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Billing Address</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="billing_company" class="form-label">Company (Optional)</label>
                                    <input type="text" class="form-control" id="billing_company" name="billing_company" value="<?= htmlspecialchars($profile['billing_company'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="billing_address_line_1" class="form-label">Address Line 1</label>
                                    <input type="text" class="form-control" id="billing_address_line_1" name="billing_address_line_1" value="<?= htmlspecialchars($profile['billing_address_line_1'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="billing_address_line_2" class="form-label">Address Line 2 (Optional)</label>
                                    <input type="text" class="form-control" id="billing_address_line_2" name="billing_address_line_2" value="<?= htmlspecialchars($profile['billing_address_line_2'] ?? '') ?>">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="billing_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="billing_city" name="billing_city" value="<?= htmlspecialchars($profile['billing_city'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="billing_state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="billing_state" name="billing_state" value="<?= htmlspecialchars($profile['billing_state'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="billing_postal_code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="billing_postal_code" name="billing_postal_code" value="<?= htmlspecialchars($profile['billing_postal_code'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="billing_country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="billing_country" name="billing_country" value="<?= htmlspecialchars($profile['billing_country'] ?? 'Bangladesh') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="billing_phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="billing_phone" name="billing_phone" value="<?= htmlspecialchars($profile['billing_phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Shipping Address</h5>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="same_as_billing" name="same_as_billing" <?= ($profile['same_as_billing'] ?? 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="same_as_billing">Same as Billing Address</label>
                                </div>
                            </div>
                            <div class="card-body" id="shippingAddressFields">
                                <div class="mb-3">
                                    <label for="shipping_company" class="form-label">Company (Optional)</label>
                                    <input type="text" class="form-control" id="shipping_company" name="shipping_company" value="<?= htmlspecialchars($profile['shipping_company'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="shipping_address_line_1" class="form-label">Address Line 1</label>
                                    <input type="text" class="form-control" id="shipping_address_line_1" name="shipping_address_line_1" value="<?= htmlspecialchars($profile['shipping_address_line_1'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="shipping_address_line_2" class="form-label">Address Line 2 (Optional)</label>
                                    <input type="text" class="form-control" id="shipping_address_line_2" name="shipping_address_line_2" value="<?= htmlspecialchars($profile['shipping_address_line_2'] ?? '') ?>">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="shipping_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="shipping_city" name="shipping_city" value="<?= htmlspecialchars($profile['shipping_city'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="shipping_state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="shipping_state" name="shipping_state" value="<?= htmlspecialchars($profile['shipping_state'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="shipping_postal_code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code" value="<?= htmlspecialchars($profile['shipping_postal_code'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="shipping_country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="shipping_country" name="shipping_country" value="<?= htmlspecialchars($profile['shipping_country'] ?? 'Bangladesh') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="shipping_phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone" value="<?= htmlspecialchars($profile['shipping_phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Addresses
                        </button>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form id="passwordForm">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                                    <small class="text-muted">Password must be at least 6 characters long</small>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preferences Tab -->
                <div class="tab-pane fade" id="preferences" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Notification Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form id="preferencesForm">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="newsletter_subscription" name="newsletter_subscription" <?= ($profile['newsletter_subscription'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="newsletter_subscription">
                                            Subscribe to newsletter and promotional emails
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="sms_notifications" name="sms_notifications" <?= ($profile['sms_notifications'] ?? 1) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="sms_notifications">
                                            Receive SMS notifications for order updates
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Preferences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/components/footer.php'; ?>

<script>
$(document).ready(function() {
    // Handle same as billing checkbox
    $('#same_as_billing').on('change', function() {
        if ($(this).is(':checked')) {
            $('#shippingAddressFields').hide();
        } else {
            $('#shippingAddressFields').show();
        }
    });

    // Initialize shipping address visibility
    if ($('#same_as_billing').is(':checked')) {
        $('#shippingAddressFields').hide();
    }

    // Profile form submission
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        submitForm('profile', $(this).serialize());
    });

    // Address form submission
    $('#addressForm').on('submit', function(e) {
        e.preventDefault();
        submitForm('addresses', $(this).serialize());
    });

    // Password form submission
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        
        if ($('#new_password').val() !== $('#confirm_password').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'New password and confirm password do not match.'
            });
            return;
        }
        
        submitForm('password', $(this).serialize());
    });

    // Preferences form submission
    $('#preferencesForm').on('submit', function(e) {
        e.preventDefault();
        submitForm('preferences', $(this).serialize());
    });

    function submitForm(type, data) {
        $.ajax({
            url: 'apis/update-user-settings.php',
            method: 'POST',
            data: data + '&type=' + type,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    if (type === 'password') {
                        $('#passwordForm')[0].reset();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving your settings.'
                });
            }
        });
    }
});
</script>

<?php $db->disconnect(); ?>