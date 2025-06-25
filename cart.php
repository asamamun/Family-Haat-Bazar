<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/vendor/autoload.php';
use App\User;
use App\model\Category;
// use App\db;
// $conn = db::connect();
$db = new MysqliDb ();
$page = "Home";
?>
<?php require __DIR__ . '/components/header.php';?>
<!-- content start -->
<div class="container">
    <h2 class="text-center my-4">Your Cart</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="cartTable">
           
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Grand Total</td>
                <td id="grandTotal">0.00</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    
</div>
<!-- content end -->
<?php require __DIR__ . '/components/footer.php'; 
$db->disconnect();
?>
<script>
    $(document).ready(function() {
        function populateItems(items) {
            $("#cartTable").html("");
            items.forEach(item => {
                $("#cartTable").append(`
                    <tr>
                        <td>${item.name}</td>
                        <td> <input type="number" class="form-control form-control-sm qty-input" data-id="${item.id}" value="${item.quantity}" min="1"></td>
                        <td>${item.price}</td>
                        <td>${ (item.quantity * item.price).toFixed(2) }</td> 
                        <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button></td>
                    </tr>
                `);
            });
        }
        let cart = new Cart();
        $("#cartCount").text(cart.getTotalItems());
        $("#grandTotal").text(cart.getTotalPrice());
        let allitems = cart.getSummary();
        populateItems(allitems.items);
/*         allitems.items.forEach(item => {
            $("#cartTable").append(`
                <tr>
                    <td>${item.name}</td>
                    <td> <input type="number" class="form-control form-control-sm qty-input" value="${item.quantity}" min="1"></td>
                    <td>${item.price}</td>
                    <td>${ (item.quantity * item.price).toFixed(2) }</td> 
                    <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button></td>
                </tr>
            `);
        }); */
        //remove item
        $("#cartTable").on("click",".remove-item", function() {
            let id = $(this).data('id');
            let items = cart.removeItem(id);
            console.log(items);
            $("#grandTotal").text(cart.getTotalPrice());
            $("#cartCount").text(cart.getTotalItems());
            $("#cartTable").html("");
            populateItems(items);
/*             items.forEach(item => {
                $("#cartTable").append(`
                    <tr>
                        <td>${item.name}</td>
                        <td> <input type="number" class="form-control form-control-sm qty-input" data-id="${item.id}" value="${item.quantity}" min="1"></td>
                        <td>${item.price}</td>
                        <td>${ (item.quantity * item.price).toFixed(2) }</td> 
                        <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button></td>
                    </tr>
                `);
            }); */
        });
        //quantity change
        $("#cartTable").on("change",".qty-input", function() {
            let id = $(this).data('id');
            let quantity = $(this).val();
            let items = cart.editItem(id, quantity);
            $("#grandTotal").text(cart.getTotalPrice());
            $("#cartCount").text(cart.getTotalItems());
            $("#cartTable").html("");
            populateItems(items);
/*             items.forEach(item => {
                $("#cartTable").append(`
                    <tr>
                        <td>${item.name}</td>
                        <td> <input type="number" class="form-control form-control-sm qty-input" value="${item.quantity}" min="1"></td>
                        <td>${item.price}</td>
                        <td>${ (item.quantity * item.price).toFixed(2) }</td> 
                        <td><button class="btn btn-danger btn-sm remove-item" data-id="${item.id}">Remove</button></td>
                    </tr>
                `);
            }); */
        });
    });
</script>
</body>
</html>