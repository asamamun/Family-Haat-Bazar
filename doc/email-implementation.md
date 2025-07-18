# Email Implementation Guide
## Family Haat Bazar - Order Confirmation Emails

### 🎯 **Objective**
Implement email functionality to send order confirmation emails with PDF invoices to customers when orders are placed.

---

## 📧 **Recommended Email Solutions**

### **Method 1: PHPMailer (Recommended)**

#### **Why PHPMailer?**
- ✅ **Most Popular** - Industry standard for PHP email
- ✅ **SMTP Support** - Works with Gmail, SendGrid, Mailgun, etc.
- ✅ **HTML Emails** - Rich formatting and attachments
- ✅ **Error Handling** - Comprehensive debugging
- ✅ **Security** - Built-in security features
- ✅ **Easy Integration** - Simple to implement

#### **Installation:**
```bash
composer require phpmailer/phpmailer
```

#### **Basic Configuration:**
```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendOrderConfirmationEmail($orderData, $customerEmail, $pdfPath) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // or your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';
        $mail->Password   = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('noreply@haatbazar.com', 'Haat Bazar');
        $mail->addAddress($customerEmail);
        $mail->addReplyTo('support@haatbazar.com', 'Haat Bazar Support');

        // Attachments
        $mail->addAttachment($pdfPath, 'Invoice-' . $orderData['order_number'] . '.pdf');

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation - ' . $orderData['order_number'];
        $mail->Body    = generateEmailTemplate($orderData);
        $mail->AltBody = generatePlainTextEmail($orderData);

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"];
    }
}
```

---

### **Method 2: SwiftMailer (Alternative)**

#### **Installation:**
```bash
composer require swiftmailer/swiftmailer
```

#### **Basic Implementation:**
```php
<?php
require_once 'vendor/autoload.php';

function sendEmailWithSwiftMailer($orderData, $customerEmail, $pdfPath) {
    // Create the Transport
    $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
        ->setUsername('your-email@gmail.com')
        ->setPassword('your-app-password');

    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);

    // Create a message
    $message = (new Swift_Message('Order Confirmation - ' . $orderData['order_number']))
        ->setFrom(['noreply@haatbazar.com' => 'Haat Bazar'])
        ->setTo([$customerEmail])
        ->setBody(generateEmailTemplate($orderData), 'text/html')
        ->addPart(generatePlainTextEmail($orderData), 'text/plain')
        ->attach(Swift_Attachment::fromPath($pdfPath));

    // Send the message
    $result = $mailer->send($message);
    
    return $result > 0 ? 
        ['success' => true, 'message' => 'Email sent successfully'] : 
        ['success' => false, 'message' => 'Failed to send email'];
}
```

---

### **Method 3: Symfony Mailer (Modern)**

#### **Installation:**
```bash
composer require symfony/mailer
```

#### **Implementation:**
```php
<?php
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

function sendEmailWithSymfonyMailer($orderData, $customerEmail, $pdfPath) {
    $transport = Transport::fromDsn('smtp://your-email@gmail.com:your-app-password@smtp.gmail.com:587');
    $mailer = new Mailer($transport);

    $email = (new Email())
        ->from(new Address('noreply@haatbazar.com', 'Haat Bazar'))
        ->to($customerEmail)
        ->subject('Order Confirmation - ' . $orderData['order_number'])
        ->html(generateEmailTemplate($orderData))
        ->text(generatePlainTextEmail($orderData))
        ->attachFromPath($pdfPath, 'Invoice-' . $orderData['order_number'] . '.pdf');

    try {
        $mailer->send($email);
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Failed to send email: ' . $e->getMessage()];
    }
}
```

---

## 🔧 **Email Configuration Options**

### **SMTP Providers:**

#### **1. Gmail SMTP (Free - Limited)**
```php
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@gmail.com';
$mail->Password   = 'your-app-password'; // Use App Password, not regular password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

#### **2. SendGrid (Recommended for Production)**
```php
$mail->Host       = 'smtp.sendgrid.net';
$mail->SMTPAuth   = true;
$mail->Username   = 'apikey';
$mail->Password   = 'your-sendgrid-api-key';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

#### **3. Mailgun**
```php
$mail->Host       = 'smtp.mailgun.org';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-mailgun-username';
$mail->Password   = 'your-mailgun-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

#### **4. Amazon SES**
```php
$mail->Host       = 'email-smtp.us-east-1.amazonaws.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-ses-username';
$mail->Password   = 'your-ses-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

---

## 📧 **Email Template System**

### **HTML Email Template:**
```php
function generateEmailTemplate($orderData) {
    $template = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2E7D32; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .order-details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .btn { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🛒 Order Confirmation</h1>
                <p>Thank you for your order!</p>
            </div>
            
            <div class="content">
                <h2>Hello ' . htmlspecialchars($orderData['customer_name']) . ',</h2>
                <p>Your order has been confirmed and is being processed.</p>
                
                <div class="order-details">
                    <h3>Order Details:</h3>
                    <p><strong>Order Number:</strong> ' . htmlspecialchars($orderData['order_number']) . '</p>
                    <p><strong>Order Date:</strong> ' . date('F d, Y', strtotime($orderData['created_at'])) . '</p>
                    <p><strong>Total Amount:</strong> ৳' . number_format($orderData['total_amount'], 2) . '</p>
                    <p><strong>Payment Method:</strong> ' . ucfirst($orderData['payment_method']) . '</p>
                </div>
                
                <div class="order-details">
                    <h3>Shipping Address:</h3>
                    <p>' . htmlspecialchars($orderData['shipping_address']) . '</p>
                </div>
                
                <p>Your invoice is attached to this email as a PDF.</p>
                
                <div style="text-align: center; margin: 20px 0;">
                    <a href="https://coders64.xyz/projects/haatbazar/user-orders.php" class="btn">Track Your Order</a>
                </div>
            </div>
            
            <div class="footer">
                <p>Thank you for shopping with Haat Bazar!</p>
                <p>If you have any questions, contact us at support@haatbazar.com</p>
                <p>&copy; ' . date('Y') . ' Haat Bazar. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $template;
}
```

### **Plain Text Email Template:**
```php
function generatePlainTextEmail($orderData) {
    return "
Order Confirmation - Haat Bazar

Hello " . $orderData['customer_name'] . ",

Your order has been confirmed and is being processed.

Order Details:
- Order Number: " . $orderData['order_number'] . "
- Order Date: " . date('F d, Y', strtotime($orderData['created_at'])) . "
- Total Amount: ৳" . number_format($orderData['total_amount'], 2) . "
- Payment Method: " . ucfirst($orderData['payment_method']) . "

Shipping Address:
" . $orderData['shipping_address'] . "

Your invoice is attached to this email as a PDF.

Track your order: https://coders64.xyz/projects/haatbazar/user-orders.php

Thank you for shopping with Haat Bazar!

If you have any questions, contact us at support@haatbazar.com

© " . date('Y') . " Haat Bazar. All rights reserved.
    ";
}
```

---

## 🔧 **Integration with processOrder.php**

### **Add to processOrder.php:**
```php
// After successful order creation
if ($order_id) {
    // ... existing code ...
    
    // Generate PDF invoice (see pdf-implementation.md)
    $pdfPath = generateInvoicePDF($order_id, $order_data);
    
    // Send confirmation email
    $customerEmail = $user ? $user['email'] : $order_data['billing_email'];
    $emailResult = sendOrderConfirmationEmail([
        'order_number' => $order_number,
        'customer_name' => $order_data['billing_first_name'] . ' ' . $order_data['billing_last_name'],
        'created_at' => $order_data['created_at'],
        'total_amount' => $order_data['total_amount'],
        'payment_method' => $order_data['payment_method'],
        'shipping_address' => $order_data['shipping_address_line_1'] . ', ' . $order_data['shipping_city']
    ], $customerEmail, $pdfPath);
    
    // Log email result
    if ($emailResult['success']) {
        error_log("Order confirmation email sent successfully for order: " . $order_number);
    } else {
        error_log("Failed to send order confirmation email for order: " . $order_number . " - " . $emailResult['message']);
    }
    
    // Clean up temporary PDF file
    if (file_exists($pdfPath)) {
        unlink($pdfPath);
    }
}
```

---

## 📧 **Email Configuration File**

### **Create config/email.php:**
```php
<?php
return [
    'default' => 'smtp',
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ],
        
        'sendgrid' => [
            'transport' => 'smtp',
            'host' => 'smtp.sendgrid.net',
            'port' => 587,
            'encryption' => 'tls',
            'username' => 'apikey',
            'password' => env('SENDGRID_API_KEY'),
        ],
    ],
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@haatbazar.com'),
        'name' => env('MAIL_FROM_NAME', 'Haat Bazar'),
    ],
    
    'reply_to' => [
        'address' => env('MAIL_REPLY_TO', 'support@haatbazar.com'),
        'name' => 'Haat Bazar Support',
    ],
];
```

---

## 🔐 **Security Best Practices**

### **Environment Variables (.env file):**
```env
# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@haatbazar.com
MAIL_FROM_NAME="Haat Bazar"
MAIL_REPLY_TO=support@haatbazar.com

# SendGrid (Alternative)
SENDGRID_API_KEY=your-sendgrid-api-key
```

### **Gmail App Password Setup:**
1. Enable 2-Factor Authentication on Gmail
2. Go to Google Account Settings
3. Security → App passwords
4. Generate app password for "Mail"
5. Use this password in your configuration

---

## 🧪 **Testing Email Functionality**

### **Create test-email.php:**
```php
<?php
require 'vendor/autoload.php';

// Test email sending
$testOrderData = [
    'order_number' => 'TEST-' . time(),
    'customer_name' => 'Test Customer',
    'created_at' => date('Y-m-d H:i:s'),
    'total_amount' => 1500.00,
    'payment_method' => 'cash',
    'shipping_address' => 'Test Address, Dhaka'
];

$result = sendOrderConfirmationEmail(
    $testOrderData, 
    'test@example.com', 
    'path/to/test-invoice.pdf'
);

if ($result['success']) {
    echo "✅ Test email sent successfully!";
} else {
    echo "❌ Test email failed: " . $result['message'];
}
?>
```

---

## 📊 **Email Analytics & Monitoring**

### **Track Email Success:**
```php
function logEmailActivity($orderNumber, $email, $status, $message = '') {
    $logData = [
        'order_number' => $orderNumber,
        'email' => $email,
        'status' => $status,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Log to database
    $db = new MysqliDb();
    $db->insert('email_logs', $logData);
    
    // Log to file
    error_log("Email Log: " . json_encode($logData));
}
```

### **Create email_logs table:**
```sql
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50),
    email VARCHAR(255),
    status ENUM('sent', 'failed'),
    message TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🚀 **Production Deployment Checklist**

### **Before Going Live:**
- [ ] Configure production SMTP settings
- [ ] Test email delivery with real email addresses
- [ ] Set up email monitoring and logging
- [ ] Configure proper from/reply-to addresses
- [ ] Test with different email providers (Gmail, Yahoo, Outlook)
- [ ] Verify PDF attachments work correctly
- [ ] Set up email templates for different languages
- [ ] Configure email rate limiting
- [ ] Set up bounce handling
- [ ] Test email deliverability

---

## 🔧 **Troubleshooting Common Issues**

### **Email Not Sending:**
1. Check SMTP credentials
2. Verify firewall/port settings
3. Check email provider limits
4. Review error logs
5. Test with simple email first

### **Emails Going to Spam:**
1. Set up SPF records
2. Configure DKIM signing
3. Use reputable SMTP provider
4. Avoid spam trigger words
5. Include unsubscribe link

### **Large PDF Attachments:**
1. Optimize PDF file size
2. Use cloud storage links instead
3. Compress images in PDF
4. Consider email size limits

---

**Happy Emailing! 📧**

*Remember: Always test thoroughly before deploying to production!*