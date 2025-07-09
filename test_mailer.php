<?php
// Test script for PHP Mailer functionality
require_once 'config/conn.php';
require_once 'config/mail_config.php';

echo "<h2>PHP Mailer Test</h2>";

// Test 1: Check if PHPMailer is available
$vendorPath = 'vendor/autoload.php';
if (!file_exists($vendorPath)) {
    echo "<p style='color: red;'>✗ PHPMailer not found. Check if vendor/autoload.php exists.</p>";
    echo "<p>Current directory: " . getcwd() . "</p>";
    echo "<p>Looking for: " . realpath($vendorPath) . "</p>";
    exit;
}

try {
    require_once $vendorPath;
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    echo "<p style='color: green;'>✓ PHPMailer is available</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ PHPMailer Error: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check mail configuration
echo "<h3>Mail Configuration:</h3>";
echo "<p>SMTP Host: " . SMTP_HOST . "</p>";
echo "<p>SMTP Port: " . SMTP_PORT . "</p>";
echo "<p>SMTP Username: " . SMTP_USERNAME . "</p>";
echo "<p>SMTP Secure: " . SMTP_SECURE . "</p>";

// Check if configuration is still using default values
if (SMTP_USERNAME === 'your-email@gmail.com' || SMTP_PASSWORD === 'your-app-password') {
    echo "<p style='color: orange;'>⚠️ Warning: You're still using default email settings. Please update config/mail_config.php</p>";
} else {
    echo "<p style='color: green;'>✓ Email configuration appears to be customized</p>";
}

// Test 3: Test mailer connection
echo "<h3>Testing Mailer Connection:</h3>";
$mail = getMailer();
if ($mail) {
    echo "<p style='color: green;'>✓ Mailer instance created successfully</p>";
    
    // Test SMTP connection (without sending email)
    try {
        $mail->smtpConnect();
        echo "<p style='color: green;'>✓ SMTP connection test successful</p>";
        $mail->smtpClose();
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ SMTP connection failed: " . $e->getMessage() . "</p>";
        echo "<p style='color: orange;'>💡 Common issues:</p>";
        echo "<ul>";
        echo "<li>Check your email and password</li>";
        echo "<li>For Gmail, use App Password instead of regular password</li>";
        echo "<li>Enable 2-factor authentication for Gmail</li>";
        echo "<li>Check if your network allows SMTP connections</li>";
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>✗ Failed to create mailer instance</p>";
    echo "<p>Check the error logs for more details.</p>";
}

// Test 4: Test sending a simple email
echo "<h3>Testing Email Sending:</h3>";
echo "<form method='post' style='margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 8px;'>";
echo "<p><strong>Send Test Email:</strong></p>";
echo "<p>Email: <input type='email' name='test_email' placeholder='your-email@example.com' style='padding: 5px; width: 250px;' required></p>";
echo "<p><input type='submit' value='Send Test Email' style='background: #ff9800; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;'></p>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    $testToken = bin2hex(random_bytes(16));
    $emailSent = sendResetPasswordEmail($testEmail, $testToken, 'TestUser');

    if ($emailSent) {
        echo "<p style='color: green;'>✓ Test email sent successfully to $testEmail</p>";
        echo "<p style='color: blue;'>📧 Check your inbox and spam folder for the test email.</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to send test email to $testEmail</p>";
        echo "<p style='color: orange;'>💡 Check the error logs for more details.</p>";
    }
}

echo "<h3>Setup Instructions:</h3>";
echo "<div style='background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
echo "<h4>Step 1: Configure Email Settings</h4>";
echo "<ol>";
echo "<li>Open <code>config/mail_config.php</code> in your code editor</li>";
echo "<li>Update these settings with your email credentials:</li>";
echo "<ul>";
echo "<li><code>SMTP_USERNAME</code> = your email address</li>";
echo "<li><code>SMTP_PASSWORD</code> = your email password or app password</li>";
echo "<li><code>FROM_EMAIL</code> = your email address</li>";
echo "</ul>";
echo "</ol>";

echo "<h4>Step 2: Gmail Setup (Recommended)</h4>";
echo "<ol>";
echo "<li>Go to <a href='https://myaccount.google.com/security' target='_blank'>Google Account Security</a></li>";
echo "<li>Enable 2-Step Verification if not already enabled</li>";
echo "<li>Go to 'App passwords' (under 2-Step Verification)</li>";
echo "<li>Generate a new app password for 'Mail'</li>";
echo "<li>Use this 16-character password as your <code>SMTP_PASSWORD</code></li>";
echo "</ol>";

echo "<h4>Step 3: Test Configuration</h4>";
echo "<ol>";
echo "<li>Save the changes to <code>config/mail_config.php</code></li>";
echo "<li>Refresh this page to run the tests again</li>";
echo "<li>Use the test email form above to verify everything works</li>";
echo "</ol>";
echo "</div>";

echo "<h3>Common SMTP Settings:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Provider</th><th>SMTP Host</th><th>Port</th><th>Security</th></tr>";
echo "<tr><td>Gmail</td><td>smtp.gmail.com</td><td>587</td><td>TLS</td></tr>";
echo "<tr><td>Outlook/Hotmail</td><td>smtp-mail.outlook.com</td><td>587</td><td>TLS</td></tr>";
echo "<tr><td>Yahoo</td><td>smtp.mail.yahoo.com</td><td>587</td><td>TLS</td></tr>";
echo "<tr><td>Yahoo</td><td>smtp.mail.yahoo.com</td><td>465</td><td>SSL</td></tr>";
echo "</table>";
?> 