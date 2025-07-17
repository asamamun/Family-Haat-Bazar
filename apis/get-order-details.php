<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo '<div class="alert alert-danger">Please log in to view order details.</div>';
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
$db = new MysqliDb();

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    echo '<div class="alert alert-danger">Invalid order ID.</div>';
    exit;
}

$orderId = (int) $_GET['order_id'];
$userId = $_SESSION['userid'];

// Get order details (ensure user owns this order)
$order = $db->where('id', $orderId)
    ->where('user_id', $userId)
    ->getOne('orders');

if (!$order) {
    echo '<div class="alert alert-danger">Order not found or access denied.</div>';
    exit;
}

// Get order items
$orderItems = $db->where('order_id', $orderId)->get('order_items');
?>

<div class="order-details">
    <!-- Order Information Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Order Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Order Number:</strong> <?=htmlspecialchars($order['order_number'])?></p>
                    <p class="mb-2"><strong>Date:</strong> <?=date('F d, Y \a\t g:i A', strtotime($order['created_at']))?></p>
                    <p class="mb-2"><strong>Payment Method:</strong> <?=ucfirst($order['payment_method'])?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Status:</strong>
                        <span class="badge bg-<?=$order['status'] == 'completed' ? 'success' : ($order['status'] == 'pending' ? 'warning' : 'secondary')?>">
                            <?=ucfirst($order['status'])?>
                        </span>
                    </p>
                    <p class="mb-2"><strong>Payment Status:</strong>
                        <span class="badge bg-<?=$order['payment_status'] == 'completed' ? 'success' : 'warning'?>">
                            <?=ucfirst($order['payment_status'])?>
                        </span>
                    </p>
                    <?php if ($order['transaction_id']): ?>
                        <p class="mb-2"><strong>Transaction ID:</strong> <?=htmlspecialchars($order['transaction_id'])?></p>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>

    <!-- Addresses Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Billing Address</h6>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <strong><?=htmlspecialchars($order['billing_first_name'] . ' ' . $order['billing_last_name'])?></strong><br>
                        <?php if ($order['billing_company']): ?>
                            <?=htmlspecialchars($order['billing_company'])?><br>
                        <?php endif;?>
                        <?=htmlspecialchars($order['billing_address_line_1'])?><br>
                        <?php if ($order['billing_address_line_2']): ?>
                            <?=htmlspecialchars($order['billing_address_line_2'])?><br>
                        <?php endif;?>
                        <?=htmlspecialchars($order['billing_city'])?>, <?=htmlspecialchars($order['billing_state'])?> <?=htmlspecialchars($order['billing_postal_code'])?><br>
                        <?=htmlspecialchars($order['billing_country'])?><br>
                        <div class="mt-2">
                            <i class="fas fa-phone me-1 text-muted"></i><?=htmlspecialchars($order['billing_phone'])?>
                        </div>
                    </address>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipping Address</h6>
                </div>
                <div class="card-body">
                    <address class="mb-0">
                        <strong><?=htmlspecialchars($order['shipping_first_name'] . ' ' . $order['shipping_last_name'])?></strong><br>
                        <?php if ($order['shipping_company']): ?>
                            <?=htmlspecialchars($order['shipping_company'])?><br>
                        <?php endif;?>
                        <?=htmlspecialchars($order['shipping_address_line_1'])?><br>
                        <?php if ($order['shipping_address_line_2']): ?>
                            <?=htmlspecialchars($order['shipping_address_line_2'])?><br>
                        <?php endif;?>
                        <?=htmlspecialchars($order['shipping_city'])?>, <?=htmlspecialchars($order['shipping_state'])?> <?=htmlspecialchars($order['shipping_postal_code'])?><br>
                        <?=htmlspecialchars($order['shipping_country'])?><br>
                        <div class="mt-2">
                            <i class="fas fa-phone me-1 text-muted"></i><?=htmlspecialchars($order['shipping_phone'])?>
                        </div>
                    </address>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h6>Order Items</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td><?=htmlspecialchars($item['product_name'])?></td>
                                <td><?=htmlspecialchars($item['product_sku'])?></td>
                                <td><?=$item['quantity']?></td>
                                <td>৳<?=number_format($item['unit_price'], 2)?></td>
                                <td>৳<?=number_format($item['total_price'], 2)?></td>
                            </tr>
                        <?php endforeach;?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                            <td><strong>৳<?=number_format($order['subtotal'], 2)?></strong></td>
                        </tr>
                        <?php if ($order['discount_amount'] > 0): ?>
                            <tr>
                                <td colspan="4" class="text-end">Discount:</td>
                                <td>-৳<?=number_format($order['discount_amount'], 2)?></td>
                            </tr>
                        <?php endif;?>
                        <?php if ($order['tax_amount'] > 0): ?>
                            <tr>
                                <td colspan="4" class="text-end">Tax:</td>
                                <td>৳<?=number_format($order['tax_amount'], 2)?></td>
                            </tr>
                        <?php endif;?>
                        <?php if ($order['shipping_amount'] > 0): ?>
                            <tr>
                                <td colspan="4" class="text-end">Shipping:</td>
                                <td>৳<?=number_format($order['shipping_amount'], 2)?></td>
                            </tr>
                        <?php endif;?>
                        <tr class="table-active">
                            <td colspan="4" class="text-end"><strong>Total:</strong></td>
                            <td><strong>৳<?=number_format($order['total_amount'], 2)?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <?php if ($order['notes']): ?>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Order Notes</h6>
                <p class="text-muted"><?=htmlspecialchars($order['notes'])?></p>
            </div>
        </div>
    <?php endif;?>
</div>

<?php $db->disconnect();?>