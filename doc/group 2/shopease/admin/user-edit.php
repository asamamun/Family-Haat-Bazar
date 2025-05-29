<?php
// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/../config.php';

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\auth\Admin;

// Check admin authentication
if (!Admin::Check()) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Initialize database
$db = new MysqliDb();

try {
    // Handle form submission
    if (isset($_POST['username'])) {
        // Validate and sanitize input
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $pass1 = $_POST['pass1'] ?? '';
        $pass2 = $_POST['pass2'] ?? '';
        $role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);

        if (!$id || !$username || !$email || !$role) {
            throw new Exception('All fields except password are required.');
        }

        // Prepare update data
        $data = [
            'username' => $username,
            'email' => $email,
            'role' => $role
        ];

        // Update password only if provided
        if (!empty($pass1) && $pass1 === $pass2) {
            $data['password'] = password_hash($pass1, PASSWORD_DEFAULT);
        } elseif ($pass1 !== $pass2) {
            throw new Exception('Passwords do not match.');
        }

        // Perform update
        $db->where('id', $id);
        if ($db->update('users', $data)) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'User updated successfully.'];
        } else {
            throw new Exception('Failed to update user: ' . $db->getLastError());
        }
    }

    // Fetch user data for editing
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id) {
            $db->where('id', $id);
            $row = $db->getOne('users', ['id', 'username', 'email', 'role']);
            if (!$row) {
                throw new Exception('User not found.');
            }
        } else {
            throw new Exception('Invalid user ID.');
        }
    } else {
        header('Location: ' . BASE_URL . 'user_select.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['message'] = ['type' => 'error', 'text' => $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/components/header.php'; ?>
</head>
<body class="sb-nav-fixed">
    <?php require_once __DIR__ . '/components/navbar.php'; ?>
    <div id="layoutSidenav">
        <?php require_once __DIR__ . '/components/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4 py-4">
                <h1 class="mb-4">Edit User</h1>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show" role="alert">
                        <?= $_SESSION['message']['text'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-user-edit me-1"></i>
                        Update User Details
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($row['username']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="pass1" class="form-label">New Password (Optional)</label>
                                <input type="password" name="pass1" id="pass1" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="pass2" class="form-label">Confirm Password</label>
                                <input type="password" name="pass2" id="pass2" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select name="role" id="role" class="form-select" required>
                                    <option value="1" <?= $row['role'] == '1' ? 'selected' : '' ?>>User</option>
                                    <option value="2" <?= $row['role'] == '2' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </main>
            <?php require_once __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= ASSET_URL ?>js/scripts.js"></script>
</body>
</html>