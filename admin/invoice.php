<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

// Database connection
$db = new MysqliDb;
$id = $_GET['id']??0;

// Check if the order exists
if (!$db->where('id', $id)->has('orders')) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Fetch invoice details
$db->where('id', $id);
$order = $db->getOne('orders');

// Fetch invoice items
$db->join('products', 'order_items.product_id = products.id', 'LEFT');
$db->where('order_id', $id);
$items = $db->get('order_items',NULL, 'order_items.*, products.name as product_name, products.sku as product_sku');
// var_dump($items);
/* 
//items example
["id"]=>
    int(19)
    ["order_id"]=>
    int(9)
    ["product_id"]=>
    int(1)
    ["product_name"]=>
    string(14) "Cotton T-Shirt"
    ["product_sku"]=>
    string(7) "TSH-001"
    ["quantity"]=>
    int(1)
    ["unit_price"]=>
    string(5) "15.99"
    ["total_price"]=>
    string(5) "15.99"
    ["created_at"]=>
    string(19) "2025-07-01 12:52:38" */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($order['id']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }
        .invoice-container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .invoice-header img {
            max-height: 80px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .badge-unpaid {
            background-color: #ffcca107;
            color: #000;
        }
        @media print {
            .no-print {
                display: none;
            }
            .invoice-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <?php //require __DIR__ . '/components/navbar.php'; ?>
    <div id="layoutSidenav">
        <?php //require __DIR__ . '/components/sidebar.php'; ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="invoice-container">
                    <div class="invoice-header">
                        <img src="<?= settings()['adminpage'] ?>assets/img/logo.png" alt="ShopEase">
                        <h2 class="mt-2">Invoice</h2>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>From:</h5>
                            <p>
                                <strong>ShopEase</strong><br>
                                House 815, West Kazipara<br>
                                Mirpur, Dhaka, Bangladesh, 1216<br>
                                Email: contact@shopease.com<br>
                                Phone: (+880) 111-000-0000
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5>Invoice Details:</h5>
                            <p>
                                <strong>Invoice id:</strong> <?= htmlspecialchars($order['id']) ?><br>
                                <strong>Invoice #:</strong> <?= htmlspecialchars($order['order_number']) ?><br>
                                <strong>Date:</strong> <?= htmlspecialchars(date('F d, Y', strtotime($order['created_at']))) ?><br>
                                <strong>User:</strong> <?=  $order['user_id'] ?><br>
                                <strong>Status:</strong> <span class="badge badge-unpaid"><?= htmlspecialchars($order['status']) ?></span>
                            </p>
                        </div>
                    </div><hr>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Shipping info:</h5><hr>
                            <p>
                                <strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?><br>
                                <strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?><br>
                                <strong>Transaction ID:</strong> <?= htmlspecialchars($order['transaction_id']) ?><br>
                                <strong>Subtotal:</strong> <?= htmlspecialchars($order['subtotal']) ?><br>
                                <strong>Discount:</strong> <?= htmlspecialchars($order['discount_amount']) ?><br>
                                <strong>Coupon ID:</strong> <?= htmlspecialchars($order['coupon_id']) ?><br>
                                <strong>Tax:</strong> <?= htmlspecialchars($order['tax_amount']) ?><br>
                                <strong>Shipping:</strong> <?= htmlspecialchars($order['shipping_amount']) ?><br>
                                <strong>Total:</strong> <?= htmlspecialchars($order['total_amount']) ?><br>
                                <strong>Currency:</strong> <?= htmlspecialchars($order['currency']) ?><br>
                                <strong>Notes:</strong> <?= htmlspecialchars($order['notes']) ?><br>
                                
                               
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5>Billing Address:</h5>
                            <hr>
                            <p>
                                <strong>Billing First Name:</strong> <?= htmlspecialchars($order['billing_first_name']) ?><br>
                                <strong>Billing Last Name:</strong> <?= htmlspecialchars($order['billing_last_name']) ?><br>
                                <strong>Billing Company:</strong> <?= htmlspecialchars($order['billing_company']) ?><br>
                                <strong>Billing Address Line 1:</strong> <?= htmlspecialchars($order['billing_address_line_1']) ?><br>
                                <strong>Billing Address Line 2:</strong> <?= htmlspecialchars($order['billing_address_line_2']) ?><br>
                                <strong>Billing City:</strong> <?= htmlspecialchars($order['billing_city']) ?><br>
                                <strong>Billing State:</strong> <?= htmlspecialchars($order['billing_state']) ?><br>
                                <strong>Billing Postal Code:</strong> <?= htmlspecialchars($order['billing_postal_code']) ?><br>
                                <strong>Billing Country:</strong> <?= htmlspecialchars($order['billing_country']) ?><br>
                                <strong>Billing Phone:</strong> <?= htmlspecialchars($order['billing_phone']) ?><br>
                            </p>
                        </div>
                    </div><hr>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <!-- <th>Description</th> -->
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $subtotal = 0;
                                foreach ($items as $key => $item) {
                                    $itemPrice = $item['unit_price'];
                                    $itemTotal = $item['total_price'];
                                    $subtotal += $itemTotal;
                                ?>
                                    <tr>
                                        <td><?= $key + 1 ?></td>
                                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                                        <!-- <td><?= htmlspecialchars($item['description']) ?></td> -->
                                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                                        <td>Tk. <?= number_format($itemPrice, 2) ?></td>
                                        <td>Tk. <?= number_format($itemTotal, 2) ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <?php
                                        $taxRate = 0.10;
                                        $taxAmount = $subtotal * $taxRate;
                                        $grandTotal = $subtotal + $taxAmount;
                                    ?>
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end">Tk. <?= number_format($subtotal, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tax (<?= $taxRate * 100 ?>%):</strong></td>
                                        <td class="text-end">Tk. <?= number_format($taxAmount, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Grand Total:</strong></td>
                                        <td class="text-end"><strong>Tk. <?= number_format($grandTotal, 2) ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="text-center no-print">
                        <button class="btn btn-primary me-2" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                        <button class="btn btn-danger" onclick="alert('PDF export functionality requires a library like Dompdf. Please implement it.');"><i class="fas fa-file-pdf"></i> Export as PDF</button>
                    </div>
                    <div class="mt-4">
                        <p><strong>Notes:</strong> Please make payment within 15 days. A 1.5% late fee will be applied to unpaid balances after the due date.</p>
                    </div>
                </div>
            </main>
            <?php require __DIR__ . '/components/footer.php'; ?>
        </div>
    </div>
    <script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/demo/chart-area-demo.js"></script>
    <script src="<?= settings()['adminpage'] ?>assets/demo/chart-bar-demo.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/datatables-simple-demo.js"></script>
</body>
</html>