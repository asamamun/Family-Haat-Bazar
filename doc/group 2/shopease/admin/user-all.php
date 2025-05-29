<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../vendor/autoload.php';

use App\auth\Admin;

if (!Admin::Check()) {
    header('HTTP/1.1 503 Service Unavailable');
    exit;
}

$db = new MysqliDb();
$users = $db->get('users');
?>
<?php require __DIR__ . '/components/header.php'; ?>
</head>
<body class="sb-nav-fixed">
<?php require __DIR__ . '/components/navbar.php'; ?>

<div id="layoutSidenav">
    <?php require __DIR__ . '/components/sidebar.php'; ?>
    <div id="layoutSidenav_content">
        <main class="container-fluid px-4">
            <h1 class="mt-4">All Users</h1>
            <div class="card mb-4">
                <div class="card-body">
                    Below is the list of all registered users.
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Active</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= (int) $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['phone']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= $user['is_active'] ? 'Yes' : 'No' ?></td>
                                <td><?= htmlspecialchars($user['created_at']) ?></td>
                                <td>
                                    <a href="user_edit.php?id=<?= (int) $user['id'] ?>" title="Edit">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <a href="user_delete.php?id=<?= (int) $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?')" title="Delete">
                                        <i class="bi bi-trash3 text-danger"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <?php require __DIR__ . '/components/footer.php'; ?>
    </div>
</div>

<!-- JS Scripts -->
<script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>
</body>
</html>
