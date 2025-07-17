<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';

// Database connection using MysqliDb
try {
    $db = new MysqliDb('localhost', 'root', '', 'haatbazar');
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch products from the database
$products = $db->get('products');

// Fetch categories
$categories = $db->get('categories', null, ['id', 'name']);

// Fetch subcategories
$subcategories = $db->get('subcategories', null, ['id', 'name', 'category_id']);
?>

<?php require __DIR__ . '/components/header.php';?>

<style>
#productGrid {
    max-height: 400px;
    overflow-y: auto;
}
.product-card {
    transition: transform 0.2s ease;
}
.product-card:hover {
    transform: scale(1.03);
}
.modal-body .form-label {
    font-weight: 500;
}
.modal-body input, .modal-body select {
    border-radius: 0.375rem;
}
</style>

<body class="sb-nav-fixed">
<?php require __DIR__ . '/components/navbar.php';?>
<div id="layoutSidenav">
    <?php require __DIR__ . '/components/sidebar.php';?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Point of Sale</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard / POS</li>
                </ol>

                <div class="row">
                    <!-- Product Selection -->
                     <!-- barcode section -->
                      <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-qrcode me-1"></i>
                                Scan Product
                            </div>
                            <div class="card-body">
                                <video id="video" width="100%" height="120" style="border:1px solid #000"></video>
                                <input type="text" id="scannedBarcode" class="form-control mt-2" placeholder="Barcode will appear here">
                            </div>
                        </div>
                      </div>

                      <!-- Today's Sales Summary -->
                      <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-chart-line me-1"></i>
                                Today's Sales
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h4 class="text-success mb-1" id="todaysSales">$0.00</h4>
                                            <small class="text-muted">Total Sales</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-primary mb-1" id="todaysOrders">0</h4>
                                        <small class="text-muted">Orders</small>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h6 class="text-info mb-1" id="avgTransaction">$0.00</h6>
                                            <small class="text-muted">Avg. Sale</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h6 class="text-warning mb-1" id="itemsSold">0</h6>
                                        <small class="text-muted">Items Sold</small>
                                    </div>
                                </div>
                                <button class="btn btn-outline-success btn-sm w-100 mt-2" onclick="refreshStats()">
                                    <i class="fas fa-sync-alt me-1"></i>Refresh
                                </button>
                            </div>
                        </div>
                      </div>

                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-shopping-basket me-1"></i>
                                Products
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search by ID or Name..." id="productSearch">
                                            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="categoryFilter">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?=htmlspecialchars($category['id'])?>">
                                                    <?=htmlspecialchars($category['name'])?>
                                                </option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" id="subcategoryFilter">
                                            <option value="">All Subcategories</option>
                                            <?php foreach ($subcategories as $subcategory): ?>
                                                <option value="<?=htmlspecialchars($subcategory['id'])?>"
                                                        data-category="<?=htmlspecialchars($subcategory['category_id'])?>">
                                                    <?=htmlspecialchars($subcategory['name'])?>
                                                </option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row" id="productGrid">
                                    <?php foreach ($products as $product): ?>
                                        <div class="col-md-3 mb-3 product-item"
                                             data-id="<?=htmlspecialchars($product['id'])?>"
                                             data-name="<?=htmlspecialchars(strtolower($product['name']))?>"
                                             data-category="<?=htmlspecialchars($product['category_id'])?>"
                                             data-subcategory="<?=htmlspecialchars($product['subcategory_id'] ?? '')?>">
                                            <div class="card product-card">
                                            <img src="<?=settings()['root']?>assets/products/<?=htmlspecialchars($product['image'] ?? '')?>" class="card-img-top" alt="<?=htmlspecialchars($product['name'])?>">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?=htmlspecialchars($product['name'])?> (ID: <?=$product['id']?>)</h6>
                                                    <p class="card-text">$<?=number_format($product['selling_price'], 2)?></p>
                                                    <button class="btn btn-sm btn-primary add-to-cart"
                                                            data-id="<?=$product['id']?>"
                                                            data-price="<?=$product['selling_price']?>"
                                                            data-name="<?=htmlspecialchars($product['name'])?>">
                                                        Add
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart and Checkout -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-shopping-cart me-1"></i>
                                Current Sale
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="cartTable">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartBody">
                                            <!-- Cart items will appear here -->
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <button class="btn btn-danger w-100" id="clearCart">Clear</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button class="btn btn-warning w-100" id="holdCart">Hold</button>
                                    </div>
                                </div>

                                <!-- Discount and Notes Section -->
                                <div class="mt-3">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Discount Amount</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="discountInput" placeholder="0.00" step="0.01" min="0">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Discount %</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control" id="discountPercent" placeholder="0" step="0.1" min="0" max="100">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">Order Notes</label>
                                        <textarea class="form-control form-control-sm" id="orderNotes" rows="2" placeholder="Add notes for this order..."></textarea>
                                    </div>
                                </div>

                                <div class="mt-3 p-3 bg-light rounded" id="cartSummary">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal:</span>
                                        <span id="subtotal">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (8%):</span>
                                        <span id="tax">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Discount:</span>
                                        <span id="discount" class="text-danger">-$0.00</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span id="total">$0.00</span>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-success w-100 py-3" id="checkoutBtn">
                                        <i class="fas fa-cash-register me-2"></i> Process Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Modal -->
                <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Process Payment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Total Amount</label>
                                    <input type="text" class="form-control" id="modalTotal" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select class="form-select" id="paymentMethod">
                                        <option>Cash</option>
                                        <option>Credit Card</option>
                                        <option>Debit Card</option>
                                        <option>Mobile Payment</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Amount Tendered</label>
                                    <input type="number" class="form-control" id="amountTendered" placeholder="0.00" step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Change</label>
                                    <input type="text" class="form-control" id="change" readonly>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="completeSale">Complete Sale</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php require __DIR__ . '/components/footer.php';?>
    </div>
</div>

<script src="<?=settings()['adminpage']?>assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="<?=settings()['adminpage']?>assets/js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
<script src="<?=settings()['adminpage']?>assets/js/datatables-simple-demo.js"></script>
<script src="<?=settings()['adminpage']?>assets/js/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Define cart and updateCart in global scope
let cart = [];

function updateCart() {
    const cartBody = document.getElementById('cartBody');
    cartBody.innerHTML = '';

    let subtotal = 0;
    cart.forEach(item => {
        const total = item.price * item.quantity;
        subtotal += total;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td><input type="number" class="form-control form-control-sm qty-input" value="${item.quantity}" min="1" data-id="${item.id}"></td>
            <td>$${item.price.toFixed(2)}</td>
            <td>$${total.toFixed(2)}</td>
            <td><button class="btn btn-sm btn-danger remove-item" data-id="${item.id}"><i class="fas fa-trash"></i></button></td>
        `;
        cartBody.appendChild(row);
    });

    // Calculate discount
    const discountAmount = parseFloat(document.getElementById('discountInput').value) || 0;
    const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;

    let totalDiscount = discountAmount;
    if (discountPercent > 0) {
        totalDiscount = Math.max(totalDiscount, subtotal * (discountPercent / 100));
    }

    // Ensure discount doesn't exceed subtotal
    totalDiscount = Math.min(totalDiscount, subtotal);

    const tax = subtotal * 0.08;
    const total = subtotal + tax - totalDiscount;

    document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
    document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
    document.getElementById('discount').textContent = `-$${totalDiscount.toFixed(2)}`;
    document.getElementById('total').textContent = `$${total.toFixed(2)}`;
    document.getElementById('modalTotal').value = `$${total.toFixed(2)}`;

    // Update quantity
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const id = this.dataset.id;
            const newQty = parseInt(this.value);
            const item = cart.find(item => item.id === id);
            if (item && newQty > 0) {
                item.quantity = newQty;
                updateCart();
            }
        });
    });

    // Remove item
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            cart = cart.filter(item => item.id !== id);
            updateCart();
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Search and filter products
    const productSearch = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const subcategoryFilter = document.getElementById('subcategoryFilter');
    const productItems = document.querySelectorAll('.product-item');

    // Filter subcategories based on selected category
    categoryFilter.addEventListener('change', function() {
        const selectedCategory = this.value;
        const subcategoryOptions = document.querySelectorAll('#subcategoryFilter option');
        subcategoryOptions.forEach(option => {
            if (option.value === '' || !selectedCategory || option.dataset.category === selectedCategory) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
        subcategoryFilter.value = ''; // Reset subcategory
        filterProducts();
    });

    productSearch.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);
    subcategoryFilter.addEventListener('change', filterProducts);

    function filterProducts() {
        const searchTerm = productSearch.value.toLowerCase();
        const category = categoryFilter.value;
        const subcategory = subcategoryFilter.value;

        productItems.forEach(item => {
            const id = item.dataset.id;
            const name = item.dataset.name;
            const itemCategory = item.dataset.category;
            const itemSubcategory = item.dataset.subcategory;

            const matchesSearch = searchTerm === '' ||
                                name.includes(searchTerm) ||
                                id === searchTerm;
            const matchesCategory = !category || itemCategory === category;
            const matchesSubcategory = !subcategory || itemSubcategory === subcategory;

            item.style.display = matchesSearch && matchesCategory && matchesSubcategory ? '' : 'none';
        });
    }

    // Add to cart
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);

            const existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({ id, name, price, quantity: 1 });
            }
            updateCart();
        });
    });

    // Clear cart
    document.getElementById('clearCart').addEventListener('click', function() {
        cart = [];
        document.getElementById('discountInput').value = '';
        document.getElementById('discountPercent').value = '';
        document.getElementById('orderNotes').value = '';
        updateCart();
    });

    // Hold cart (store in sessionStorage)
    document.getElementById('holdCart').addEventListener('click', function() {
        sessionStorage.setItem('heldCart', JSON.stringify(cart));
        alert('Cart held successfully!');
    });

    // Discount input event listeners
    document.getElementById('discountInput').addEventListener('input', function() {
        // Clear percentage when amount is entered
        if (this.value) {
            document.getElementById('discountPercent').value = '';
        }
        updateCart();
    });

    document.getElementById('discountPercent').addEventListener('input', function() {
        // Clear amount when percentage is entered
        if (this.value) {
            document.getElementById('discountInput').value = '';
        }
        updateCart();
    });

    // Checkout
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        if (cart.length === 0) {
            alert('Cart is empty!');
            return;
        }
        var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
    });

    // Amount tendered calculation
    document.getElementById('amountTendered').addEventListener('input', function() {
        const total = parseFloat(document.getElementById('modalTotal').value.replace('$', '')) || 0;
        const tendered = parseFloat(this.value) || 0;
        const change = tendered - total;
        document.getElementById('change').value = `$${change.toFixed(2)}`;
    });

    // Complete sale
    document.getElementById('completeSale').addEventListener('click', function(event) {
        event.preventDefault();
        const tendered = parseFloat(document.getElementById('amountTendered').value) || 0;
        const total = parseFloat(document.getElementById('modalTotal').value.replace('$', '')) || 0;
        if (tendered < total) {
            alert('Insufficient amount tendered!');
            return;
        }

        // Get discount and notes
        const discountAmount = parseFloat(document.getElementById('discountInput').value) || 0;
        const discountPercent = parseFloat(document.getElementById('discountPercent').value) || 0;
        const orderNotes = document.getElementById('orderNotes').value.trim();

        // Calculate final discount
        const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace('$', ''));
        let finalDiscount = discountAmount;
        if (discountPercent > 0) {
            finalDiscount = Math.max(finalDiscount, subtotal * (discountPercent / 100));
        }
        finalDiscount = Math.min(finalDiscount, subtotal);

        // Prepare data to send to process_order.php
        let data = {
            subtotal: subtotal,
            discount_amount: finalDiscount,
            tax_amount: parseFloat(document.getElementById('tax').textContent.replace('$', '')),
            shipping_amount: 0.00,
            total_amount: total,
            notes: orderNotes || null,
            items: cart,
            payment_method: document.getElementById('paymentMethod').value,
            amount_tendered: tendered
        };

        // Send data via AJAX to process_order.php
        $.ajax({
            url: 'process_order.php',
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                console.log(response);
                if(response.success){
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: response.message + ", Order # " + response.order_number,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    // Clear everything
                    cart = [];
                    document.getElementById('discountInput').value = '';
                    document.getElementById('discountPercent').value = '';
                    document.getElementById('orderNotes').value = '';
                    document.getElementById('amountTendered').value = '';
                    updateCart();
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to process order'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while processing the order.'
                });
            }
        });
    });

    // Load today's sales stats on page load
    loadTodaysStats();
});

function refreshStats() {
    loadTodaysStats();
}

function loadTodaysStats() {
    $.ajax({
        url: 'apis/todays-sales.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#todaysSales').text('$' + parseFloat(data.total_sales || 0).toFixed(2));
            $('#todaysOrders').text(data.total_orders || 0);
            $('#avgTransaction').text('$' + parseFloat(data.avg_transaction || 0).toFixed(2));
            $('#itemsSold').text(data.items_sold || 0);
        },
        error: function() {
            console.log('Error loading today\'s stats');
        }
    });
}
</script>

<!-- Barcode scanner -->
<script type="module">
    import { BrowserMultiFormatReader } from "https://cdn.jsdelivr.net/npm/@zxing/library@latest/+esm";

    const codeReader = new BrowserMultiFormatReader();
    window.onload = async () => {
        const videoInputDevices = await codeReader.listVideoInputDevices();
        const selectedDeviceId = videoInputDevices[0]?.deviceId;

        codeReader.decodeFromVideoDevice(selectedDeviceId, 'video', (result, err) => {
            if (result) {
                console.log(result);
                // result.text has barcode
                $.ajax({
                    url: "apis/product.php",
                    method: "GET",
                    data: { code: result.text },
                    success: function(data) {
                        console.log(data);
                        data = JSON.parse(data);
                        const id = data.id;
                        const name = data.name;
                        const price = parseFloat(data.selling_price);

                        const existingItem = cart.find(item => item.id === id);
                        if (existingItem) {
                            existingItem.quantity++;
                        } else {
                            cart.push({ id, name, price, quantity: 1 });
                        }
                        updateCart(); // Now accessible
                    },
                    error: function() {
                        console.error('Error fetching product data');
                    }
                });
            }
        });
    };
</script>
<!-- Barcode scanner end -->
</body>
</html>