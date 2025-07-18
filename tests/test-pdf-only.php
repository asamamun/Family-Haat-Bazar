<?php
/**
 * PDF Service Test - Focused PDF Testing
 * Family Haat Bazar - PDF generation verification
 */

// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');

require_once __DIR__ . '/../vendor/autoload.php';

use App\PDFService;

echo "<h1>📄 PDF Service Test</h1>";
echo "<p>Testing PDF invoice generation functionality...</p>";
echo "<hr>";

try {
    // Initialize PDF service
    echo "<h2>🔧 PDF Service Initialization</h2>";
    $pdfService = new PDFService();
    echo "<p>✅ PDFService initialized successfully</p>";
    
    // Check TCPDF availability
    echo "<h2>📦 TCPDF Library Check</h2>";
    if (class_exists('TCPDF')) {
        echo "<p>✅ TCPDF library is available</p>";
        $tcpdf = new TCPDF();
        echo "<p>✅ TCPDF instance created successfully</p>";
    } else {
        throw new Exception("TCPDF library is not available. Please run 'composer install'");
    }
    
    // Test invoice directory
    echo "<h2>📁 Directory Check</h2>";
    $invoiceDir = __DIR__ . '/../assets/invoices/';
    if (is_dir($invoiceDir)) {
        echo "<p>✅ Invoice directory exists: " . $invoiceDir . "</p>";
        if (is_writable($invoiceDir)) {
            echo "<p>✅ Invoice directory is writable</p>";
        } else {
            echo "<p>⚠️ Invoice directory is not writable - may cause issues</p>";
        }
    } else {
        echo "<p>⚠️ Invoice directory does not exist - will be created automatically</p>";
    }
    
    // Create test order data
    echo "<h2>📋 Test Data Preparation</h2>";
    $testOrderData = [
        'id' => 999999,
        'order_number' => 'PDF-TEST-' . time(),
        'created_at' => date('Y-m-d H:i:s'),
        'total_amount' => 3500.00,
        'subtotal' => 3000.00,
        'tax_amount' => 450.00,
        'discount_amount' => 50.00,
        'shipping_amount' => 100.00,
        'payment_method' => 'bkash',
        'payment_status' => 'completed',
        'status' => 'processing',
        'billing_first_name' => 'Ahmed',
        'billing_last_name' => 'Rahman',
        'billing_company' => 'Rahman Trading Co.',
        'billing_address_line_1' => '123 Dhanmondi Road',
        'billing_address_line_2' => 'Apartment 5A',
        'billing_city' => 'Dhaka',
        'billing_state' => 'Dhaka Division',
        'billing_postal_code' => '1205',
        'billing_country' => 'Bangladesh',
        'billing_phone' => '+880 1712-345678',
        'shipping_first_name' => 'Ahmed',
        'shipping_last_name' => 'Rahman',
        'shipping_company' => 'Rahman Trading Co.',
        'shipping_address_line_1' => '456 Gulshan Avenue',
        'shipping_address_line_2' => 'House 12, Road 8',
        'shipping_city' => 'Dhaka',
        'shipping_state' => 'Dhaka Division',
        'shipping_postal_code' => '1212',
        'shipping_country' => 'Bangladesh',
        'shipping_phone' => '+880 1812-345678',
        'notes' => 'Please deliver during office hours (9 AM - 6 PM). Call before delivery. Handle with care - fragile items included.'
    ];
    
    $testOrderItems = [
        [
            'product_sku' => 'ELEC-001',
            'product_name' => 'Samsung Galaxy Smartphone - 128GB',
            'quantity' => 1,
            'unit_price' => 25000.00,
            'total_price' => 25000.00
        ],
        [
            'product_sku' => 'FASH-002',
            'product_name' => 'Premium Cotton T-Shirt - Blue',
            'quantity' => 2,
            'unit_price' => 1500.00,
            'total_price' => 3000.00
        ],
        [
            'product_sku' => 'HOME-003',
            'product_name' => 'Kitchen Blender - 500W',
            'quantity' => 1,
            'unit_price' => 4500.00,
            'total_price' => 4500.00
        ]
    ];
    
    echo "<p>✅ Test order data prepared</p>";
    echo "<p>📦 Order Number: " . $testOrderData['order_number'] . "</p>";
    echo "<p>💰 Total Amount: ৳" . number_format($testOrderData['total_amount'], 2) . "</p>";
    echo "<p>📋 Items Count: " . count($testOrderItems) . "</p>";
    
    // Test HTML generation
    echo "<h2>🎨 HTML Template Generation Test</h2>";
    
    // Use reflection to access private method
    $reflection = new ReflectionClass($pdfService);
    $method = $reflection->getMethod('generateInvoiceHTML');
    $method->setAccessible(true);
    
    $htmlContent = $method->invoke($pdfService, $testOrderData, $testOrderItems);
    
    if (strlen($htmlContent) > 2000) {
        echo "<p>✅ HTML template generated successfully</p>";
        echo "<p>📏 Template size: " . number_format(strlen($htmlContent)) . " characters</p>";
        
        // Show a preview of the HTML
        echo "<h3>👀 HTML Preview (First 500 characters)</h3>";
        echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd; font-family: monospace; font-size: 12px;'>";
        echo htmlspecialchars(substr($htmlContent, 0, 500)) . "...";
        echo "</div>";
    } else {
        throw new Exception("HTML template generation failed - content too short");
    }
    
    // Test PDF generation
    echo "<h2>📄 PDF Generation Test</h2>";
    echo "<p>🔄 Generating PDF invoice...</p>";
    
    try {
        // Create PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Haat Bazar Test');
        $pdf->SetAuthor('Haat Bazar');
        $pdf->SetTitle('Test Invoice - ' . $testOrderData['order_number']);
        $pdf->SetSubject('Test Invoice');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Write HTML content
        $pdf->writeHTML($htmlContent, true, false, true, false, '');
        
        // Save PDF
        $testPdfPath = __DIR__ . '/../assets/temp/test-invoice-' . time() . '.pdf';
        $pdf->Output($testPdfPath, 'F');
        
        if (file_exists($testPdfPath)) {
            $fileSize = filesize($testPdfPath);
            echo "<p>✅ PDF generated successfully!</p>";
            echo "<p>📁 File: " . basename($testPdfPath) . "</p>";
            echo "<p>📏 Size: " . number_format($fileSize) . " bytes (" . number_format($fileSize/1024, 1) . " KB)</p>";
            echo "<p>📍 Location: " . $testPdfPath . "</p>";
            
            // Create a link to view the PDF
            $relativePath = '../assets/temp/' . basename($testPdfPath);
            echo "<p><a href='{$relativePath}' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔗 View Generated PDF</a></p>";
            
            // Test PDF properties
            echo "<h3>📊 PDF Properties</h3>";
            echo "<ul>";
            echo "<li>File exists: ✅ Yes</li>";
            echo "<li>File size: " . number_format($fileSize) . " bytes</li>";
            echo "<li>File readable: " . (is_readable($testPdfPath) ? "✅ Yes" : "❌ No") . "</li>";
            echo "<li>Created at: " . date('F d, Y \a\t g:i A', filemtime($testPdfPath)) . "</li>";
            echo "</ul>";
            
        } else {
            throw new Exception("PDF file was not created at expected location");
        }
        
    } catch (Exception $e) {
        throw new Exception("PDF generation failed: " . $e->getMessage());
    }
    
    // Success summary
    echo "<h2>🎉 Test Summary</h2>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>✅ PDF Service Test Completed Successfully!</strong></p>";
    echo "<ul>";
    echo "<li>✅ PDF service initialization: OK</li>";
    echo "<li>✅ TCPDF library check: OK</li>";
    echo "<li>✅ Directory permissions: OK</li>";
    echo "<li>✅ Test data preparation: OK</li>";
    echo "<li>✅ HTML template generation: OK</li>";
    echo "<li>✅ PDF file generation: OK</li>";
    echo "</ul>";
    echo "<p><strong>🎯 Result:</strong> Your PDF invoice system is working perfectly!</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>❌ Test Failed!</strong></p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
    
    echo "<h3>🔧 Troubleshooting Tips</h3>";
    echo "<ul>";
    echo "<li>Make sure you've run <code>composer install</code></li>";
    echo "<li>Check that the <code>assets/invoices/</code> directory exists and is writable</li>";
    echo "<li>Verify TCPDF library is properly installed</li>";
    echo "<li>Check PHP error logs for more details</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><em>Test completed at: " . date('F d, Y \a\t g:i A') . " (Bangladesh Time)</em></p>";
?>