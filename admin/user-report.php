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
            <h1 class="mt-4">User Report</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item active">User Report Overview</li>
            </ol>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filter User Data
                </div>
                <div class="card-body">
                    <form id="userFilterForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="userSearch" class="form-label">Search User</label>
                                <input type="text" class="form-control" id="userSearch" name="userSearch" placeholder="Search by username or email">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    User Data
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="userTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>First Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
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
                    Daywise Sales for Selected User
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="userDaywiseSalesTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Total Orders</th>
                                    <th>Total Sales Amount</th>
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
        var userTable = $('#userTable').DataTable({
            "processing": true,
            "serverSide": true,
            "deferLoading": true,
            "ajax": {
                "url": "report-ajax.php",
                "type": "POST",
                "data": function (d) {
                    d.action = 'fetchUsers';
                    d.searchValue = $('#userSearch').val();
                    console.log("Order Column Name:", d.columns[d.order[0].column].data); // Added for debugging
                }
            },
            "columns": [
                { "data": "id" },
                { "data": "first_name" },
                { "data": "email" },
                { "data": "role" },
                { "data": "created_at" },
                { "data": "updated_at" },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return '<button class="btn btn-info btn-sm view-user-sales" data-user-id="' + row.id + '">View Sales</button>';
                    }
                }
            ],
            "responsive": true,
            "order": [[0, "desc"]]
        });

        $('#userFilterForm').on('submit', function(e) {
            e.preventDefault();
            userTable.ajax.reload();
        });

        var userDaywiseSalesTable = $('#userDaywiseSalesTable').DataTable({
            "paging": false,
            "searching": false,
            "info": false,
            "columns": [
                { "data": "sale_date" },
                { "data": "total_orders" },
                { "data": "total_sales_amount" }
            ]
        });

        $('#userTable tbody').on('click', '.view-user-sales', function() {
            var userId = $(this).data('user-id');
            fetchUserDaywiseSales(userId);
        });

        function fetchUserDaywiseSales(userId) {
            $.ajax({
                url: "report-ajax.php",
                type: "POST",
                data: {
                    action: 'fetchUserDaywiseSales',
                    userId: userId
                },
                dataType: "json",
                success: function(response) {
                    userDaywiseSalesTable.clear().draw();
                    if (response.data && response.data.length > 0) {
                        userDaywiseSalesTable.rows.add(response.data).draw();
                    } else {
                        // Optionally display a message if no sales data
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching user daywise sales:", error);
                }
            });
        }
    });
</script>

<?php include_once 'components/footer.php'; ?>
