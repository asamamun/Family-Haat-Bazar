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
$dbConfig = include __DIR__ . '/../config/idb.php';
$db = new MysqliDb(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['db']
);
?>
<?php require __DIR__ . '/components/header.php'; ?>

</head>

<body class="sb-nav-fixed">
    <?php require __DIR__ . '/components/navbar.php'; ?>
    <div id="layoutSidenav">
        <main>
            <?php require __DIR__ . '/components/sidebar.php'; ?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Best Seller Products Report</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Best Seller Products Overview</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Best Seller Products Data
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="bestSellerTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Total Quantity Sold</th>
                                    <th>Total Sales Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include_once 'components/footer.php'; ?>
</div>
<script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="<?= settings()['adminpage'] ?>assets/js/jquery-3.7.1.min.js"></script>    
<script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-2.3.2/datatables.min.js" integrity="sha384-rL0MBj9uZEDNQEfrmF51TAYo90+AinpwWp2+duU1VDW/RG7flzbPjbqEI3hlSRUv" crossorigin="anonymous"></script>


<script>
    $(document).ready(function() {
        var bestSellerTable = $('#bestSellerTable').DataTable({
            "processing": true,
            "serverSide": true,
            "deferLoading": true,
            "ajax": {
                "url": "report-ajax.php",
                "type": "POST",
                "data": function (d) {
                    d.action = 'fetchBestSellers';
                }
            },
            "columns": [
                { "data": "product_id" },
                { "data": "product_name" },
                { "data": "total_quantity_sold" },
                { "data": "total_sales_amount" }
            ],
            "responsive": true,
            "order": [[2, "desc"]]
        });

        // Initial load
        bestSellerTable.ajax.reload();
    });
</script>

<?php include_once 'components/footer.php'; ?>