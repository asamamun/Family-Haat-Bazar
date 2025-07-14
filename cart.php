<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
use App\User;
use App\model\Category;
$db = new MysqliDb();
$page = "Cart";
?>
<?php require __DIR__ . '/components/header.php';?>
<!-- content start -->
<div class="container my-5">
    <h2 class="text-center mb-4 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Your Shopping Cart</h2>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div id="cartEmptyMessage" class="alert alert-info text-center d-none">
                <i class="fas fa-info-circle me-2"></i>Your cart is empty. Start shopping now!
            </div>
            <table class="table table-hover table-bordered d-none" id="cartTableMain">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Item</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Price</th>
                        <th scope="col">Total</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="cartTable">
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Grand Total</td>
                        <td id="grandTotal" class="fw-bold">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="text-center mt-4">
                <a href="place_order.php" class="btn btn-success btn-lg"><i class="fas fa-check-circle me-2"></i>Proceed to Checkout</a>
            </div>
        </div>
    </div>
</div>
<!-- content end -->
<?php require __DIR__ . '/components/footer.php'; 
$db->disconnect();
?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
.card {
    border-radius: 10px;
}
.table th, .table td {
    vertical-align: middle;
}
.btn-success {
    padding: 10px 30px;
    font-size: 1.1rem;
}
.alert-info {
    border-radius: 8px;
    padding: 20px;
}
.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}
</style>
</body>
</html>