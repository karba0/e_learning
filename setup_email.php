<?php
// Email Setup Script
// This script helps you configure email settings for password reset functionality

session_start();

$message = '';
$config_updated = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = trim($_POST['smtp_port'] ?? '');
    $smtp_username = trim($_POST['smtp_username'] ?? '');
    $smtp_password = trim($_POST['smtp_password'] ?? '');
    $smtp_secure = trim($_POST['smtp_secure'] ?? '');
    $from_email = trim($_POST['from_email'] ?? '');
    
    if (empty($smtp_host) || empty($smtp_port) || empty($smtp_username) || empty($smtp_password) || empty($from_email)) {
        $message = '<div style="color: red;">Please fill in all required fields.</div>';
    } else {
        // Read the current config file
        $config_file = 'config/mail_config.php';
        $config_content = file_get_contents($config_file);
        
        // Update the configuration values
        $config_content = preg_replace("/define\('SMTP_HOST',\s*'[^']*'\);/", "define('SMTP_HOST', '$smtp_host');", $config_content);
        $config_content = preg_replace("/define\('SMTP_PORT',\s*[^;]*\);/", "define('SMTP_PORT', $smtp_port);", $config_content);
        $config_content = preg_replace("/define\('SMTP_USERNAME',\s*'[^']*'\);/", "define('SMTP_USERNAME', '$smtp_username');", $config_content);
        $config_content = preg_replace("/define\('SMTP_PASSWORD',\s*'[^']*'\);/", "define('SMTP_PASSWORD', '$smtp_password');", $config_content);
        $config_content = preg_replace("/define\('SMTP_SECURE',\s*'[^']*'\);/", "define('SMTP_SECURE', '$smtp_secure');", $config_content);
        $config_content = preg_replace("/define\('FROM_EMAIL',\s*'[^']*'\);/", "define('FROM_EMAIL', '$from_email');", $config_content);
        
        // Write the updated config back to file
        if (file_put_contents($config_file, $config_content)) {
            $message = '<div style="color: green;">✓ Email configuration updated successfully!</div>';
            $config_updated = true;
        } else {
            $message = '<div style="color: red;">✗ Failed to update configuration file. Please check file permissions.</div>';
        }
    }
}

// Read current config values
$current_config = [];
if (file_exists('config/mail_config.php')) {
    $config_content = file_get_contents('config/mail_config.php');
    preg_match("/define\('SMTP_HOST',\s*'([^']*)'\);/", $config_content, $matches);
    $current_config['smtp_host'] = $matches[1] ?? '';
    preg_match("/define\('SMTP_PORT',\s*([^;]*)\);/", $config_content, $matches);
    $current_config['smtp_port'] = $matches[1] ?? '';
    preg_match("/define\('SMTP_USERNAME',\s*'([^']*)'\);/", $config_content, $matches);
    $current_config['smtp_username'] = $matches[1] ?? '';
    preg_match("/define\('SMTP_PASSWORD',\s*'([^']*)'\);/", $config_content, $matches);
    $current_config['smtp_password'] = $matches[1] ?? '';
    preg_match("/define\('SMTP_SECURE',\s*'([^']*)'\);/", $config_content, $matches);
    $current_config['smtp_secure'] = $matches[1] ?? '';
    preg_match("/define\('FROM_EMAIL',\s*'([^']*)'\);/", $config_content, $matches);
    $current_config['from_email'] = $matches[1] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Configuration Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #185a9d;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn {
            background: #ff9800;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background: #185a9d;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .preset-buttons {
            margin: 20px 0;
            text-align: center;
        }
        .preset-btn {
            background: #e9ecef;
            border: 1px solid #dee2e6;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
        }
        .preset-btn:hover {
            background: #185a9d;
            color: white;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Configuration Setup</h1>
        
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($config_updated): ?>
            <div class="info-box">
                <h3>✅ Configuration Updated!</h3>
                <p>Your email settings have been updated. You can now:</p>
                <ul>
                    <li><a href="test_mailer.php">Test the email configuration</a></li>
                    <li><a href="auth/forgot_password.php">Test the forgot password functionality</a></li>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="smtp_host">SMTP Host:</label>
                <input type="text" id="smtp_host" name="smtp_host" value="<?php echo htmlspecialchars($current_config['smtp_host'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="smtp_port">SMTP Port:</label>
                <input type="number" id="smtp_port" name="smtp_port" value="<?php echo htmlspecialchars($current_config['smtp_port'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="smtp_username">Email Address:</label>
                <input type="email" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($current_config['smtp_username'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="smtp_password">Password/App Password:</label>
                <input type="password" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($current_config['smtp_password'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="smtp_secure">Security:</label>
                <select id="smtp_secure" name="smtp_secure" required>
                    <option value="tls" <?php echo ($current_config['smtp_secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="ssl" <?php echo ($current_config['smtp_secure'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="from_email">From Email:</label>
                <input type="email" id="from_email" name="from_email" value="<?php echo htmlspecialchars($current_config['from_email'] ?? ''); ?>" required>
            </div>
            
            <div class="preset-buttons">
                <button type="button" class="preset-btn" onclick="setPreset('gmail')">Gmail</button>
                <button type="button" class="preset-btn" onclick="setPreset('outlook')">Outlook</button>
                <button type="button" class="preset-btn" onclick="setPreset('yahoo')">Yahoo</button>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn">Save Configuration</button>
                <a href="test_mailer.php" class="btn btn-secondary">Test Configuration</a>
            </div>
        </form>
        
        <div class="info-box">
            <h3>💡 Quick Setup Guide</h3>
            <p><strong>For Gmail users:</strong></p>
            <ol>
                <li>Enable 2-Step Verification in your Google Account</li>
                <li>Generate an App Password (Google Account → Security → App passwords)</li>
                <li>Use the App Password as your password field</li>
            </ol>
            <p><strong>For other providers:</strong> Use your regular email password</p>
        </div>
    </div>
    
    <script>
        function setPreset(provider) {
            const presets = {
                gmail: {
                    smtp_host: 'smtp.gmail.com',
                    smtp_port: '587',
                    smtp_secure: 'tls'
                },
                outlook: {
                    smtp_host: 'smtp-mail.outlook.com',
                    smtp_port: '587',
                    smtp_secure: 'tls'
                },
                yahoo: {
                    smtp_host: 'smtp.mail.yahoo.com',
                    smtp_port: '587',
                    smtp_secure: 'tls'
                }
            };
            
            const preset = presets[provider];
            document.getElementById('smtp_host').value = preset.smtp_host;
            document.getElementById('smtp_port').value = preset.smtp_port;
            document.getElementById('smtp_secure').value = preset.smtp_secure;
        }
    </script>
</body>
</html> 