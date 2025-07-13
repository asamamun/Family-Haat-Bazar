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
            <h1 class="mt-4">Sales Report</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Sales Report Overview</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filter Sales Data
                </div>
                <div class="card-body">
                    <form id="salesFilterForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate" name="startDate">
                            </div>
                            <div class="col-md-6">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate" name="endDate">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Sales Data
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="salesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Order Number</th>
                                    <th>Order Type</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Payment Method</th>
                                    <th>Transaction ID</th>
                                    <th>Subtotal</th>
                                    <th>Discount Amount</th>
                                    <th>Tax Amount</th>
                                    <th>Total Amount</th>
                                    <th>Currency</th>
                                    <th>Notes</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
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
        var salesTable = $('#salesTable').DataTable({
            "processing": true,
            "serverSide": true,
            "deferLoading": true, // Defer initial loading
            "ajax": {
                "url": "report-ajax.php",
                "type": "POST",
                "data": function (d) {
                    d.action = 'fetchSales';
                    d.startDate = $('#startDate').val();
                    d.endDate = $('#endDate').val();
                }
            },
            "columns": [
                { "data": "id" },
                { "data": "order_number" },
                { "data": "order_type" },
                { "data": "status" },
                { "data": "payment_status" },
                { "data": "payment_method" },
                { "data": "transaction_id" },
                { "data": "subtotal" },
                { "data": "discount_amount" },
                { "data": "tax_amount" },
                { "data": "total_amount" },
                { "data": "currency" },
                { "data": "notes" },
                { "data": "created_at" },
                { "data": "updated_at" }
            ],
            "responsive": true,
            "order": [[0, "desc"]]
        });

        $('#salesFilterForm').on('submit', function(e) {
            e.preventDefault();
            salesTable.ajax.reload();
        });
    });
</script>

<?php include_once 'components/footer.php'; ?>
