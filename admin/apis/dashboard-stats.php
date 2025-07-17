<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../../vendor/autoload.php';

// Database connection using settings
$db = new MysqliDb(
    settings()['hostname'],
    settings()['user'],
    settings()['password'],
    settings()['database']
);

header('Content-Type: application/json');

try {
    $today = date('Y-m-d');

    // Total Users
    $totalUsers = $db->getValue('users', 'COUNT(*)');

    // Total Products
    $totalProducts = $db->getValue('products', 'COUNT(*)');

    // Pending Orders
    $pendingOrders = $db->where('status', 'pending')->getValue('orders', 'COUNT(*)');

    // Today's Sales
    $todaysSales = $db->where('DATE(created_at)', $today)
        ->where('status', 'completed')
        ->getValue('orders', 'SUM(total_amount)') ?: 0;

    // Day-wise sales for last 7 days (for area chart)
    $dayWiseSales = [];
    $dayLabels = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $sales = $db->where('DATE(created_at)', $date)
            ->where('status', 'completed')
            ->getValue('orders', 'SUM(total_amount)') ?: 0;

        $dayWiseSales[] = floatval($sales);
        $dayLabels[] = date('M j', strtotime($date));
    }

    // Month-wise sales for last 6 months (for bar chart)
    $monthWiseSales = [];
    $monthLabels = [];

    for ($i = 5; $i >= 0; $i--) {
        $date = date('Y-m-01', strtotime("-$i months"));
        $nextMonth = date('Y-m-01', strtotime("+1 month", strtotime($date)));

        $sales = $db->where('created_at', $date, '>=')
            ->where('created_at', $nextMonth, '<')
            ->where('status', 'completed')
            ->getValue('orders', 'SUM(total_amount)') ?: 0;

        $monthWiseSales[] = floatval($sales);
        $monthLabels[] = date('M Y', strtotime($date));
    }

    // Recent orders for table
    $recentOrders = $db->orderBy('created_at', 'DESC')
        ->get('orders', 10, [
            'id', 'order_number', 'user_id', 'status',
            'payment_status', 'total_amount', 'created_at',
        ]);

    // Get user names for recent orders
    foreach ($recentOrders as &$order) {
        if ($order['user_id']) {
            $user = $db->where('id', $order['user_id'])->getOne('users', ['email', 'first_name', 'last_name']);
            $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
            $order['customer_name'] = $fullName ?: $user['email'] ?: 'User #' . $order['user_id'];
        } else {
            $order['customer_name'] = 'Guest';
        }
    }

    $response = [
        'cards' => [
            'total_users' => intval($totalUsers),
            'total_products' => intval($totalProducts),
            'pending_orders' => intval($pendingOrders),
            'todays_sales' => floatval($todaysSales),
        ],
        'charts' => [
            'daywise' => [
                'labels' => $dayLabels,
                'data' => $dayWiseSales,
            ],
            'monthwise' => [
                'labels' => $monthLabels,
                'data' => $monthWiseSales,
            ],
        ],
        'recent_orders' => $recentOrders,
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'cards' => [
            'total_users' => 0,
            'total_products' => 0,
            'pending_orders' => 0,
            'todays_sales' => 0,
        ],
        'charts' => [
            'daywise' => ['labels' => [], 'data' => []],
            'monthwise' => ['labels' => [], 'data' => []],
        ],
        'recent_orders' => [],
    ]);
}

$db->disconnect();
