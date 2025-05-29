<?php
// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/../config.php';

// Load Composer autoloader (if needed)
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
            <main class="container-fluid px-4 py-4">
                <h1 class="mb-4">Admin Dashboard</h1>
                <div class="row">
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-area me-1"></i>
                                Area Chart
                            </div>
                            <div class="card-body">
                                <canvas id="areaChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-chart-bar me-1"></i>
                                Bar Chart
                            </div>
                            <div class="card-body">
                                <canvas id="barChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i>
                        Data Table
                    </div>
                    <div class="card-body">
                        <table id="dataTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dynamic data will be inserted here -->
                            </tbody>
                        </table>
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