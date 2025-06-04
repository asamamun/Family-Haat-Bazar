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
$categories = $db->get('categories', null, ['id', 'name', 'slug', 'description', 'image', 'is_active', 'sort_order', 'created_at', 'updated_at']);
/* echo "<pre>";
print_r($categories);
echo "</pre>";
exit; */
?>
<?php require __DIR__ . '/components/header.php'; ?>

</head>

<body class="sb-nav-fixed">
    <?php require __DIR__ . '/components/navbar.php'; ?>
    <div id="layoutSidenav">
        <?php require __DIR__ . '/components/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <!-- changed content -->
                <div class="d-flex justify-content-between">
                    <h3>All Categories</h3>
                    <a href="category-add.php" class="btn btn-primary">Add Category</a>
                </div>

                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= $category['id'] ?></td>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td><?= htmlspecialchars($category['slug']) ?></td>
                                <td><?= htmlspecialchars($category['description']) ?></td>
                                <td><img src="<?= settings()['root'] ?>/assets/categories/<?= $category['image'] ?>" width="50" height="50" /></td>
                                <td><?= $category['is_active'] ? 'Active' : 'Inactive' ?></td>
                                <td><?= $category['sort_order'] ?></td>
                                <td><?= $category['created_at'] ?></td>
                                <td><?= $category['updated_at'] ?></td>
                                <td>
                                    <a href="category-edit.php?id=<?= $category['id'] ?>" class="btn btn-primary">Edit</a>
                                    <a href="category-delete.php?id=<?= $category['id'] ?>" class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- changed content  ends-->
            </main>
            <!-- footer -->
            <?php require __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>
    <script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/demo/chart-area-demo.js"></script>
    <script src="<?= settings()['adminpage'] ?>assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>
</body>

</html>