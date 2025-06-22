<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
$db = new MysqliDb();
$categories = $db->get('categories', null, ['id', 'name']);
/* var_dump($categories);
exit; */
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
                            <div class="card-header d-flex justify-content-st align-items-center">
                                <h5 class="mb-0">Products Management</h5>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openAddModal()">
                                    <i class="fas fa-plus"></i> Add New Product
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="productsTable" class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Subcategory</th>
                                                <th>Slug</th>
                                                <th>Description</th>
                                                <th>Short Description</th>
                                                <th>SKU</th>
                                                <th>Barcode</th>
                                                <th>Selling Price</th>
                                                <th>Cost Price</th>
                                                <th>Markup %</th>
                                                <th>Pricing Method</th>
                                                <th>Auto Update Price</th>
                                                <th>Stock Quantity</th>
                                                <th>Min Stock Level</th>
                                                <th>Weight</th>
                                                <th>Dimensions</th>
                                                <th>Active</th>
                                                <th>Hot Item</th>                                                
                                                <th>Actions</th>
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
            <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productModalLabel">Add New Product</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="productForm" enctype="multipart/form-data">
                            <div class="modal-body">
                                <!-- product entry form start -->
                               <input type="hidden" id="product_id" name="product_id">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <div id="imagePreview" class="preview-container" style="display: none;">
                                        <img id="previewImg" class="preview-image" src="" alt="Preview">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Select Category</option>
                                        <?php                                        
                                        foreach ($categories as $category):
                                        ?>
                                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="subcategory" class="form-label">Subcategory</label>
                                    <select class="form-select" id="subcategory" name="subcategory" required>
                                        <option value="">Select Subcategory</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug</label>
                                    <input type="text" class="form-control" id="slug" name="slug" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="short_description" class="form-label">Short Description</label>
                                    <textarea class="form-control" id="short_description" name="short_description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU</label>
                                    <input type="text" class="form-control" id="sku" name="sku" required>
                                </div>
                                <div class="mb-3">
                                    <label for="barcode" class="form-label">Barcode</label>
                                    <input type="text" class="form-control" id="barcode" name="barcode" required>
                                </div>
                                <div class="mb-3">
                                    <label for="selling_price" class="form-label">Selling Price</label>
                                    <input type="number" class="form-control" id="selling_price" name="selling_price" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cost_price" class="form-label">Cost Price</label>
                                    <input type="number" class="form-control" id="cost_price" name="cost_price" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="markup" class="form-label">Markup %</label>
                                    <input type="number" class="form-control" id="markup" name="markup" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pricing_method" class="form-label">Pricing Method</label>
                                    <select class="form-select" id="pricing_method" name="pricing_method" required>
                                        <option value="">Select Pricing Method</option>
                                        <option value="1">Fixed Pricing</option>
                                        <option value="2">Formula Pricing</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="auto_update_price" class="form-label">Auto Update Price</label>
                                    <select class="form-select" id="auto_update_price" name="auto_update_price" required>
                                        <option value="">Select Auto Update Price</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                                </div>
                                <div class="mb-3">
                                    <label for="min_stock_level" class="form-label">Min Stock Level</label>
                                    <input type="number" class="form-control" id="min_stock_level" name="min_stock_level" required>
                                </div>
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Weight</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dimensions" class="form-label">Dimensions</label>
                                    <input type="text" class="form-control" id="dimensions" name="dimensions" placeholder="e.g. 10x10x10 cm" required>
                                </div>
                                <div class="mb-3">
                                    <label for="active" class="form-label">Active</label>
                                    <select class="form-select" id="active" name="active" required>
                                        <option value="">Select Active</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="hot_item" class="form-label">Hot Item</label>
                                    <select class="form-select" id="hot_item" name="hot_item" required>
                                        <option value="">Select Hot Item</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <!-- product entry form end -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary" id="saveBtn">Save Product</button>
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
                            <p>Are you sure you want to delete this product?</p>
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
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#productsTable').DataTable({
                "ajax": {
                    "url": "product-ajax.php",
                    "type": "POST",
                    "data": {
                        action: 'fetch'
                    }
                },
                "columns": [{
                        "data": "id"
                    },
                    {
                        "data": "image",
                        "render": function(data, type, row) {
                            if (data) {
                                return '<img src="<?= settings()['root'] ?>assets/products/' + data + '" class="image-preview" alt="Image" style="max-width: 100px; max-height: 100px;">';
                            }
                            return '<span class="text-muted">No image</span>';
                        }
                    },
                    {
                        "data": "name"
                    },
                    {
                        "data": "category_name"
                    },
                    {
                        "data": "subcategory_name"
                    },
                    {
                        "data": "slug"
                    },
                    {
                        "data": "description",
                        "render": function(data, type, row) {
                            if (data && data.length > 50) {
                                return data.substring(0, 50) + '...';
                            }
                            return data || '';
                        }
                    },
                    {
                        "data": "short_description"
                    },
                    {
                        "data": "sku"
                    },
                    { data: "barcode"},
                    { data: "selling_price"},
                    { data: "cost_price"},
                    { data: "markup_percentage"},
                    { data: "pricing_method"},
                    { data: "auto_update_price"},
                    { data: "stock_quantity"},
                    { data: "min_stock_level"},
                    { data: "weight"},
                    { data: "dimensions"},
                    {
                        "data": "is_active",
                        "render": function(data, type, row) {
                            return data == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        }
                    },
                    {
                        "data": "is_hot_item",
                        "render": function(data, type, row) {
                            return data == 1 ? '<span class="badge bg-success">Featured</span>' : '<span class="badge bg-danger">Not Featured</span>';
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            // console.log(row);
                            return '<div class="table-actions">' +
                                '<button class="btn btn-sm btn-info me-1" onclick="editProduct(' + row.id + ')" title="Edit">' +
                                '<i class="fas fa-edit"></i></button>' +
                                '<button class="btn btn-sm btn-danger" onclick="deleteProduct(' + row.id + ')" title="Delete">' +
                                '<i class="fas fa-trash"></i></button>' +
                                '</div>';
                        }
                    }
                ],
                "responsive": true,
                "pageLength": 10,
                "order": [
                    [0, "desc"]
                ]
            });

            // Load categories for dropdown
            loadCategories();

            //loadSubcategories();
            $("#category").on('change', function() {
                var category_id = $(this).val();
                loadSubcategories(category_id);
            });
            function loadSubcategories(category_id) {
                $.ajax({
                    url: 'product-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'get_subcategories',
                        category_id: category_id
                    },
                    success: function(response) {
                        console.log(response);
                        
                        if(response.success){
                            $('#subcategory').empty();
                            $('#subcategory').html('<option value="">Select Subcategory</option>');
                            response.data.forEach(function(subcategory) {
                                $('#subcategory').append('<option value="' + subcategory.id + '">' + subcategory.name + '</option>');
                            })
                        }
                        // $('#subcategory').html(response);
                    }
                });
            }

            // Generate slug from name
            $('#name').on('input', function() {
                var name = $(this).val();
                var slug = name.toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove special characters
                    .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
                    .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
                $('#slug').val(slug);
            });

            // Image preview
            $('#image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewImg').attr('src', e.target.result).attr("width", "100");
                        $('#imagePreview').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreview').hide();
                }
            });

            // Form submission
            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                // Determine action: if subcategory_id has a value, it's an update
                var action = $('#product_id').val() ? 'update' : 'create';
                formData.append('action', action);

                // Convert checkbox to int
                formData.set('is_active', $('#is_active').is(':checked') ? 1 : 0);

                $.ajax({
                    url: 'product-ajax.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#saveBtn').prop('disabled', true).text('Saving...');
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.success) {
                            $('#productModal').modal('hide');
                            table.ajax.reload();
                            showAlert('success', response.message);
                        } else {
                            showAlert('danger', response.message);
                        }
                    },
                    error: function() {
                        showAlert('danger', 'An error occurred. Please try again.');
                    },
                    complete: function() {
                        $('#saveBtn').prop('disabled', false).text('Save Subcategory');
                    }
                });
            });

            // Delete confirmation
            var deleteId = null;
            window.deleteProduct = function(id) {
                deleteId = id;
                $('#deleteModal').modal('show');
            };

            $('#confirmDelete').on('click', function() {
                if (deleteId) {
                    $.ajax({
                        url: 'product-ajax.php',
                        type: 'POST',
                        data: {
                            action: 'delete',
                            id: deleteId
                        },
                        success: function(response) {
                            $('#deleteModal').modal('hide');
                            if (response.success) {
                                table.ajax.reload();
                                showAlert('success', response.message);
                            } else {
                                showAlert('danger', response.message);
                            }
                        },
                        error: function() {
                            showAlert('danger', 'An error occurred. Please try again.');
                        }
                    });
                }
            });
        });

        function loadCategories() {
            $.ajax({
                url: 'subcategory-ajax.php',
                type: 'POST',
                data: {
                    action: 'get_categories'
                },
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Select Category</option>';
                        response.data.forEach(function(category) {
                            options += '<option value="' + category.id + '">' + category.name + '</option>';
                        });
                        $('#category_id').html(options);
                    }
                }
           
            });
        }

        //loadSubcategories
        function loadSubCategories() {
            $.ajax({
                url: 'subcategory-ajax.php',
                type: 'POST',
                data: {
                    action: 'get_subcategories'
                },
                success: function(response) {
                    if (response.success) {
                        var options = '<option value="">Select Subcategory</option>';
                        response.data.forEach(function(subcategory) {
                            options += '<option value="' + subcategory.id + '">' + subcategory.name + '</option>';
                        });
                        $('#subcategory_id').html(options);
                    }
                }
            });
        }

        function openAddModal() {
            $('#productForm')[0].reset();
            $('#product_id').val('');
            $('#productModalLabel').text('Add New Product');
            $('#imagePreview').hide();
            $('#is_active').prop('checked', true);
        }

        function editSubcategory(id) {
            $.ajax({
                url: 'subcategory-ajax.php',
                type: 'POST',
                data: {
                    action: 'get_single',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        $('#subcategory_id').val(data.id);
                        $('#category_id').val(data.category_id);
                        $('#name').val(data.name);
                        $('#slug').val(data.slug);
                        $('#description').val(data.description);
                        $('#sort_order').val(data.sort_order);
                        $('#is_active').prop('checked', data.is_active == 1);

                        if (data.image) {
                            $('#previewImg').attr('src', '<?= settings()['root'] ?>' + 'assets/subcategories/' + data.image).attr("width", "100");
                            $('#imagePreview').show();
                        } else {
                            $('#imagePreview').hide();
                        }

                        $('#subcategoryModalLabel').text('Edit Subcategory');
                        $('#subcategoryModal').modal('show');
                    } else {
                        showAlert('danger', response.message);
                    }
                }
            });
        }

        function showAlert(type, message) {
            Swal.fire({
                            position: "top-end",
                            icon: type,
                            title: message,
                            showConfirmButton: false,
                            timer: 1500
                        });
            /* var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>';

            // Remove existing alerts
            $('.alert').remove();

            // Add new alert at the top of the card body
            $('.card-body').prepend(alertHtml);

            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000); */
        }
    </script>
</body>

</html>