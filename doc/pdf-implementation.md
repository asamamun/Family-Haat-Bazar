# PDF Invoice Implementation Guide
## Family Haat Bazar - PDF Invoice Generation

### 🎯 **Objective**
Generate professional PDF invoices for order confirmations that can be emailed to customers and stored for records.

---

## 📄 **Recommended PDF Solutions**

### **Method 1: TCPDF (Recommended)**

#### **Why TCPDF?**
- ✅ **Pure PHP** - No external dependencies
- ✅ **Unicode Support** - Perfect for Bengali text
- ✅ **Rich Features** - Headers, footers, images, tables
- ✅ **Lightweight** - Fast generation
- ✅ **Well Documented** - Extensive examples
- ✅ **Free & Open Source** - No licensing costs

#### **Installation:**
```bash
composer require tecnickcom/tcpdf
```

#### **Basic Implementation:**
```php
<?php
require_once('vendor/autoload.php');

function generateInvoicePDF($orderId, $orderData) {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('Haat Bazar');
    $pdf->SetAuthor('Haat Bazar');
    $pdf->SetTitle('Invoice - ' . $orderData['order_number']);
    $pdf->SetSubject('Order Invoice');
    $pdf->SetKeywords('Invoice, Order, Haat Bazar');
    
    // Set default header data
    $pdf->SetHeaderData('', 0, 'HAAT BAZAR', 'Invoice - ' . $orderData['order_number']);
    
    // Set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    // Set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    
    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    
    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    
    // Add a page
    $pdf->AddPage();
    
    // Generate invoice content
    $html = generateInvoiceHTML($orderData);
    
    // Print text using writeHTMLCell()
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Save PDF to file
    $filename = 'invoices/invoice-' . $orderData['order_number'] . '.pdf';
    $pdf->Output($filename, 'F');
    
    return $filename;
}
```

---

### **Method 2: DOMPDF (Alternative)**

#### **Installation:**
```bash
composer require dompdf/dompdf
```

#### **Implementation:**
```php
<?php
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

function generateInvoicePDFWithDompdf($orderId, $orderData) {
    // Configure Dompdf
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isRemoteEnabled', true);
    
    // Initialize Dompdf
    $dompdf = new Dompdf($options);
    
    // Generate HTML content
    $html = generateInvoiceHTML($orderData);
    
    // Load HTML content
    $dompdf->loadHtml($html);
    
    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');
    
    // Render PDF
    $dompdf->render();
    
    // Save to file
    $filename = 'invoices/invoice-' . $orderData['order_number'] . '.pdf';
    file_put_contents($filename, $dompdf->output());
    
    return $filename;
}
```

---

### **Method 3: mPDF (Feature Rich)**

#### **Installation:**
```bash
composer require mpdf/mpdf
```

#### **Implementation:**
```php
<?php
require_once 'vendor/autoload.php';

function generateInvoicePDFWithMpdf($orderId, $orderData) {
    // Create mPDF instance
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'orientation' => 'P',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9
    ]);
    
    // Set document properties
    $mpdf->SetTitle('Invoice - ' . $orderData['order_number']);
    $mpdf->SetAuthor('Haat Bazar');
    $mpdf->SetCreator('Haat Bazar System');
    
    // Generate HTML content
    $html = generateInvoiceHTML($orderData);
    
    // Write HTML to PDF
    $mpdf->WriteHTML($html);
    
    // Save to file
    $filename = 'invoices/invoice-' . $orderData['order_number'] . '.pdf';
    $mpdf->Output($filename, 'F');
    
    return $filename;
}
```

---

## 🎨 **Professional Invoice HTML Template**

### **Complete Invoice HTML:**
```php
function generateInvoiceHTML($orderData) {
    // Get order items
    $db = new MysqliDb();
    $orderItems = $db->where('order_id', $orderData['id'])->get('order_items');
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2E7D32; padding-bottom: 20px; }
            .company-name { font-size: 24px; font-weight: bold; color: #2E7D32; margin-bottom: 5px; }
            .company-info { font-size: 10px; color: #666; }
            .invoice-title { font-size: 20px; font-weight: bold; margin: 20px 0; text-align: center; background: #f0f0f0; padding: 10px; }
            .invoice-details { margin: 20px 0; }
            .invoice-details table { width: 100%; }
            .invoice-details td { padding: 5px 0; }
            .billing-shipping { margin: 20px 0; }
            .billing-shipping table { width: 100%; }
            .billing-shipping td { vertical-align: top; padding: 10px; }
            .address-box { border: 1px solid #ddd; padding: 15px; background: #f9f9f9; }
            .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .items-table th { background: #2E7D32; color: white; font-weight: bold; }
            .items-table .number { text-align: right; }
            .totals { margin-top: 20px; }
            .totals table { width: 100%; }
            .totals td { padding: 5px 0; }
            .total-row { font-weight: bold; font-size: 14px; background: #f0f0f0; }
            .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
            .thank-you { text-align: center; margin: 20px 0; font-size: 16px; color: #2E7D32; font-weight: bold; }
        </style>
    </head>
    <body>
        <!-- Header -->
        <div class="header">
            <div class="company-name">HAAT BAZAR</div>
            <div class="company-info">
                Your Family Shopping Destination<br>
                📍 Dhaka, Bangladesh | 📞 +880 1700-000000 | 📧 info@haatbazar.com<br>
                🌐 https://coders64.xyz/projects/haatbazar/
            </div>
        </div>
        
        <!-- Invoice Title -->
        <div class="invoice-title">INVOICE</div>
        
        <!-- Invoice Details -->
        <div class="invoice-details">
            <table>
                <tr>
                    <td style="width: 50%;"><strong>Invoice Number:</strong> ' . htmlspecialchars($orderData['order_number']) . '</td>
                    <td style="width: 50%;"><strong>Invoice Date:</strong> ' . date('F d, Y', strtotime($orderData['created_at'])) . '</td>
                </tr>
                <tr>
                    <td><strong>Order Date:</strong> ' . date('F d, Y', strtotime($orderData['created_at'])) . '</td>
                    <td><strong>Payment Method:</strong> ' . ucfirst($orderData['payment_method']) . '</td>
                </tr>
                <tr>
                    <td><strong>Payment Status:</strong> ' . ucfirst($orderData['payment_status']) . '</td>
                    <td><strong>Order Status:</strong> ' . ucfirst($orderData['status']) . '</td>
                </tr>
            </table>
        </div>
        
        <!-- Billing and Shipping -->
        <div class="billing-shipping">
            <table>
                <tr>
                    <td style="width: 50%;">
                        <div class="address-box">
                            <h3 style="margin-top: 0; color: #2E7D32;">📄 BILL TO:</h3>
                            <strong>' . htmlspecialchars($orderData['billing_first_name'] . ' ' . $orderData['billing_last_name']) . '</strong><br>';
    
    if (!empty($orderData['billing_company'])) {
        $html .= htmlspecialchars($orderData['billing_company']) . '<br>';
    }
    
    $html .= htmlspecialchars($orderData['billing_address_line_1']) . '<br>';
    
    if (!empty($orderData['billing_address_line_2'])) {
        $html .= htmlspecialchars($orderData['billing_address_line_2']) . '<br>';
    }
    
    $html .= htmlspecialchars($orderData['billing_city'] . ', ' . $orderData['billing_state'] . ' ' . $orderData['billing_postal_code']) . '<br>
                            ' . htmlspecialchars($orderData['billing_country']) . '<br>
                            📞 ' . htmlspecialchars($orderData['billing_phone']) . '
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="address-box">
                            <h3 style="margin-top: 0; color: #2E7D32;">🚚 SHIP TO:</h3>
                            <strong>' . htmlspecialchars($orderData['shipping_first_name'] . ' ' . $orderData['shipping_last_name']) . '</strong><br>';
    
    if (!empty($orderData['shipping_company'])) {
        $html .= htmlspecialchars($orderData['shipping_company']) . '<br>';
    }
    
    $html .= htmlspecialchars($orderData['shipping_address_line_1']) . '<br>';
    
    if (!empty($orderData['shipping_address_line_2'])) {
        $html .= htmlspecialchars($orderData['shipping_address_line_2']) . '<br>';
    }
    
    $html .= htmlspecialchars($orderData['shipping_city'] . ', ' . $orderData['shipping_state'] . ' ' . $orderData['shipping_postal_code']) . '<br>
                            ' . htmlspecialchars($orderData['shipping_country']) . '<br>
                            📞 ' . htmlspecialchars($orderData['shipping_phone']) . '
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Order Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">SKU</th>
                    <th style="width: 40%;">Product Name</th>
                    <th style="width: 10%;">Qty</th>
                    <th style="width: 15%;">Unit Price</th>
                    <th style="width: 15%;">Total</th>
                </tr>
            </thead>
            <tbody>';
    
    $itemNumber = 1;
    foreach ($orderItems as $item) {
        $html .= '
                <tr>
                    <td class="number">' . $itemNumber++ . '</td>
                    <td>' . htmlspecialchars($item['product_sku']) . '</td>
                    <td>' . htmlspecialchars($item['product_name']) . '</td>
                    <td class="number">' . $item['quantity'] . '</td>
                    <td class="number">৳' . number_format($item['unit_price'], 2) . '</td>
                    <td class="number">৳' . number_format($item['total_price'], 2) . '</td>
                </tr>';
    }
    
    $html .= '
            </tbody>
        </table>
        
        <!-- Totals -->
        <div class="totals">
            <table style="width: 50%; margin-left: auto;">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="number"><strong>৳' . number_format($orderData['subtotal'], 2) . '</strong></td>
                </tr>';
    
    if ($orderData['discount_amount'] > 0) {
        $html .= '
                <tr>
                    <td>Discount:</td>
                    <td class="number" style="color: red;">-৳' . number_format($orderData['discount_amount'], 2) . '</td>
                </tr>';
    }
    
    if ($orderData['tax_amount'] > 0) {
        $html .= '
                <tr>
                    <td>Tax:</td>
                    <td class="number">৳' . number_format($orderData['tax_amount'], 2) . '</td>
                </tr>';
    }
    
    if ($orderData['shipping_amount'] > 0) {
        $html .= '
                <tr>
                    <td>Shipping:</td>
                    <td class="number">৳' . number_format($orderData['shipping_amount'], 2) . '</td>
                </tr>';
    }
    
    $html .= '
                <tr class="total-row">
                    <td><strong>TOTAL:</strong></td>
                    <td class="number"><strong>৳' . number_format($orderData['total_amount'], 2) . '</strong></td>
                </tr>
            </table>
        </div>';
    
    if (!empty($orderData['notes'])) {
        $html .= '
        <div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-left: 4px solid #2E7D32;">
            <strong>Order Notes:</strong><br>
            ' . nl2br(htmlspecialchars($orderData['notes'])) . '
        </div>';
    }
    
    $html .= '
        <!-- Thank You Message -->
        <div class="thank-you">
            🙏 Thank you for shopping with Haat Bazar! 🙏
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Terms & Conditions:</strong></p>
            <p>• Payment is due within 30 days of invoice date</p>
            <p>• Returns accepted within 7 days with original receipt</p>
            <p>• For support, contact us at support@haatbazar.com or +880 1700-000000</p>
            <p>• Visit us online: https://coders64.xyz/projects/haatbazar/</p>
            <hr style="margin: 10px 0;">
            <p>&copy; ' . date('Y') . ' Haat Bazar. All rights reserved. | Generated on ' . date('F d, Y \a\t g:i A') . '</p>
        </div>
    </body>
    </html>';
    
    return $html;
}
```

---

## 🔧 **Integration with processOrder.php**

### **Add PDF Generation Function:**
```php
// Add this function to processOrder.php or create a separate file

function generateOrderInvoicePDF($orderId) {
    try {
        $db = new MysqliDb();
        
        // Get order details
        $order = $db->where('id', $orderId)->getOne('orders');
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Create invoices directory if it doesn't exist
        $invoiceDir = __DIR__ . '/../invoices/';
        if (!is_dir($invoiceDir)) {
            mkdir($invoiceDir, 0755, true);
        }
        
        // Generate PDF using TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Haat Bazar');
        $pdf->SetAuthor('Haat Bazar');
        $pdf->SetTitle('Invoice - ' . $order['order_number']);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Generate HTML content
        $html = generateInvoiceHTML($order);
        
        // Write HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Save PDF
        $filename = $invoiceDir . 'invoice-' . $order['order_number'] . '.pdf';
        $pdf->Output($filename, 'F');
        
        return $filename;
        
    } catch (Exception $e) {
        error_log('PDF Generation Error: ' . $e->getMessage());
        return false;
    }
}
```

### **Update processOrder.php:**
```php
// After successful order creation and before response
if ($order_id) {
    // ... existing code ...
    
    $db->commit();
    
    // Generate PDF invoice
    $pdfPath = generateOrderInvoicePDF($order_id);
    
    if ($pdfPath) {
        // Send email with PDF attachment (see email-implementation.md)
        $customerEmail = $user ? $user['email'] : $_POST['billing_email'] ?? '';
        if ($customerEmail) {
            $emailResult = sendOrderConfirmationEmail($order, $customerEmail, $pdfPath);
            
            if ($emailResult['success']) {
                error_log("Invoice email sent successfully for order: " . $order_number);
            } else {
                error_log("Failed to send invoice email: " . $emailResult['message']);
            }
        }
    } else {
        error_log("Failed to generate PDF invoice for order: " . $order_number);
    }
    
    $response['success'] = true;
    $response['message'] = 'Order placed successfully';
    $response['order_number'] = $order_number;
    $response['pdf_generated'] = $pdfPath ? true : false;
}
```

---

## 🎨 **Advanced PDF Features**

### **Add Company Logo:**
```php
// In generateInvoiceHTML function, add logo
$logoPath = __DIR__ . '/../admin/assets/img/logo.png';
if (file_exists($logoPath)) {
    $logoBase64 = base64_encode(file_get_contents($logoPath));
    $logoHtml = '<img src="data:image/png;base64,' . $logoBase64 . '" style="height: 50px; margin-bottom: 10px;">';
    // Add to header section
}
```

### **Add Barcode/QR Code:**
```php
// Install QR code library
// composer require endroid/qr-code

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

function generateOrderQRCode($orderNumber, $orderUrl) {
    $qrCode = new QrCode($orderUrl);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);
    
    $qrPath = 'temp/qr-' . $orderNumber . '.png';
    file_put_contents($qrPath, $result->getString());
    
    return $qrPath;
}
```

### **Multi-language Support:**
```php
function generateInvoiceHTML($orderData, $language = 'en') {
    $translations = [
        'en' => [
            'invoice' => 'INVOICE',
            'bill_to' => 'BILL TO:',
            'ship_to' => 'SHIP TO:',
            'thank_you' => 'Thank you for shopping with Haat Bazar!'
        ],
        'bn' => [
            'invoice' => 'চালান',
            'bill_to' => 'বিল প্রাপক:',
            'ship_to' => 'শিপিং ঠিকানা:',
            'thank_you' => 'হাট বাজারে কেনাকাটার জন্য ধন্যবাদ!'
        ]
    ];
    
    $t = $translations[$language];
    // Use $t['key'] in your HTML template
}
```

---

## 📁 **File Management**

### **Create Invoice Storage Structure:**
```
invoices/
├── 2025/
│   ├── 01/
│   │   ├── invoice-ORD-123.pdf
│   │   └── invoice-ORD-124.pdf
│   └── 02/
└── temp/
    └── qr-codes/
```

### **Organized File Storage:**
```php
function getInvoiceFilePath($orderNumber, $date) {
    $year = date('Y', strtotime($date));
    $month = date('m', strtotime($date));
    
    $directory = __DIR__ . "/../invoices/{$year}/{$month}/";
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    return $directory . "invoice-{$orderNumber}.pdf";
}
```

---

## 🧪 **Testing PDF Generation**

### **Create test-pdf.php:**
```php
<?php
require 'vendor/autoload.php';

// Test PDF generation
$testOrderData = [
    'id' => 1,
    'order_number' => 'TEST-' . time(),
    'created_at' => date('Y-m-d H:i:s'),
    'total_amount' => 1500.00,
    'subtotal' => 1300.00,
    'tax_amount' => 200.00,
    'discount_amount' => 0.00,
    'shipping_amount' => 0.00,
    'payment_method' => 'cash',
    'payment_status' => 'pending',
    'status' => 'pending',
    'billing_first_name' => 'Test',
    'billing_last_name' => 'Customer',
    'billing_address_line_1' => '123 Test Street',
    'billing_city' => 'Dhaka',
    'billing_state' => 'Dhaka',
    'billing_postal_code' => '1000',
    'billing_country' => 'Bangladesh',
    'billing_phone' => '+880 1700-000000',
    'shipping_first_name' => 'Test',
    'shipping_last_name' => 'Customer',
    'shipping_address_line_1' => '123 Test Street',
    'shipping_city' => 'Dhaka',
    'shipping_state' => 'Dhaka',
    'shipping_postal_code' => '1000',
    'shipping_country' => 'Bangladesh',
    'shipping_phone' => '+880 1700-000000'
];

$pdfPath = generateOrderInvoicePDF($testOrderData);

if ($pdfPath) {
    echo "✅ PDF generated successfully: " . $pdfPath;
    echo "<br><a href='" . $pdfPath . "' target='_blank'>View PDF</a>";
} else {
    echo "❌ PDF generation failed";
}
?>
```

---

## 🔐 **Security Considerations**

### **File Access Control:**
```php
// Create .htaccess in invoices directory
$htaccessContent = "
Order Deny,Allow
Deny from all
Allow from 127.0.0.1
";

file_put_contents(__DIR__ . '/../invoices/.htaccess', $htaccessContent);
```

### **Secure PDF Access:**
```php
// Create secure-pdf.php for controlled access
function servePDFSecurely($orderNumber, $userId) {
    // Verify user owns the order
    $db = new MysqliDb();
    $order = $db->where('order_number', $orderNumber)
                ->where('user_id', $userId)
                ->getOne('orders');
    
    if (!$order) {
        http_response_code(403);
        die('Access denied');
    }
    
    $pdfPath = getInvoiceFilePath($orderNumber, $order['created_at']);
    
    if (file_exists($pdfPath)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="invoice-' . $orderNumber . '.pdf"');
        readfile($pdfPath);
    } else {
        http_response_code(404);
        die('Invoice not found');
    }
}
```

---

## 📊 **Performance Optimization**

### **Async PDF Generation:**
```php
// Generate PDF in background
function generatePDFAsync($orderId) {
    $command = "php generate-pdf-worker.php {$orderId} > /dev/null 2>&1 &";
    exec($command);
}
```

### **PDF Caching:**
```php
function getCachedPDF($orderNumber) {
    $cacheFile = "cache/pdf-{$orderNumber}.pdf";
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
        return $cacheFile;
    }
    
    return false;
}
```

---

## 🚀 **Production Deployment**

### **Deployment Checklist:**
- [ ] Install PDF library via Composer
- [ ] Create invoices directory with proper permissions
- [ ] Test PDF generation with sample data
- [ ] Verify file permissions and security
- [ ] Set up automated cleanup for old PDFs
- [ ] Configure error logging for PDF generation
- [ ] Test email attachment functionality
- [ ] Verify PDF displays correctly in different viewers
- [ ] Set up monitoring for PDF generation failures

---

## 🔧 **Troubleshooting**

### **Common Issues:**
1. **Memory Limit** - Increase PHP memory_limit
2. **File Permissions** - Check directory write permissions
3. **Missing Fonts** - Install required fonts for Unicode
4. **Large Images** - Optimize images before including
5. **HTML Errors** - Validate HTML structure

### **Debug Mode:**
```php
// Enable TCPDF debugging
$pdf->SetDisplayMode('fullpage', 'single', 'UseNone');
$pdf->SetCompression(false);
```

---

**Happy PDF Generation! 📄**

*Remember: Always test with real order data before going live!*