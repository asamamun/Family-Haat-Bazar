<?php
namespace App;

// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');

class PDFService {
    
    public function generateOrderInvoicePDF($orderId) {
        try {
            $db = new MysqliDb();
            
            // Get order details
            $order = $db->where('id', $orderId)->getOne('orders');
            if (!$order) {
                throw new Exception('Order not found');
            }
            
            // Get order items
            $orderItems = $db->where('order_id', $orderId)->get('order_items');
            
            // Create invoices directory if it doesn't exist
            $invoiceDir = __DIR__ . '/../assets/invoices/';
            if (!is_dir($invoiceDir)) {
                mkdir($invoiceDir, 0755, true);
            }
            
            // Generate PDF using TCPDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('Haat Bazar');
            $pdf->SetAuthor('Haat Bazar');
            $pdf->SetTitle('Invoice - ' . $order['order_number']);
            $pdf->SetSubject('Order Invoice');
            $pdf->SetKeywords('Invoice, Order, Haat Bazar');
            
            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Set margins
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(TRUE, 15);
            
            // Add a page
            $pdf->AddPage();
            
            // Generate HTML content
            $html = $this->generateInvoiceHTML($order, $orderItems);
            
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
    
    private function generateInvoiceHTML($order, $orderItems) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2E7D32; padding-bottom: 20px; }
                .company-name { font-size: 28px; font-weight: bold; color: #2E7D32; margin-bottom: 5px; }
                .company-tagline { font-size: 14px; color: #4CAF50; margin-bottom: 10px; }
                .company-info { font-size: 10px; color: #666; }
                .invoice-title { font-size: 24px; font-weight: bold; margin: 25px 0; text-align: center; background: linear-gradient(135deg, #e8f5e8, #c8e6c9); padding: 15px; border-radius: 8px; }
                .invoice-details { margin: 20px 0; }
                .invoice-details table { width: 100%; }
                .invoice-details td { padding: 8px 0; border-bottom: 1px solid #eee; }
                .billing-shipping { margin: 25px 0; }
                .billing-shipping table { width: 100%; }
                .billing-shipping td { vertical-align: top; padding: 15px; }
                .address-box { border: 2px solid #4CAF50; padding: 15px; background: #f8fff8; border-radius: 8px; }
                .address-title { font-size: 14px; font-weight: bold; color: #2E7D32; margin-bottom: 10px; }
                .items-table { width: 100%; border-collapse: collapse; margin: 25px 0; }
                .items-table th, .items-table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                .items-table th { background: linear-gradient(135deg, #2E7D32, #4CAF50); color: white; font-weight: bold; font-size: 11px; }
                .items-table .number { text-align: right; }
                .items-table .center { text-align: center; }
                .totals { margin-top: 25px; }
                .totals table { width: 60%; margin-left: auto; border: 2px solid #4CAF50; border-radius: 8px; }
                .totals td { padding: 8px 15px; border-bottom: 1px solid #eee; }
                .totals .total-row { font-weight: bold; font-size: 16px; background: linear-gradient(135deg, #e8f5e8, #c8e6c9); }
                .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; border-top: 2px solid #4CAF50; padding-top: 20px; }
                .thank-you { text-align: center; margin: 30px 0; font-size: 18px; color: #2E7D32; font-weight: bold; background: #f0f8f0; padding: 15px; border-radius: 8px; }
                .terms { margin-top: 25px; font-size: 10px; background: #f9f9f9; padding: 15px; border-radius: 5px; }
            </style>
        </head>
        <body>
            <!-- Header -->
            <div class="header">
                <div class="company-name">🛒 HAAT BAZAR</div>
                <div class="company-tagline">Your Family Shopping Destination</div>
                <div class="company-info">
                    📍 Dhaka, Bangladesh | 📞 +880 1700-000000 | 📧 info@coders64.xyz<br>
                    🌐 https://coders64.xyz/projects/haatbazar/
                </div>
            </div>
            
            <!-- Invoice Title -->
            <div class="invoice-title">📄 INVOICE</div>
            
            <!-- Invoice Details -->
            <div class="invoice-details">
                <table>
                    <tr>
                        <td style="width: 50%; font-weight: bold;">Invoice Number:</td>
                        <td style="width: 50%;">' . htmlspecialchars($order['order_number']) . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Invoice Date:</td>
                        <td>' . date('F d, Y', strtotime($order['created_at'])) . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Order Date:</td>
                        <td>' . date('F d, Y \a\t g:i A', strtotime($order['created_at'])) . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Payment Method:</td>
                        <td>' . ucfirst($order['payment_method']) . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Payment Status:</td>
                        <td style="color: ' . ($order['payment_status'] == 'completed' ? '#4CAF50' : '#FF9800') . '; font-weight: bold;">' . ucfirst($order['payment_status']) . '</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Order Status:</td>
                        <td style="color: ' . ($order['status'] == 'completed' ? '#4CAF50' : '#FF9800') . '; font-weight: bold;">' . ucfirst($order['status']) . '</td>
                    </tr>
                </table>
            </div>
            
            <!-- Billing and Shipping -->
            <div class="billing-shipping">
                <table>
                    <tr>
                        <td style="width: 50%;">
                            <div class="address-box">
                                <div class="address-title">📄 BILL TO:</div>
                                <strong>' . htmlspecialchars($order['billing_first_name'] . ' ' . $order['billing_last_name']) . '</strong><br>';
        
        if (!empty($order['billing_company'])) {
            $html .= htmlspecialchars($order['billing_company']) . '<br>';
        }
        
        $html .= htmlspecialchars($order['billing_address_line_1']) . '<br>';
        
        if (!empty($order['billing_address_line_2'])) {
            $html .= htmlspecialchars($order['billing_address_line_2']) . '<br>';
        }
        
        $html .= htmlspecialchars($order['billing_city'] . ', ' . $order['billing_state'] . ' ' . $order['billing_postal_code']) . '<br>
                                ' . htmlspecialchars($order['billing_country']) . '<br>
                                📞 ' . htmlspecialchars($order['billing_phone']) . '
                            </div>
                        </td>
                        <td style="width: 50%;">
                            <div class="address-box">
                                <div class="address-title">🚚 SHIP TO:</div>
                                <strong>' . htmlspecialchars($order['shipping_first_name'] . ' ' . $order['shipping_last_name']) . '</strong><br>';
        
        if (!empty($order['shipping_company'])) {
            $html .= htmlspecialchars($order['shipping_company']) . '<br>';
        }
        
        $html .= htmlspecialchars($order['shipping_address_line_1']) . '<br>';
        
        if (!empty($order['shipping_address_line_2'])) {
            $html .= htmlspecialchars($order['shipping_address_line_2']) . '<br>';
        }
        
        $html .= htmlspecialchars($order['shipping_city'] . ', ' . $order['shipping_state'] . ' ' . $order['shipping_postal_code']) . '<br>
                                ' . htmlspecialchars($order['shipping_country']) . '<br>
                                📞 ' . htmlspecialchars($order['shipping_phone']) . '
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Order Items -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="center">#</th>
                        <th style="width: 15%;">SKU</th>
                        <th style="width: 40%;">Product Name</th>
                        <th style="width: 10%;" class="center">Qty</th>
                        <th style="width: 15%;" class="number">Unit Price</th>
                        <th style="width: 15%;" class="number">Total</th>
                    </tr>
                </thead>
                <tbody>';
        
        $itemNumber = 1;
        foreach ($orderItems as $item) {
            $html .= '
                    <tr>
                        <td class="center">' . $itemNumber++ . '</td>
                        <td>' . htmlspecialchars($item['product_sku']) . '</td>
                        <td>' . htmlspecialchars($item['product_name']) . '</td>
                        <td class="center">' . $item['quantity'] . '</td>
                        <td class="number">৳' . number_format($item['unit_price'], 2) . '</td>
                        <td class="number">৳' . number_format($item['total_price'], 2) . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <!-- Totals -->
            <div class="totals">
                <table>
                    <tr>
                        <td style="width: 70%;"><strong>Subtotal:</strong></td>
                        <td class="number" style="width: 30%;"><strong>৳' . number_format($order['subtotal'], 2) . '</strong></td>
                    </tr>';
        
        if ($order['discount_amount'] > 0) {
            $html .= '
                    <tr>
                        <td>Discount:</td>
                        <td class="number" style="color: #f44336;">-৳' . number_format($order['discount_amount'], 2) . '</td>
                    </tr>';
        }
        
        if ($order['tax_amount'] > 0) {
            $html .= '
                    <tr>
                        <td>Tax/VAT:</td>
                        <td class="number">৳' . number_format($order['tax_amount'], 2) . '</td>
                    </tr>';
        }
        
        if ($order['shipping_amount'] > 0) {
            $html .= '
                    <tr>
                        <td>Shipping:</td>
                        <td class="number">৳' . number_format($order['shipping_amount'], 2) . '</td>
                    </tr>';
        }
        
        $html .= '
                    <tr class="total-row">
                        <td><strong>GRAND TOTAL:</strong></td>
                        <td class="number"><strong>৳' . number_format($order['total_amount'], 2) . '</strong></td>
                    </tr>
                </table>
            </div>';
        
        if (!empty($order['notes'])) {
            $html .= '
            <div style="margin-top: 25px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                <strong>📝 Order Notes:</strong><br>
                ' . nl2br(htmlspecialchars($order['notes'])) . '
            </div>';
        }
        
        $html .= '
            <!-- Thank You Message -->
            <div class="thank-you">
                🙏 Thank you for shopping with Haat Bazar! 🙏<br>
                <span style="font-size: 14px; font-weight: normal;">We appreciate your business and trust in our service.</span>
            </div>
            
            <!-- Terms & Conditions -->
            <div class="terms">
                <strong>📋 Terms & Conditions:</strong><br>
                • Payment is due within 30 days of invoice date<br>
                • Returns accepted within 7 days with original receipt and packaging<br>
                • Warranty terms apply as per manufacturer guidelines<br>
                • For support and inquiries, contact us at info@coders64.xyz or +880 1700-000000<br>
                • Visit our website: https://coders64.xyz/projects/haatbazar/
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p><strong>Haat Bazar - Your Family Shopping Destination</strong></p>
                <p>📍 Dhaka, Bangladesh | 📞 +880 1700-000000 | 📧 info@coders64.xyz</p>
                <p>🌐 https://coders64.xyz/projects/haatbazar/</p>
                <hr style="margin: 15px 0; border: 1px solid #4CAF50;">
                <p>&copy; ' . date('Y') . ' Haat Bazar. All rights reserved. | Generated on ' . date('F d, Y \a\t g:i A') . ' (Bangladesh Time)</p>
                <p style="font-size: 9px; color: #999;">This is a computer-generated invoice and does not require a signature.</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
}
?>