<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../vendor/autoload.php';
?>
<?php require __DIR__.'/components/header.php'; ?>

    </head>
    <body class="sb-nav-fixed">
    <?php require __DIR__.'/components/navbar.php'; ?>
        <div id="layoutSidenav">
        <?php require __DIR__.'/components/sidebar.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                    <!-- changed content -->
        

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
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
            background-color: #ffc107;
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

    <div class="invoice-container">
        <div class="invoice-header">
            <img src="https://via.placeholder.com/150x50?text=Your+Logo" alt="Company Logo">
            <h2 class="mt-2">Invoice</h2>
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>From:</h5>
                <p>
                    <strong>Your Company</strong><br>
                    123 Business Street<br>
                    City, State, ZIP<br>
                    Email: contact@yourcompany.com<br>
                    Phone: (123) 456-7890
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <h5>Invoice Details:</h5>
                <p>
                    <strong>Invoice #:</strong> INV-001<br>
                    <strong>Date:</strong> July 01, 2025<br>
                    <strong>Due Date:</strong> July 15, 2025<br>
                    <strong>Status:</strong> <span class="badge badge-unpaid">Unpaid</span>
                </p>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>To:</h5>
                <p>
                    <strong>Client Name</strong><br>
                    456 Client Avenue<br>
                    City, State, ZIP<br>
                    Email: client@domain.com<br>
                    Phone: (123) 456-7890
                </p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>ShopEase</td>
                        <td>Full-featured e-commerce platform</td>
                        <td>1</td>
                        <td>Tk.00.00</td>
                        <td>Tk.00.00</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Hosting</td>
                        <td>1-year hosting subscription</td>
                        <td>1</td>
                        <td>Tk.0.00</td>
                        <td>Tk.0.00</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Support</td>
                        <td>6-month premium support package</td>
                        <td>1</td>
                        <td>Tk.0.00</td>
                        <td>Tk.0.00</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <td><strong>Subtotal:</strong></td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td><strong>Tax (10%):</strong></td>
                            <td class="text-end">0.00</td>
                        </tr>
                        <tr>
                            <td><strong>Total:</strong></td>
                            <td class="text-end"><strong>0.00</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-center no-print">
            <button class="btn btn-primary me-2" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            <button class="btn btn-danger"><i class="fas fa-file-pdf"></i> Export as PDF</button>
        </div>
        <div class="mt-4">
            <p><strong>Notes:</strong> Please make payment within 15 days. A 1.5% late fee will be applied to unpaid balances after the due date.</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

                    <!-- changed content  ends-->
                </main>
<!-- footer -->
<?php require __DIR__.'/components/footer.php'; ?>
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
