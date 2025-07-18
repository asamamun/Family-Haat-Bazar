<?php
namespace App;

// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    private function configureSMTP() {
        try {
            // Server settings using cPanel email
            $this->mail->isSMTP();
            $this->mail->Host       = settings()['mail_host'];
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = settings()['mail_username'];
            $this->mail->Password   = settings()['mail_password'];
            $this->mail->SMTPSecure = settings()['mail_encryption'];
            $this->mail->Port       = settings()['mail_port'];
            
            // Set default from address
            $this->mail->setFrom(
                settings()['mail_from_address'], 
                settings()['mail_from_name']
            );
            
            // Set reply-to
            $this->mail->addReplyTo(
                settings()['mail_reply_to'], 
                settings()['mail_from_name'] . ' Support'
            );
            
        } catch (Exception $e) {
            error_log("SMTP Configuration Error: " . $e->getMessage());
        }
    }
    
    public function sendOrderConfirmationEmail($orderData, $customerEmail, $pdfPath = null) {
        try {
            // Clear any previous recipients
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Recipients
            $this->mail->addAddress($customerEmail);
            
            // Attachments
            if ($pdfPath && file_exists($pdfPath)) {
                $this->mail->addAttachment($pdfPath, 'Invoice-' . $orderData['order_number'] . '.pdf');
            }
            
            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Order Confirmation - ' . $orderData['order_number'] . ' - Haat Bazar';
            $this->mail->Body    = $this->generateEmailTemplate($orderData);
            $this->mail->AltBody = $this->generatePlainTextEmail($orderData);
            
            $this->mail->send();
            
            // Log success
            $this->logEmailActivity($orderData['order_number'], $customerEmail, 'sent', 'Order confirmation sent successfully');
            
            return ['success' => true, 'message' => 'Email sent successfully'];
            
        } catch (Exception $e) {
            // Log error
            $this->logEmailActivity($orderData['order_number'], $customerEmail, 'failed', $e->getMessage());
            
            return ['success' => false, 'message' => "Email could not be sent. Error: {$this->mail->ErrorInfo}"];
        }
    }
    
    private function generateEmailTemplate($orderData) {
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Order Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2E7D32, #4CAF50); color: white; padding: 30px 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { margin: 0; font-size: 28px; }
                .header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }
                .content { padding: 30px 20px; background: #f9f9f9; }
                .order-details { background: white; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .order-details h3 { color: #2E7D32; margin-top: 0; border-bottom: 2px solid #4CAF50; padding-bottom: 10px; }
                .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
                .detail-row:last-child { border-bottom: none; }
                .detail-label { font-weight: bold; color: #555; }
                .detail-value { color: #333; }
                .total-amount { font-size: 18px; font-weight: bold; color: #2E7D32; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 14px; background: #e8f5e8; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #4CAF50; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; margin: 20px 0; font-weight: bold; }
                .btn:hover { background: #45a049; }
                .social-links { margin: 15px 0; }
                .social-links a { color: #4CAF50; text-decoration: none; margin: 0 10px; }
                .address-box { background: #f0f8f0; padding: 15px; border-left: 4px solid #4CAF50; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🛒 Order Confirmation</h1>
                    <p>Thank you for choosing Haat Bazar!</p>
                </div>
                
                <div class="content">
                    <h2>Hello ' . htmlspecialchars($orderData['customer_name']) . ',</h2>
                    <p>Your order has been confirmed and is being processed. We\'re excited to get your items to you soon!</p>
                    
                    <div class="order-details">
                        <h3>📋 Order Details</h3>
                        <div class="detail-row">
                            <span class="detail-label">Order Number:</span>
                            <span class="detail-value">' . htmlspecialchars($orderData['order_number']) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Order Date:</span>
                            <span class="detail-value">' . date('F d, Y \a\t g:i A', strtotime($orderData['created_at'])) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value">' . ucfirst($orderData['payment_method']) . '</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Total Amount:</span>
                            <span class="detail-value total-amount">৳' . number_format($orderData['total_amount'], 2) . '</span>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <h3>🚚 Shipping Information</h3>
                        <div class="address-box">
                            <strong>' . htmlspecialchars($orderData['shipping_name']) . '</strong><br>
                            ' . htmlspecialchars($orderData['shipping_address']) . '<br>
                            📞 ' . htmlspecialchars($orderData['shipping_phone']) . '
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="https://coders64.xyz/projects/haatbazar/user-orders.php" class="btn">
                            📦 Track Your Order
                        </a>
                    </div>
                    
                    <div class="order-details">
                        <h3>📄 Invoice</h3>
                        <p>Your detailed invoice is attached to this email as a PDF. Please keep it for your records.</p>
                    </div>
                </div>
                
                <div class="footer">
                    <p><strong>🙏 Thank you for shopping with Haat Bazar! 🙏</strong></p>
                    <p>Your Family Shopping Destination</p>
                    
                    <div class="social-links">
                        <a href="https://coders64.xyz/projects/haatbazar/">🌐 Visit Website</a> |
                        <a href="mailto:info@coders64.xyz">📧 Contact Support</a>
                    </div>
                    
                    <p style="font-size: 12px; margin-top: 20px;">
                        If you have any questions about your order, please contact us at info@coders64.xyz<br>
                        &copy; ' . date('Y') . ' Haat Bazar. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    private function generatePlainTextEmail($orderData) {
        return "
🛒 ORDER CONFIRMATION - HAAT BAZAR
=====================================

Hello " . $orderData['customer_name'] . ",

Thank you for your order! Your order has been confirmed and is being processed.

ORDER DETAILS:
--------------
Order Number: " . $orderData['order_number'] . "
Order Date: " . date('F d, Y \a\t g:i A', strtotime($orderData['created_at'])) . "
Total Amount: ৳" . number_format($orderData['total_amount'], 2) . "
Payment Method: " . ucfirst($orderData['payment_method']) . "

SHIPPING INFORMATION:
--------------------
" . $orderData['shipping_name'] . "
" . $orderData['shipping_address'] . "
Phone: " . $orderData['shipping_phone'] . "

INVOICE:
--------
Your detailed invoice is attached to this email as a PDF.

TRACK YOUR ORDER:
-----------------
Visit: https://coders64.xyz/projects/haatbazar/user-orders.php

NEED HELP?
----------
Contact us at: info@coders64.xyz
Website: https://coders64.xyz/projects/haatbazar/

Thank you for choosing Haat Bazar - Your Family Shopping Destination!

© " . date('Y') . " Haat Bazar. All rights reserved.
        ";
    }
    
    private function logEmailActivity($orderNumber, $email, $status, $message = '') {
        try {
            $db = new MysqliDb();
            $logData = [
                'order_number' => $orderNumber,
                'email' => $email,
                'status' => $status,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $db->insert('email_logs', $logData);
        } catch (Exception $e) {
            error_log("Email logging error: " . $e->getMessage());
        }
        
        // Also log to file
        error_log("Email Log - Order: {$orderNumber}, Email: {$email}, Status: {$status}, Message: {$message}");
    }
}
?>