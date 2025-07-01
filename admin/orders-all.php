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
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Orders Management</h5>                                
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="ordersTable" class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Order Number</th>
                                                <!-- <th>User ID</th> -->
                                                <th>Order Type</th>
                                                <th>Status</th>
                                                <th>Payment Status</th>
                                                <th>Payment Method</th>
                                                <th>Transaction ID</th>
                                                <th>Subtotal</th>
                                                <th>Discount Amount</th>
                                                <!-- <th>Coupon ID</th> -->
                                                <th>Tax Amount</th>
                                                <!-- <th>Shipping Amount</th> -->
                                                <th>Total Amount</th>
                                                <th>Currency</th>
                                                <th>Notes</th>
                                                <!-- <th>Billing Name</th>
                                                <th>Billing Phone</th>                                                 -->
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $db = new MysqliDb();
                                            // show order and order details
                                            $orders = $db->get('orders');
                                            foreach ($orders as $order) {
                                                echo "<tr>";
                                                echo "<td>" . $order['id'] . "</td>";
                                                echo "<td>" . $order['order_number'] . "</td>";
                                                // echo "<td>" . $order['user_id'] . "</td>";
                                                echo "<td>" . $order['order_type'] . "</td>";
                                                echo "<td>" . $order['status'] . "</td>";
                                                echo "<td>" . $order['payment_status'] . "</td>";
                                                echo "<td>" . $order['payment_method'] . "</td>";
                                                echo "<td>" . $order['transaction_id'] . "</td>";
                                                echo "<td>" . $order['subtotal'] . "</td>";
                                                echo "<td>" . $order['discount_amount'] . "</td>";
                                                // echo "<td>" . $order['coupon_id'] . "</td>";
                                                echo "<td>" . $order['tax_amount'] . "</td>";
                                                // echo "<td>" . $order['shipping_amount'] . "</td>";
                                                echo "<td>" . $order['total_amount'] . "</td>";
                                                echo "<td>" . $order['currency'] . "</td>";
                                                echo "<td>" . $order['notes'] . "</td>";
                                                // echo "<td>" . $order['billing_first_name']." ". $order['billing_last_name'] . "</td>";
                                                // echo "<td>" . $order['billing_phone'] . "</td>";
                                                echo "<td>" . $order['created_at'] . "</td>";
                                                echo "<td>" . $order['updated_at'] . "</td>";
                                                echo "<td>
                                                <a href='order-details.php?id=" . $order['id'] . "' class='btn btn-primary' title='View Order Details'><i class='bi bi-arrows-fullscreen'></i></a>
                                                <a href='invoice.php?id=" . $order['id'] . "' class='btn btn-secondary' title='View Invoice'><i class='bi bi-receipt'></i></a>
                                                </td>";
                                                echo "</tr>";
                                            }?>
                                            <!-- Data will be loaded via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
                </div>
            </div>

            <!-- Subcategory Modal -->
            <div class="modal fade" id="subcategoryModal" tabindex="-1" aria-labelledby="subcategoryModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="subcategoryModalLabel">Add New Subcategory</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="subcategoryForm" enctype="multipart/form-data">
                            <div class="modal-body">
                                <input type="hidden" id="subcategory_id" name="subcategory_id">

                                <div class="form-group">
                                    <label for="category_id" class="form-label">Category *</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <!-- Categories will be loaded via AJAX -->
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="name" class="form-label">Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>

                                <div class="form-group">
                                    <label for="slug" class="form-label">Slug *</label>
                                    <input type="text" class="form-control" id="slug" name="slug" required>
                                    <small class="form-text text-muted">URL-friendly version of the name</small>
                                </div>

                                <div class="form-group">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div id="imagePreview" class="preview-container" style="display: none;">
                                        <img id="previewImg" class="preview-image" src="" alt="Preview">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="saveBtn">Save Subcategory</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this subcategory?</p>
                            <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
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