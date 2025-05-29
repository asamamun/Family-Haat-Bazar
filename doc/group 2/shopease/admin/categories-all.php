<?php
// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/../config.php';

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/components/navbar.php'; ?>
</head>
<body class="sb-nav-fixed">
    <!-- <?php require_once __DIR__ . '/components/navbar.php'; ?> -->
    <div id="layoutSidenav">
        <!-- <?php require_once __DIR__ . '/components/sidebar.php'; ?> -->
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4">
                <!-- Main content goes here -->
                <h1 class="mt-4">Dashboard</h1>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i>
                        Data Overview
                    </div>
                    <div class="card-body">
                        <!-- Add your dynamic content here -->
                    </div>
                </div>
            </main>
            <?php require_once __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>

    <!-- JavaScript Assets -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.3.0/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    <script src="<?= ASSET_URL ?>js/scripts.js"></script>
    <script src="<?= ASSET_URL ?>js/chart-area-demo.js"></script>
    <script src="<?= ASSET_URL ?>js/chart-bar-demo.js"></script>
    <script src="<?= ASSET_URL ?>js/datatables-simple-demo.js"></script>
</body>
</html>