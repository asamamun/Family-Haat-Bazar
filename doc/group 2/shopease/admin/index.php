<?php
declare(strict_types=1);

// Session and security
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict'
    ]);
}

// Autoload and environment
require __DIR__ . '/../vendor/autoload.php';
use App\auth\Admin;

// Authentication check
if (!Admin::check()) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access Denied');
}

// Page metadata
$pageTitle = "Admin Dashboard";
$activeMenu = "dashboard";
$breadcrumbs = [
    ['name' => 'Home', 'url' => '/admin'],
    ['name' => 'Dashboard', 'url' => '/admin/dashboard.php']
];

// Include header
require __DIR__.'/components/navbar.php';
?>

<body class="sb-nav-fixed">
    <!-- <?php require __DIR__.'/components/navbar.php'; ?> -->
    
    <div id="layoutSidenav">
        <!-- <?php require __DIR__.'/components/sidebar.php'; ?> -->
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="mt-4"><?= htmlspecialchars($pageTitle) ?></h1>
                        <div class="breadcrumb">
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <a href="<?= htmlspecialchars($crumb['url']) ?>" 
                                   class="text-decoration-none">
                                    <?= htmlspecialchars($crumb['name']) ?>
                                </a>
                                <?php if (!end($breadcrumbs) === $crumb): ?>
                                    <span class="mx-2">/</span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <?php 
                        $stats = [
                            ['title' => 'Total Users', 'value' => 1245, 'icon' => 'users', 'color' => 'primary'],
                            ['title' => 'Revenue', 'value' => '$34,245', 'icon' => 'dollar-sign', 'color' => 'success'],
                            ['title' => 'Pending Orders', 'value' => 23, 'icon' => 'shopping-cart', 'color' => 'warning'],
                            ['title' => 'Support Tickets', 'value' => 12, 'icon' => 'ticket-alt', 'color' => 'danger']
                        ];
                        
                        foreach ($stats as $stat): ?>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-<?= $stat['color'] ?> shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-<?= $stat['color'] ?> text-uppercase mb-1">
                                                <?= htmlspecialchars($stat['title']) ?>
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?= htmlspecialchars($stat['value']) ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-<?= $stat['icon'] ?> fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Charts Row -->
                    <div class="row">
                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Revenue Overview</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow">
                                            <a class="dropdown-item" href="#">Export Data</a>
                                            <a class="dropdown-item" href="#">Print Chart</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Refresh</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="revenueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Traffic Sources</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="trafficChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Direct
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Social
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-info"></i> Referral
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="activityTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Sample activity data - in real app would come from database
                                        $activities = [
                                            ['time' => '2 mins ago', 'user' => 'John Doe', 'action' => 'Login', 'details' => 'Successful login from 192.168.1.1'],
                                            ['time' => '15 mins ago', 'user' => 'Jane Smith', 'action' => 'Update', 'details' => 'Updated product #1234'],
                                            ['time' => '1 hour ago', 'user' => 'Admin', 'action' => 'Delete', 'details' => 'Deleted user #5678'],
                                            ['time' => '3 hours ago', 'user' => 'Robert Johnson', 'action' => 'Create', 'details' => 'Created new order #9012'],
                                            ['time' => '5 hours ago', 'user' => 'Admin', 'action' => 'Settings', 'details' => 'Updated system settings'],
                                        ];
                                        
                                        foreach ($activities as $activity): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($activity['time']) ?></td>
                                            <td><?= htmlspecialchars($activity['user']) ?></td>
                                            <td><?= htmlspecialchars($activity['action']) ?></td>
                                            <td><?= htmlspecialchars($activity['details']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <?php require __DIR__.'/components/footer.php'; ?>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="<?= htmlspecialchars(settings()['adminpage']) ?>assets/vendor/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars(settings()['adminpage']) ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= htmlspecialchars(settings()['adminpage']) ?>assets/vendor/chart.js/Chart.min.js"></script>
    
    <!-- Page Level Scripts -->
    <script src="<?= htmlspecialchars(settings()['adminpage']) ?>assets/js/demo/chart-area-demo.js"></script>
    <script src="<?= htmlspecialchars(settings()['adminpage']) ?>assets/js/demo/chart-pie-demo.js"></script>
    
    <!-- Custom Scripts -->
    <script src="<?= htmlspecialchars(settings()['adminpage']) ?>assets/js/admin.js"></script>
</body>
</html>