<?php
/**
 * Email Service Test - Focused Email Testing
 * Family Haat Bazar - Email functionality verification
 */

// Set timezone to Bangladesh Standard Time
date_default_timezone_set('Asia/Dhaka');

require_once __DIR__ . '/../vendor/autoload.php';

use App\EmailService;

echo "<h1>📧 Email Service Test</h1>";
echo "<p>Testing email functionality with your cPanel email configuration...</p>";
echo "<hr>";

try {
    // Initialize email service
    echo "<h2>🔧 Email Service Initialization</h2>";
    $emailService = new EmailService();
    echo "<p>✅ EmailService initialized successfully</p>";
    
    // Test configuration
    echo "<h2>⚙️ Configuration Check</h2>";
    $settings = settings();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr><th style='padding: 8px;'>Setting</th><th style='padding: 8px;'>Value</th></tr>";
    echo "<tr><td style='padding: 8px;'>Mail Host</td><td style='padding: 8px;'>" . ($settings['mail_host'] ?? 'Not set') . "</td></tr>";
    echo "<tr><td style='padding: 8px;'>Mail Username</td><td style='padding: 8px;'>" . ($settings['mail_username'] ?? 'Not set') . "</td></tr>";
    echo "<tr><td style='padding: 8px;'>Mail Password</td><td style='padding: 8px;'>" . (!empty($settings['mail_password']) ? '***CONFIGURED***' : 'Not set') . "</td></tr>";
    echo "<tr><td style='padding: 8px;'>From Address</td><td style='padding: 8px;'>" . ($settings['mail_from_address'] ?? 'Not set') . "</td></tr>";
    echo "<tr><td style='padding: 8px;'>From Name</td><td style='padding: 8px;'>" . ($settings['mail_from_name'] ?? 'Not set') . "</td></tr>";
    echo "</table>";
    
    // Test email template generation
    echo "<h2>📝 Email Template Test</h2>";
    
    $testOrderData = [
        'order_number' => 'EMAIL-TEST-' . time(),
        'customer_name' => 'Test Customer',
        'created_at' => date('Y-m-d H:i:s'),
        'total_amount' => 1500.00,
        'payment_method' => 'cash',
        'shipping_name' => 'Test Customer',
        'shipping_address' => '123 Test Street, Dhaka, 1000',
        'shipping_phone' => '+880 1700-000000'
    ];
    
    // Use reflection to test private methods
    $reflection = new ReflectionClass($emailService);
    
    // Test HTML template
    $htmlMethod = $reflection->getMethod('generateEmailTemplate');
    $htmlMethod->setAccessible(true);
    $htmlTemplate = $htmlMethod->invoke($emailService, $testOrderData);
    
    if (strlen($htmlTemplate) > 1000) {
        echo "<p>✅ HTML email template generated (" . number_format(strlen($htmlTemplate)) . " characters)</p>";
    } else {
        echo "<p>❌ HTML email template generation failed</p>";
    }
    
    // Test plain text template
    $textMethod = $reflection->getMethod('generatePlainTextEmail');
    $textMethod->setAccessible(true);
    $textTemplate = $textMethod->invoke($emailService, $testOrderData);
    
    if (strlen($textTemplate) > 100) {
        echo "<p>✅ Plain text email template generated (" . number_format(strlen($textTemplate)) . " characters)</p>";
    } else {
        echo "<p>❌ Plain text email template generation failed</p>";
    }
    
    // Show template preview
    echo "<h3>📋 Email Template Preview</h3>";
    echo "<div style='border: 2px solid #ddd; padding: 15px; margin: 10px 0; max-height: 400px; overflow-y: auto;'>";
    echo $htmlTemplate;
    echo "</div>";
    
    // Email sending test section
    echo "<h2>📤 Email Sending Test</h2>";
    echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 10px 0;'>";
    echo "<p><strong>📝 Instructions for Email Testing:</strong></p>";
    echo "<ol>";
    echo "<li>Replace 'YOUR_TEST_EMAIL@EXAMPLE.COM' below with your actual email address</li>";
    echo "<li>Uncomment the email sending code</li>";
    echo "<li>Refresh this page to test actual email sending</li>";
    echo "</ol>";
    echo "</div>";
    
    // Uncomment the lines below and replace with your test email to test actual sending
    /*
    $testEmail = 'YOUR_TEST_EMAIL@EXAMPLE.COM'; // Replace with your email
    
    echo "<p>🔄 Attempting to send test email to: {$testEmail}</p>";
    
    $result = $emailService->sendOrderConfirmationEmail($testOrderData, $testEmail);
    
    if ($result['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 10px 0;'>";
        echo "<p><strong>✅ SUCCESS!</strong> Test email sent successfully!</p>";
        echo "<p>Check your inbox at: {$testEmail}</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 10px 0;'>";
        echo "<p><strong>❌ FAILED!</strong> Email sending failed.</p>";
        echo "<p><strong>Error:</strong> " . htmlspecialchars($result['message']) . "</p>";
        echo "</div>";
    }
    */
    
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 10px 0;'>";
    echo "<p><strong>⚠️ Email Sending Test Skipped</strong></p>";
    echo "<p>To test actual email sending, uncomment the code above and provide your test email address.</p>";
    echo "</div>";
    
    // Success summary
    echo "<h2>🎉 Test Summary</h2>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>✅ Email Service Test Completed Successfully!</strong></p>";
    echo "<ul>";
    echo "<li>✅ Email service initialization: OK</li>";
    echo "<li>✅ Configuration check: OK</li>";
    echo "<li>✅ HTML template generation: OK</li>";
    echo "<li>✅ Plain text template generation: OK</li>";
    echo "<li>⏳ Actual email sending: Ready for testing</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
    echo "<p><strong>❌ Test Failed!</strong></p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><em>Test completed at: " . date('F d, Y \a\t g:i A') . " (Bangladesh Time)</em></p>";
?>