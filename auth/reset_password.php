<?php
@include '../config/conn.php';
$message = '';
$show_form = false;
$token = $_GET['token'] ?? '';
$email = '';

// Debug: log the token and DB row
file_put_contents(__DIR__ . '/debug_reset_token.txt', 'Token: ' . $token . "\n", FILE_APPEND);

if ($token) {
    // Validate token
    $safe_token = mysqli_real_escape_string($conn, $token);
    $sql = "SELECT * FROM password_resets WHERE token = '$safe_token' AND expires_at > NOW() LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    file_put_contents(__DIR__ . '/debug_reset_token.txt', 'DB Row: ' . print_r($row, true) . "\n", FILE_APPEND);
    if ($row) {
        $email = $row['email'];
        $show_form = true;
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $cpassword = $_POST['cpassword'] ?? '';
            if (strlen($password) < 8) {
                $message = '<div style="color:#e74c3c; margin-bottom:12px;">Password must be at least 8 characters.</div>';
            } elseif ($password !== $cpassword) {
                $message = '<div style="color:#e74c3c; margin-bottom:12px;">Passwords do not match.</div>';
            } else {
                $hashed = md5($password); // Use password_hash in production!
                $safe_email = mysqli_real_escape_string($conn, $email);
                $conn->query("UPDATE users SET password = '$hashed' WHERE email = '$safe_email'");
                $conn->query("DELETE FROM password_resets WHERE email = '$safe_email'");
                $message = '<div style="color:#43cea2; margin-bottom:12px;">Your password has been reset. <a href=\'login.php\'>Login</a></div>';
                $show_form = false;
            }
        }
    } else {
        $message = '<div style="color:#e74c3c; margin-bottom:12px;">Invalid or expired reset link.</div>';
    }
} else {
    $message = '<div style="color:#e74c3c; margin-bottom:12px;">No reset token provided.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f4f8fb;
            min-height: 100vh;
            margin: 0;
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
        }
        .reset-container {
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
        .reset-title {
            font-size: 2rem;
            font-weight: 800;
            color: #185a9d;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .reset-desc {
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
        .reset-btn {
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
        .reset-btn:hover {
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
        @media (max-width: 600px) {
            .reset-container {
                padding: 18px 6px 12px 6px;
                border-radius: 12px;
            }
            .reset-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-title">Reset Password</div>
        <div class="reset-desc">Enter your new password below.</div>
        <?php if ($message) echo $message; ?>
        <?php if ($show_form): ?>
        <form method="post" action="">
            <div class="form-floating">
                <input type="password" name="password" id="password" class="form-control" placeholder=" " minlength="8" required>
                <label for="password" class="form-label">New Password</label>
            </div>
            <div class="form-floating">
                <input type="password" name="cpassword" id="cpassword" class="form-control" placeholder=" " minlength="8" required>
                <label for="cpassword" class="form-label">Confirm Password</label>
            </div>
            <button type="submit" class="reset-btn">Reset Password</button>
        </form>
        <?php endif; ?>
        <a href="login.php" class="back-link">&larr; Back to Login</a>
    </div>
</body>
</html> 