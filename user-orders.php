<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['message'] = "You must log in to view your orders.";
    header('Location: login.php');
    exit;
}

require __DIR__ . '/vendor/autoload.php';
$db = new MysqliDb();
$page = "My Orders";

// Get user's orders
$userId = $_SESSION['userid'];
$orders = $db->where('user_id', $userId)
             ->orderBy('created_at', 'DESC')
             ->get('orders');
?>

<?php require __DIR__ . '/components/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4"><i class="fas fa-shopping-bag me-2"></i>My Orders</h2>
            
            <?php if (empty($orders)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    You haven't placed any orders yet. <a href="index.php" class="alert-link">Start shopping now!</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($orders as $order): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><?= htmlspecialchars($order['order_number']) ?></h6>
                                    <span class="badge bg-<?= $order['status'] == 'completed' ? 'success' : ($order['status'] == 'pending' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <strong>Date:</strong> <?= date('M d, Y', strtotime($order['created_at'])) ?><br>
                                        <strong>Total:</strong> ৳<?= number_format($order['total_amount'], 2) ?><br>
                                        <strong>Payment:</strong> <?= ucfirst($order['payment_method']) ?><br>
                                        <strong>Payment Status:</strong> 
                                        <span class="badge bg-<?= $order['payment_status'] == 'completed' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($order['payment_status']) ?>
                                        </span>
                                    </p>
                                    
                                    <?php
                                    // Get order items
                                    $orderItems = $db->where('order_id', $order['id'])->get('order_items');
                                    ?>
                                    
                                    <div class="mb-3">
                                        <strong>Items:</strong>
                                        <ul class="list-unstyled mt-2">
                                            <?php foreach ($orderItems as $item): ?>
                                                <li class="small">
                                                    <?= htmlspecialchars($item['product_name']) ?> 
                                                    <span class="text-muted">(Qty: <?= $item['quantity'] ?>)</span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </button>
                                        <?php if ($order['status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="cancelOrder(<?= $order['id'] ?>)">
                                                <i class="fas fa-times me-1"></i>Cancel
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/components/footer.php'; ?>

<script>
function viewOrderDetails(orderId) {
    // Load order details via AJAX
    $.ajax({
        url: 'apis/get-order-details.php',
        method: 'GET',
        data: { order_id: orderId },
        success: function(response) {
            $('#orderDetailsContent').html(response);
            $('#orderDetailsModal').modal('show');
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load order details.'
            });
        }
    });
}

function cancelOrder(orderId) {
    Swal.fire({
        title: 'Cancel Order?',
        text: 'Are you sure you want to cancel this order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'apis/cancel-order.php',
                method: 'POST',
                data: { order_id: orderId },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Order Cancelled',
                            text: 'Your order has been cancelled successfully.'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to cancel order.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to cancel order.'
                    });
                }
            });
        }
    });
}
</script>

<?php $db->disconnect(); ?>