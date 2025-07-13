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
            <h1 class="mt-4">Product Report</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">Product Report Overview</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filter Product Data
                </div>
                <div class="card-body">
                    <form id="productFilterForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="productSearch" class="form-label">Search Product</label>
                                <input type="text" class="form-control" id="productSearch" name="productSearch" placeholder="Search by name or SKU">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Product Data
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="productTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock Quantity</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Daywise Sales for Selected Product
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="daywiseSalesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Quantity Sold</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Daywise sales data will be loaded here -->
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
        var productTable = $('#productTable').DataTable({
            "processing": true,
            "serverSide": true,
            "deferLoading": true,
            "ajax": {
                "url": "report-ajax.php",
                "type": "POST",
                "data": function (d) {
                    d.action = 'fetchProducts';
                    d.searchValue = $('#productSearch').val();
                }
            },
            "columns": [
                { "data": "id" },
                { "data": "name" },
                { "data": "sku" },
                { "data": "selling_price" },
                { "data": "stock_quantity" },
                { "data": "created_at" },
                { "data": "updated_at" },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-info btn-sm view-daywise-sales" data-product-id="' + row.id + '">View Sales</button>';
                    }
                }
            ],
            "responsive": true,
            "order": [[0, "desc"]]
        });

        $('#productFilterForm').on('submit', function(e) {
            e.preventDefault();
            console.log("Product filter form submitted."); // Added for debugging
            productTable.ajax.reload();
        });

        // Initialize daywiseSalesTable once
        var daywiseSalesTable = $('#daywiseSalesTable').DataTable({
            "paging": false,
            "searching": false,
            "info": false,
            "columns": [
                { "data": "sale_date" },
                { "data": "total_quantity_sold" },
                { "data": "total_sales_amount" }
            ]
        });

        // Handle view daywise sales button click
        $('#productTable tbody').on('click', '.view-daywise-sales', function() {
            var productId = $(this).data('product-id');
            fetchDaywiseSales(productId);
        });

        function fetchDaywiseSales(productId) {
            $.ajax({
                url: "report-ajax.php",
                type: "POST",
                data: {
                    action: 'fetchProductDaywiseSales',
                    productId: productId
                },
                dataType: "json",
                success: function(response) {
                    daywiseSalesTable.clear().draw();
                    if (response.data && response.data.length > 0) {
                        daywiseSalesTable.rows.add(response.data).draw();
                    } else {
                        // Optionally display a message if no sales data
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching daywise sales:", error);
                }
            });
        }
    });
</script>

<?php include_once 'components/footer.php'; ?>