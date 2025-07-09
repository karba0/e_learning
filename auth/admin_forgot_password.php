<?php
session_start();
@include '../config/conn.php';

$success = '';
$error = '';

if (isset($_POST['submit'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    if ($email === 'karbadeditz@gmail.com') {
        $query = "SELECT * FROM users WHERE email = 'karbadeditz@gmail.com' LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) === 1) {
            $success = 'A password reset link would be sent to your email (feature not implemented).';
        } else {
            $error = 'No admin account found with that email.';
        }
    } else {
        $error = 'No admin account found with that email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', Arial, sans-serif; background: var(--light, #f0e4de); }
        .login-card { border-radius: 18px; box-shadow: 0 8px 32px rgba(24,90,157,0.10); background: #fff; }
        .login-title { color: var(--primary, #f69050); font-weight: 800; font-size: 2rem; margin-bottom: 24px; font-family: 'Nunito', Arial, sans-serif; }
        .login-link, .login-link:visited { color: var(--primary, #f69050); text-decoration: none; font-family: 'Nunito', Arial, sans-serif; }
        .login-link:hover { text-decoration: underline; }
        .alert-danger { background: #ffe5e0; color: #d9534f; border: none; font-family: 'Nunito', Arial, sans-serif; }
        .alert-success { background: #e6fff2; color: #28a745; border: none; font-family: 'Nunito', Arial, sans-serif; }
        .form-control, label, .btn { font-family: 'Nunito', Arial, sans-serif; }
    </style>
</head>
<body>
    <div class="container-xxl py-2 mt-4">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-6">
                    <form class="login-card shadow p-4" style="max-width: 550px; margin:auto;" method="post" action="">
                        <div class="text-center">
                            <div class="login-title fw-semi-bold">Admin Forgot Password</div>
                        </div>
                        <?php if ($success): ?>
                            <div class="alert alert-success text-center"><?php echo $success; ?></div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Admin Email" required>
                            <label for="email">Admin Email</label>
                        </div>
                        <button class="btn btn-primary w-100 py-2 fw-semi-bold" type="submit" name="submit">Send Reset Link</button>
                        <div class="mt-3 text-center">
                            <a href="admin_login.php" class="login-link">Back to Admin Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 