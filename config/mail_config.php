<?php
// Mail configuration for PHP Mailer
// You need to configure these settings based on your email provider

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');  // Change to your SMTP server
define('SMTP_PORT', 587);               // SMTP port (587 for TLS, 465 for SSL)
define('SMTP_USERNAME', 'karkibam586@gmail.com');  // Your email address
define('SMTP_PASSWORD', 'penxbwvblbcbyhrr');     // Your email password or app password
define('SMTP_SECURE', 'tls');           // 'tls' or 'ssl'

// Email settings
define('FROM_EMAIL', 'karkibam586@gmail.com');
define('FROM_NAME', 'E-Learning Platform');

// Reset link settings
define('RESET_LINK_BASE_URL', 'http://localhost/project2/e_learning/auth/reset_password.php');

// Function to get mailer instance
function getMailer() {
    // Fix the path to vendor autoload
    $vendorPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($vendorPath)) {
        error_log("Vendor autoload not found at: " . $vendorPath);
        return false;
    }
    
    require_once $vendorPath;
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Enable debug output for troubleshooting
        $mail->SMTPDebug = 0; // Set to 2 for debugging
        $mail->Debugoutput = 'error_log';
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        // Connection timeout settings
        $mail->Timeout = 30;
        $mail->SMTPKeepAlive = false;
        
        // Default settings
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->isHTML(true);
        
        return $mail;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return false;
    }
}

// Function to send reset password email
function sendResetPasswordEmail($email, $token, $username = '') {
    $mail = getMailer();
    if (!$mail) {
        return false;
    }
    
    try {
        $mail->addAddress($email);
        $mail->Subject = 'Password Reset Request - E-Learning Platform';
        
        $resetLink = RESET_LINK_BASE_URL . '?token=' . $token;
        
        $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #185a9d 0%, #ff9800 100%); padding: 30px; text-align: center;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>Password Reset Request</h1>
            </div>
            
            <div style='padding: 30px; background: #f9f9f9;'>
                <h2 style='color: #185a9d; margin-top: 0;'>Hello" . ($username ? " $username" : "") . ",</h2>
                
                <p style='color: #333; font-size: 16px; line-height: 1.6;'>
                    We received a request to reset your password for your E-Learning Platform account.
                </p>
                
                <p style='color: #333; font-size: 16px; line-height: 1.6;'>
                    Click the button below to reset your password:
                </p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$resetLink' 
                       style='background: #ff9800; color: white; padding: 15px 30px; text-decoration: none; 
                              border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block;'>
                        Reset Password
                    </a>
                </div>
                
                <p style='color: #666; font-size: 14px; line-height: 1.6;'>
                    If the button doesn't work, copy and paste this link into your browser:<br>
                    <a href='$resetLink' style='color: #185a9d;'>$resetLink</a>
                </p>
                
                <p style='color: #666; font-size: 14px; line-height: 1.6;'>
                    This link will expire in 24 hours for security reasons.
                </p>
                
                <p style='color: #666; font-size: 14px; line-height: 1.6;'>
                    If you didn't request this password reset, please ignore this email. Your password will remain unchanged.
                </p>
                
                <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
                
                <p style='color: #999; font-size: 12px; text-align: center;'>
                    This is an automated message from the E-Learning Platform. Please do not reply to this email.
                </p>
            </div>
        </div>";
        
        $mail->Body = $htmlBody;
        
        // Plain text version
        $textBody = "Password Reset Request\n\n";
        $textBody .= "Hello" . ($username ? " $username" : "") . ",\n\n";
        $textBody .= "We received a request to reset your password for your E-Learning Platform account.\n\n";
        $textBody .= "Click the following link to reset your password:\n";
        $textBody .= "$resetLink\n\n";
        $textBody .= "This link will expire in 24 hours for security reasons.\n\n";
        $textBody .= "If you didn't request this password reset, please ignore this email.\n\n";
        $textBody .= "Best regards,\nE-Learning Platform Team";
        
        $mail->AltBody = $textBody;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}
?> 