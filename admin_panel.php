<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: auth/admin_login.php');
    exit();
}
include 'config/conn.php';

// --- Dashboard Stats ---
$user_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0] ?? 0;
$quiz_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM quizzes"))[0] ?? 0;
$file_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM files"))[0] ?? 0;
$attempt_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM quiz_results"))[0] ?? 0;

// --- Recent Quizzes ---
$recent_quizzes = mysqli_query($conn, "SELECT * FROM quizzes ORDER BY created_date DESC LIMIT 5");
// --- Recent Notes ---
$recent_notes = mysqli_query($conn, "SELECT * FROM files ORDER BY upload_date DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #43cea2 0%, #185a9d 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.18);
            padding: 36px 32px 32px 32px;
        }
        .admin-title {
            color: #185a9d;
            font-size: 2.2rem;
            font-weight: 800;
            font-family: 'Nunito', Arial, sans-serif;
        }
        .logout-btn {
            background: #43cea2;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            font-family: 'Nunito', Arial, sans-serif;
        }
        .logout-btn:hover {
            background: #3bb890;
            color: #fff;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 36px;
        }
        .stat-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 28px 18px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(24,90,157,0.07);
            font-family: 'Nunito', Arial, sans-serif;
        }
        .stat-label {
            color: #43cea2;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .stat-value {
            font-size: 2.1rem;
            font-weight: 800;
            color: #185a9d;
        }
        .actions {
            display: flex;
            gap: 18px;
            margin-bottom: 36px;
        }
        .action-btn {
            background: #43cea2;
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
            display: inline-block;
            font-family: 'Nunito', Arial, sans-serif;
        }
        .action-btn:hover {
            background: #3bb890;
            color: #fff;
        }
        .section {
            margin-bottom: 36px;
        }
        .section-title {
            color: #185a9d;
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 16px;
            font-family: 'Nunito', Arial, sans-serif;
        }
        .list-table {
            width: 100%;
            border-collapse: collapse;
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            font-family: 'Nunito', Arial, sans-serif;
        }
        .list-table th, .list-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
        }
        .list-table th {
            background: #e0f7f4;
            color: #185a9d;
            font-weight: 700;
        }
        .list-table tr:last-child td {
            border-bottom: none;
        }
        .manage-link {
            color: #43cea2;
            text-decoration: none;
            font-weight: 700;
            margin-right: 10px;
        }
        .manage-link:hover {
            color: #185a9d;
            text-decoration: underline;
        }
        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header d-flex justify-content-between align-items-center mb-4">
            <div class="admin-title">Admin Panel</div>
            <form method="post" action="auth/logout.php" style="margin:0;">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo $user_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Quizzes</div>
                <div class="stat-value"><?php echo $quiz_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Notes</div>
                <div class="stat-value"><?php echo $file_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Quiz Attempts</div>
                <div class="stat-value"><?php echo $attempt_count; ?></div>
            </div>
        </div>
        <div class="actions">
            <a href="components/quiz_management.php" class="action-btn">Manage Quizzes</a>
            <a href="components/quiz_upload_form.php" class="action-btn">Upload Quiz</a>
            <a href="components/upload_form.html" class="action-btn">Add Notes</a>
        </div>
        <div class="section">
            <div class="section-title">Quizzes</div>
            <table class="list-table">
                <tr><th>Title</th><th>Description</th><th>Status</th><th>Actions</th></tr>
                <?php while ($quiz = mysqli_fetch_assoc($recent_quizzes)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                    <td><?php echo htmlspecialchars($quiz['description']); ?></td>
                    <td><?php echo $quiz['is_active'] ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <a href="components/quiz_management.php" class="manage-link">Manage</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="section">
            <div class="section-title">Notes</div>
            <table class="list-table">
                <tr><th>File Name</th><th>Description</th><th>Uploaded</th><th>Actions</th></tr>
                <?php while ($note = mysqli_fetch_assoc($recent_notes)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($note['filename']); ?></td>
                    <td><?php echo htmlspecialchars($note['description']); ?></td>
                    <td><?php echo htmlspecialchars($note['upload_date']); ?></td>
                    <td>
                        <a href="components/upload_form.html" class="manage-link">Manage</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="section">
            <div class="section-title">Users</div>
            <table class="list-table">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <?php
                    // Check if 'role' and 'created_at' columns exist
                    $user_columns = [];
                    $columns_result = mysqli_query($conn, "SHOW COLUMNS FROM users");
                    while ($col = mysqli_fetch_assoc($columns_result)) {
                        $user_columns[] = $col['Field'];
                    }
                    $has_role = in_array('role', $user_columns);
                    $has_created_at = in_array('created_at', $user_columns);
                    if ($has_role) echo '<th>Role</th>';
                    if ($has_created_at) echo '<th>Registered</th>';
                    ?>
                </tr>
                <?php
                if ($has_role) {
                    $admins = mysqli_query($conn, "SELECT * FROM users WHERE LOWER(role) = 'admin' ORDER BY id DESC");
                    $users = mysqli_query($conn, "SELECT * FROM users WHERE LOWER(role) != 'admin' OR role IS NULL OR role = '' ORDER BY id DESC");
                    while ($user = mysqli_fetch_assoc($admins)):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($user['name']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span style="display:inline-block; background:#43cea2; color:#fff; border-radius:6px; padding:2px 8px; font-size:0.9em;">Admin</span>
                    </td>
                    <?php if ($has_created_at) echo '<td>' . htmlspecialchars($user['created_at']) . '</td>'; ?>
                </tr>
                <?php endwhile; while ($user = mysqli_fetch_assoc($users)):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($user['name']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span style="display:inline-block; background:#3498db; color:#fff; border-radius:6px; padding:2px 8px; font-size:0.9em;">User</span>
                    </td>
                    <?php if ($has_created_at) echo '<td>' . htmlspecialchars($user['created_at']) . '</td>'; ?>
                </tr>
                <?php endwhile;
                } else {
                    $all_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
                    while ($user = mysqli_fetch_assoc($all_users)):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($user['name']); ?>
                        <span style="display:inline-block; background:#3498db; color:#fff; border-radius:6px; padding:2px 8px; font-size:0.9em; margin-left:6px;">User</span>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <?php if ($has_created_at) echo '<td>' . htmlspecialchars($user['created_at']) . '</td>'; ?>
                </tr>
                <?php endwhile;
                }
                ?>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 