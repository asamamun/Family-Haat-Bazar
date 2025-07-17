<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
use App\auth\Admin;
if(!Admin::Check()){
    header('HTTP/1.1 503 Service Unavailable');
    exit;
}
?>
<?php require __DIR__.'/components/header.php'; ?>
    </head>
    <body class="sb-nav-fixed">
    <?php require __DIR__.'/components/navbar.php'; ?>
        <div id="layoutSidenav">
        <?php require __DIR__.'/components/sidebar.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                        
                        <!-- Statistics Cards -->
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-white-75 small">Total Users</div>
                                                <div class="text-lg fw-bold" id="totalUsers">0</div>
                                            </div>
                                            <div class="fa-3x opacity-25">
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="users-all.php">View All Users</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-white-75 small">Total Products</div>
                                                <div class="text-lg fw-bold" id="totalProducts">0</div>
                                            </div>
                                            <div class="fa-3x opacity-25">
                                                <i class="fas fa-box"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="product-all.php">View All Products</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-info text-white mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-white-75 small">Pending Orders</div>
                                                <div class="text-lg fw-bold" id="pendingOrders">0</div>
                                            </div>
                                            <div class="fa-3x opacity-25">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="orders-all.php">View All Orders</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-white-75 small">Today's Sales</div>
                                                <div class="text-lg fw-bold" id="todaysSales">$0.00</div>
                                            </div>
                                            <div class="fa-3x opacity-25">
                                                <i class="fas fa-dollar-sign"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="sales-report.php">View Sales Report</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Charts -->
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-chart-area me-1"></i>
                                            Daily Sales (Last 7 Days)
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshCharts()">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <div class="card-body"><canvas id="myAreaChart" width="100%" height="40"></canvas></div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-chart-bar me-1"></i>
                                            Monthly Sales (Last 6 Months)
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" onclick="refreshCharts()">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <div class="card-body"><canvas id="myBarChart" width="100%" height="40"></canvas></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Orders Table -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-table me-1"></i>
                                    Recent Orders
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshData()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="recentOrdersTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Total</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentOrdersBody">
                                        <tr>
                                            <td colspan="6" class="text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
                <?php require __DIR__.'/components/footer.php'; ?>
            </div>
        </div>

        <script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
        // Global chart variables
        let areaChart, barChart;

        // Load dashboard data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
        });

        function loadDashboardData() {
            fetch('apis/dashboard-stats.php')
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Dashboard data received:', data);
                    if (data.error) {
                        console.error('API Error:', data.error);
                        // Still update with default/empty data
                        updateCards(data.cards);
                        updateCharts(data.charts);
                        updateRecentOrders(data.recent_orders);
                    } else {
                        updateCards(data.cards);
                        updateCharts(data.charts);
                        updateRecentOrders(data.recent_orders);
                    }
                })
                .catch(error => {
                    console.error('Error loading dashboard data:', error);
                    // Load with default values if API fails
                    updateCards({
                        total_users: 0,
                        total_products: 0,
                        pending_orders: 0,
                        todays_sales: 0
                    });
                    updateCharts({
                        daywise: { labels: [], data: [] },
                        monthwise: { labels: [], data: [] }
                    });
                    updateRecentOrders([]);
                });
        }

        function updateCards(cards) {
            document.getElementById('totalUsers').textContent = cards.total_users.toLocaleString();
            document.getElementById('totalProducts').textContent = cards.total_products.toLocaleString();
            document.getElementById('pendingOrders').textContent = cards.pending_orders.toLocaleString();
            document.getElementById('todaysSales').textContent = '$' + cards.todays_sales.toFixed(2);
        }

        function updateCharts(charts) {
            // Update Area Chart (Daily Sales)
            const areaCtx = document.getElementById('myAreaChart').getContext('2d');
            
            if (areaChart) {
                areaChart.destroy();
            }
            
            areaChart = new Chart(areaCtx, {
                type: 'line',
                data: {
                    labels: charts.daywise.labels,
                    datasets: [{
                        label: 'Daily Sales',
                        data: charts.daywise.data,
                        backgroundColor: 'rgba(2, 117, 216, 0.2)',
                        borderColor: 'rgba(2, 117, 216, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toFixed(0);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Update Bar Chart (Monthly Sales)
            const barCtx = document.getElementById('myBarChart').getContext('2d');
            
            if (barChart) {
                barChart.destroy();
            }
            
            barChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: charts.monthwise.labels,
                    datasets: [{
                        label: 'Monthly Sales',
                        data: charts.monthwise.data,
                        backgroundColor: 'rgba(2, 117, 216, 0.8)',
                        borderColor: 'rgba(2, 117, 216, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toFixed(0);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        function updateRecentOrders(orders) {
            const tbody = document.getElementById('recentOrdersBody');
            tbody.innerHTML = '';

            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No recent orders found</td></tr>';
                return;
            }

            orders.forEach(order => {
                const statusBadge = getStatusBadge(order.status);
                const paymentBadge = getPaymentBadge(order.payment_status);
                const date = new Date(order.created_at).toLocaleDateString();

                const row = `
                    <tr>
                        <td><strong>${order.order_number}</strong></td>
                        <td>${order.customer_name}</td>
                        <td>${statusBadge}</td>
                        <td>${paymentBadge}</td>
                        <td><strong>$${parseFloat(order.total_amount).toFixed(2)}</strong></td>
                        <td>${date}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        function getStatusBadge(status) {
            const badges = {
                'pending': '<span class="badge bg-warning">Pending</span>',
                'processing': '<span class="badge bg-info">Processing</span>',
                'completed': '<span class="badge bg-success">Completed</span>',
                'cancelled': '<span class="badge bg-danger">Cancelled</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">' + status + '</span>';
        }

        function getPaymentBadge(status) {
            const badges = {
                'pending': '<span class="badge bg-warning">Pending</span>',
                'completed': '<span class="badge bg-success">Paid</span>',
                'failed': '<span class="badge bg-danger">Failed</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">' + status + '</span>';
        }

        function refreshData() {
            loadDashboardData();
        }

        function refreshCharts() {
            loadDashboardData();
        }
        </script>
    </body>
</html>