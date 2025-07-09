<?php
@include '../config/conn.php';
@include '../config/mail_config.php';
$message = '';
$reset_link = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div style="color:#e74c3c; margin-bottom:12px;">Please enter a valid email address.</div>';
    } else {
        // Check if email is registered
        $safe_email = mysqli_real_escape_string($conn, $email);
        $sql = "SELECT * FROM users WHERE email = '$safe_email' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) === 0) {
            $message = '<div style="color:#e74c3c; margin-bottom:12px;">This email is not registered.</div>';
        } else {
            $user = mysqli_fetch_assoc($result);
            $username = $user['username'] ?? '';
            
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Create password_resets table if not exists
            $conn->query("CREATE TABLE IF NOT EXISTS password_resets (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255), token VARCHAR(64), expires_at DATETIME)");
            
            // Remove old tokens for this email
            $conn->query("DELETE FROM password_resets WHERE email = '$safe_email'");
            
            // Save the token
            $conn->query("INSERT INTO password_resets (email, token, expires_at) VALUES ('$safe_email', '$token', '$expires')");
            
            // Send email using PHP Mailer
            $emailSent = sendResetPasswordEmail($email, $token, $username);
            
            if ($emailSent) {
                $message = '<div style="color:#43cea2; margin-bottom:12px;">✓ A password reset link has been sent to your email address. Please check your inbox and spam folder.</div>';
                // Log the reset link for debugging (remove in production)
                $reset_link = "http://localhost/project2/e_learning/auth/reset_password.php?token=$token";
                file_put_contents(__DIR__ . '/reset_link_log.txt', date('Y-m-d H:i:s') . ' - ' . $reset_link . PHP_EOL, FILE_APPEND);
            } else {
                $message = '<div style="color:#e74c3c; margin-bottom:12px;">✗ Failed to send reset email. Please check your email configuration or try again later.</div>';
                // For debugging, show the link
                $reset_link = "http://localhost/project2/e_learning/auth/reset_password.php?token=$token";
                $message .= '<div style="color:#ff9800; margin-bottom:12px; font-size:0.9rem;">Debug: Reset link generated but email failed to send. Check mail configuration.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f4f8fb;
            min-height: 100vh;
            margin: 0;
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
        }
        .forgot-container {
            max-width: 420px;
            margin: 80px auto 0 auto;
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.13);
            padding: 38px 32px 28px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .forgot-title {
            font-size: 2rem;
            font-weight: 800;
            color: #185a9d;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .forgot-desc {
            color: #388e8e;
            font-size: 1.08rem;
            margin-bottom: 28px;
            text-align: center;
        }
        .form-floating {
            width: 100%;
            margin-bottom: 22px;
            position: relative;
        }
        .form-control {
            width: 100%;
            padding: 14px 12px;
            border-radius: 8px;
            border: 1px solid #b2c9e2;
            font-size: 1rem;
            background: #f0f7fa;
            outline: none;
            transition: border 0.2s;
        }
        .form-control:focus {
            border: 1.5px solid #185a9d;
        }
        .form-label {
            position: absolute;
            left: 14px;
            top: 10px;
            color: #185a9d;
            font-size: 1rem;
            pointer-events: none;
            transition: 0.2s;
            background: #fff;
            padding: 0 4px;
        }
        .form-control:focus + .form-label,
        .form-control:not(:placeholder-shown) + .form-label {
            top: -12px;
            left: 10px;
            font-size: 0.92rem;
            color: #7f53ff;
            background: #fff;
        }
        .forgot-btn {
            width: 100%;
            background: #ff9800;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 14px 0;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-bottom: 12px;
            transition: background 0.2s;
        }
        .forgot-btn:hover {
            background: #185a9d;
        }
        .back-link {
            color: #185a9d;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.02rem;
            margin-top: 10px;
            display: inline-block;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #ff9800;
            text-decoration: underline;
        }
        .reset-link-demo {
            margin-top: 18px;
            background: #eaf6fb;
            color: #185a9d;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 1.01rem;
            word-break: break-all;
        }
        @media (max-width: 600px) {
            .forgot-container {
                padding: 18px 6px 12px 6px;
                border-radius: 12px;
            }
            .forgot-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-title">Forgot Password?</div>
        <div class="forgot-desc">Enter your email address and we\'ll send you a link to reset your password.</div>
        <?php if ($message) echo $message; ?>
        <?php
// For production, do NOT display the reset link on the page
// if ($reset_link) echo '<div class="reset-link-demo">Reset link (local test): <a href="' . $reset_link . '">' . $reset_link . '</a></div>';
?>
        <form method="post" action="">
            <div class="form-floating">
                <input type="email" name="email" id="email" class="form-control" placeholder=" " required>
                <label for="email" class="form-label">Email Address</label>
            </div>
            <button type="submit" class="forgot-btn">Send Reset Link</button>
        </form>
        <a href="login.php" class="back-link">&larr; Back to Login</a>
    </div>
</body>
</html> 