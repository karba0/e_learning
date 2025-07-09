<?php
session_start();
@include '../config/conn.php';

$success = '';
$error = '';

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $password = md5($_POST['password'] ?? '');
    $role = 'admin';
    if ($name && $email && $password) {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $insert = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
            if (mysqli_query($conn, $insert)) {
                $success = 'Admin account created successfully!';
            } else {
                $error = 'Error creating admin account.';
            }
        }
    } else {
        $error = 'All fields are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin Account</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container-xxl py-2 mt-4">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-6">
                    <form class="shadow p-4 bg-white" method="post" action="">
                        <h1 class="mb-4 text-center">Add Admin Account</h1>
                        <?php if ($success): ?>
                            <div class="alert alert-success text-center"><?php echo $success; ?></div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <div class="form-floating mb-3">
                            <input type="text" name="name" class="form-control" id="name" placeholder="Admin Name" required>
                            <label for="name">Admin Name</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="email" placeholder="Admin Email" required>
                            <label for="email">Admin Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        <button class="btn btn-primary w-100 py-2" type="submit" name="submit">Add Admin</button>
                        <div class="mt-3 text-center">
                            <a href="admin_login.php">Back to Admin Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 