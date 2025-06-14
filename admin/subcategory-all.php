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
                                <h5 class="mb-0">Subcategories Management</h5>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subcategoryModal" onclick="openAddModal()">
                                    <i class="fas fa-plus"></i> Add New Subcategory
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="subcategoriesTable" class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Image</th>
                                                <th>Name</th>
                                                <th>Category</th>
                                                <th>Slug</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Sort Order</th>
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
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#subcategoriesTable').DataTable({
                "ajax": {
                    "url": "subcategory-ajax.php",
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
                                return '<img src="<?= settings()['root'] ?>assets/subcategories/' + data + '" class="image-preview" alt="Image" style="max-width: 100px; max-height: 100px;">';
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
                        "data": "is_active",
                        "render": function(data, type, row) {
                            return data == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                        }
                    },
                    {
                        "data": "sort_order"
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return '<div class="table-actions">' +
                                '<button class="btn btn-sm btn-info me-1" onclick="editSubcategory(' + row.id + ')" title="Edit">' +
                                '<i class="fas fa-edit"></i></button>' +
                                '<button class="btn btn-sm btn-danger" onclick="deleteSubcategory(' + row.id + ')" title="Delete">' +
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
            $('#subcategoryForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                // Determine action: if subcategory_id has a value, it's an update
                var action = $('#subcategory_id').val() ? 'update' : 'create';
                formData.append('action', action);

                // Convert checkbox to int
                formData.set('is_active', $('#is_active').is(':checked') ? 1 : 0);

                $.ajax({
                    url: 'subcategory-ajax.php',
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
                            $('#subcategoryModal').modal('hide');
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
            window.deleteSubcategory = function(id) {
                deleteId = id;
                $('#deleteModal').modal('show');
            };

            $('#confirmDelete').on('click', function() {
                if (deleteId) {
                    $.ajax({
                        url: 'subcategory-ajax.php',
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

        function openAddModal() {
            $('#subcategoryForm')[0].reset();
            $('#subcategory_id').val('');
            $('#subcategoryModalLabel').text('Add New Subcategory');
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
            var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
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
            }, 5000);
        }
    </script>
</body>

</html>