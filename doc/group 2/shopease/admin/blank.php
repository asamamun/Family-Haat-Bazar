<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload dependencies via Composer
require __DIR__ . '/../vendor/autoload.php';
?>

<?php require __DIR__ . '/components/navbar.php'; ?>

</head>
<body class="sb-nav-fixed">

    <!-- <?php require __DIR__ . '/components/navbar.php'; ?> -->

    <div id="layoutSidenav">

        <!-- <?php require __DIR__ . '/components/sidebar.php'; ?> -->

        <div id="layoutSidenav_content">
            <main>
                <!-- Main content starts here -->
                <div class="container-fluid px-4">
                    <h1 class="mt-4">Dashboard</h1>
                    <p class="mb-4">Welcome to your admin panel.</p>
                    <!-- Add your custom content here -->
                </div>
                <!-- Main content ends here -->
            </main>

            <?php require __DIR__ . '/components/footer.php'; ?>
        </div>

    </div>

    <!-- Core JavaScript -->
    <script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>

    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/demo/chart-area-demo.js"></script>
    <script src="<?= settings()['adminpage'] ?>assets/demo/chart-bar-demo.js"></script>

    <!-- Simple-DataTables -->
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>

</body>
</html>
