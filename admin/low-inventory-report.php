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
            <h1 class="mt-4">Low Inventory Report</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Low Inventory Overview</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filter Low Inventory Data
                </div>
                <div class="card-body">
                    <form id="lowInventoryFilterForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="quantityThreshold" class="form-label">Quantity Threshold</label>
                                <input type="number" class="form-control" id="quantityThreshold" name="quantityThreshold" value="10" min="0">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Low Inventory Products Data
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="lowInventoryTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>SKU</th>
                                    <th>Current Stock</th>
                                    <th>Min Stock Level</th>
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
        var lowInventoryTable = $('#lowInventoryTable').DataTable({
            "processing": true,
            "serverSide": true,
            "deferLoading": true,
            "ajax": {
                "url": "report-ajax.php",
                "type": "POST",
                "data": function (d) {
                    d.action = 'fetchLowInventory';
                    d.threshold = $('#quantityThreshold').val();
                }
            },
            "columns": [
                { "data": "id" },
                { "data": "name" },
                { "data": "sku" },
                { "data": "stock_quantity" },
                { "data": "min_stock_level" }
            ],
            "responsive": true,
            "order": [[3, "asc"]]
        });

        $('#lowInventoryFilterForm').on('submit', function(e) {
            e.preventDefault();
            lowInventoryTable.ajax.reload();
        });

        // Initial load with default threshold
        lowInventoryTable.ajax.reload();
    });
</script>

<?php include_once 'components/footer.php'; ?>