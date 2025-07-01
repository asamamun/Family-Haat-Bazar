<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
?>
<?php require __DIR__ . '/components/header.php'; ?>

</head>

<body class="sb-nav-fixed">
    <?php require __DIR__ . '/components/navbar.php'; ?>
    <div id="layoutSidenav">
        <main>
            <?php require __DIR__ . '/components/sidebar.php'; ?>
            <div id="layoutSidenav_content">

                <!-- changed content -->
                <!-- TODO: subcategory CRUD with AJAX -->
                <!--  -->
                <!--  -->
                <div class="row">
                    <div class="col-12">
                        <?php
                        $db = new MysqliDb();
                        //get order with order details

                        $id = $_GET['id'];
                        $db->where("id", $id);
                        $orderinfo = $db->get('orders');
                        // var_dump($orderinfo);
                        $orderItems = $db->where("order_id", $id)->get('order_items');
                        // var_dump($orderItems);
/*array(40) {
    ["id"]=>
    int(10)
    ["order_number"]=>
    string(19) "POS-1751352994-4326"
    ["user_id"]=>
    int(7)
    ["order_type"]=>
    string(3) "pos"
    ["status"]=>
    string(9) "delivered"
    ["payment_status"]=>
    string(4) "paid"
    ["payment_method"]=>
    string(4) "cash"
    ["transaction_id"]=>
    NULL
    ["subtotal"]=>
    string(6) "361.97"
    ["discount_amount"]=>
    string(4) "0.00"
    ["coupon_id"]=>
    NULL
    ["tax_amount"]=>
    string(5) "28.96"
    ["shipping_amount"]=>
    string(4) "0.00"
    ["total_amount"]=>
    string(6) "390.93"
    ["currency"]=>
    string(3) "BDT"
    ["notes"]=>
    string(0) ""
    ["billing_first_name"]=>
    string(0) ""
    ["billing_last_name"]=>
    string(0) ""
    ["billing_company"]=>
    NULL
    ["billing_address_line_1"]=>
    string(0) ""
    ["billing_address_line_2"]=>
    NULL
    ["billing_city"]=>
    string(0) ""
    ["billing_state"]=>
    NULL
    ["billing_postal_code"]=>
    string(0) ""
    ["billing_country"]=>
    string(0) ""
    ["billing_phone"]=>
    string(0) ""
    ["shipping_first_name"]=>
    string(0) ""
    ["shipping_last_name"]=>
    string(0) ""
    ["shipping_company"]=>
    NULL
    ["shipping_address_line_1"]=>
    string(0) ""
    ["shipping_address_line_2"]=>
    NULL
    ["shipping_city"]=>
    string(0) ""
    ["shipping_state"]=>
    NULL
    ["shipping_postal_code"]=>
    string(0) ""
    ["shipping_country"]=>
    string(0) ""
    ["shipping_phone"]=>
    string(0) ""
    ["processed_by"]=>
    int(7)
    ["processed_at"]=>
    string(19) "2025-07-01 12:56:34"
    ["created_at"]=>
    string(19) "2025-07-01 12:56:34"
    ["updated_at"]=>
    string(19) "2025-07-01 12:56:34"
  }
}
array(3) {
  [0]=>
  array(9) {
    ["id"]=>
    int(22)
    ["order_id"]=>
    int(10)
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
    string(19) "2025-07-01 12:56:34"
  }
  [1]=>
  array(9) {
    ["id"]=>
    int(23)
    ["order_id"]=>
    int(10)
    ["product_id"]=>
    int(2)
    ["product_name"]=>
    string(12) "Denim Jacket"
    ["product_sku"]=>
    string(7) "JKT-002"
    ["quantity"]=>
    int(1)
    ["unit_price"]=>
    string(5) "45.99"
    ["total_price"]=>
    string(5) "45.99"
    ["created_at"]=>
    string(19) "2025-07-01 12:56:34"
  }
  [2]=>
  array(9) {
    ["id"]=>
    int(24)
    ["order_id"]=>
    int(10)
    ["product_id"]=>
    int(3)
    ["product_name"]=>
    string(16) "Electric Scooter"
    ["product_sku"]=>
    string(7) "SCO-001"
    ["quantity"]=>
    int(1)
    ["unit_price"]=>
    string(6) "299.99"
    ["total_price"]=>
    string(6) "299.99"
    ["created_at"]=>
    string(19) "2025-07-01 12:56:34"
  }
}*/
?>
                        <div class="container">
                            <div class="row justify-content-center">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h3 class="card-title">Order Information</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <h5>Order Number: <span class="text-secondary"><?php echo $orderinfo[0]['order_number']; ?></span></h5>
                                                </div>
                                                <div class="col-md-4">
                                                    <h5>Order Date: <span class="text-secondary"><?php echo $orderinfo[0]['created_at']; ?></span></h5>
                                                </div>
                                                <div class="col-md-4">
                                                    <h5>Order Status: <span class="text-secondary"><?php echo $orderinfo[0]['status']; ?></span> 
                                                <?php if($orderinfo[0]['order_type'] == 'online'): ?>
                                                    <span class="badge bg-warning" data-bs-toggle="modal" data-bs-target="#deleteModal">Change Status</span>
                                                <?php endif; ?>
                                                </h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row justify-content-center mt-3">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-secondary text-white">
                                            <h3 class="card-title">Order Items</h3>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Item Name</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Price</th>
                                                        <th>Total Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($orderItems as $item) { ?>
                                                        <tr>
                                                            <td><?php echo $item['product_name']; ?></td>
                                                            <td><?php echo $item['quantity']; ?></td>
                                                            <td><?php echo $item['unit_price']; ?></td>
                                                            <td><?php echo $item['total_price']; ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Update Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Update Status to: 
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="pending" value="pending">
                                <label class="form-check-label" for="pending">
                                    Pending
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="processing" value="processing">
                                <label class="form-check-label" for="processing">
                                    Processing
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="shipped" value="shipped">
                                <label class="form-check-label" for="shipped">
                                    Shipped
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="delivered" value="delivered">
                                <label class="form-check-label" for="delivered">
                                    Delivered
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="cancelled" value="cancelled">
                                <label class="form-check-label" for="cancelled">
                                    Cancelled
                                </label>
                            </div>
                            <!-- refunded -->
                             <div class="form-check">
                                <input class="form-check-input" type="radio" name="status" id="refunded" value="refunded">
                                <label class="form-check-label" for="refunded">
                                    Refunded
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">Update</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--  -->
            <!--  -->
            <!-- changed content  ends-->

            <!-- footer -->
            <?php require __DIR__ . '/components/footer.php'; ?>

        </main>
    </div>
    <script src="<?= settings()['adminpage'] ?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/scripts.js"></script>
    <script src="<?= settings()['adminpage'] ?>assets/js/jquery-3.7.1.min.js"></script>
<!--     <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap5.min.js"></script> -->

 
<script src="https://cdn.datatables.net/v/bs5/dt-2.3.2/datatables.min.js" integrity="sha384-rL0MBj9uZEDNQEfrmF51TAYo90+AinpwWp2+duU1VDW/RG7flzbPjbqEI3hlSRUv" crossorigin="anonymous"></script>


    <script>

         
    </script>
</body>

</html>