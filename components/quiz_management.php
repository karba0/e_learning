<?php
session_start();
include '../config/conn.php';

$message = '';
$error = '';

// Handle quiz deletion
if (isset($_POST['delete_quiz'])) {
    $quiz_id = intval($_POST['quiz_id']);
    $sql = "DELETE FROM quizzes WHERE id = $quiz_id";
    if (mysqli_query($conn, $sql)) {
        $message = "Quiz deleted successfully!";
    } else {
        $error = "Error deleting quiz: " . mysqli_error($conn);
    }
}

// Handle quiz activation/deactivation
if (isset($_POST['toggle_status'])) {
    $quiz_id = intval($_POST['quiz_id']);
    $new_status = $_POST['new_status'] == '1' ? 0 : 1;
    $sql = "UPDATE quizzes SET is_active = $new_status WHERE id = $quiz_id";
    if (mysqli_query($conn, $sql)) {
        $message = "Quiz status updated successfully!";
    } else {
        $error = "Error updating quiz status: " . mysqli_error($conn);
    }
}

// Fetch all quizzes
$sql = "SELECT q.*, COUNT(qq.id) as question_count, COUNT(qr.id) as attempt_count 
        FROM quizzes q 
        LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id 
        LEFT JOIN quiz_results qr ON q.id = qr.quiz_id 
        GROUP BY q.id 
        ORDER BY q.created_date DESC";
$result = mysqli_query($conn, $sql);
$quizzes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $quizzes[] = $row;
}

// Fetch all chapters (files) and build a map: file_id => chapter_number
$chapter_result = mysqli_query($conn, "SELECT id FROM files ORDER BY id ASC");
$chapter_map = [];
$chapter_num = 1;
while ($row = mysqli_fetch_assoc($chapter_result)) {
    $chapter_map[$row['id']] = $chapter_num++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Management</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.18);
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .page-title {
            color: #185a9d;
            font-size: 2rem;
            font-weight: 800;
        }
        .btn {
            background: #43cea2;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #3bb890;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }
        .quiz-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            background: #f9f9f9;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .quiz-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .quiz-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #185a9d;
            margin-bottom: 10px;
        }
        .quiz-description {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        .quiz-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        .stat {
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            text-align: center;
            font-size: 0.9rem;
        }
        .stat-label {
            color: #666;
            font-size: 0.8rem;
        }
        .stat-value {
            font-weight: 700;
            color: #185a9d;
        }
        .quiz-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
            display: inline-block;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .empty-state h3 {
            color: #185a9d;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .quiz-grid {
                grid-template-columns: 1fr;
            }
            .quiz-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">📊 Quiz Management</h1>
            <div>
                <a href="quiz_upload_form.php" class="btn">➕ Create New Quiz</a>
                <a href="../admin_panel.php" class="btn btn-secondary">🏠 Back to Home</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (empty($quizzes)): ?>
            <div class="empty-state">
                <h3>No quizzes found</h3>
                <p>Create your first quiz to get started!</p>
                <a href="quiz_upload_form.php" class="btn">Create Quiz</a>
            </div>
        <?php else: ?>
            <div class="quiz-grid">
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="quiz-card">
                        <div class="status-badge <?php echo $quiz['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $quiz['is_active'] ? '✅ Active' : '❌ Inactive'; ?>
                        </div>
                        
                        <div class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></div>
                        <div class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></div>
                        <div class="quiz-chapter">Chapter: Chapter <?php echo isset($chapter_map[$quiz['course_id']]) ? $chapter_map[$quiz['course_id']] : 'N/A'; ?></div>
                        
                        <div class="quiz-stats">
                            <div class="stat">
                                <div class="stat-value"><?php echo $quiz['question_count']; ?></div>
                                <div class="stat-label">Questions</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value"><?php echo $quiz['attempt_count']; ?></div>
                                <div class="stat-label">Attempts</div>
                            </div>
                        </div>
                        
                        <div class="quiz-actions">
                            <a href="quiz_template_db.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn">📝 Take Quiz</a>
                            <a href="edit_quiz.php?quiz_id=<?php echo $quiz['id']; ?>" class="btn btn-warning">✏️ Edit</a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                <input type="hidden" name="new_status" value="<?php echo $quiz['is_active']; ?>">
                                <button type="submit" name="toggle_status" class="btn btn-warning">
                                    <?php echo $quiz['is_active'] ? '⏸️ Deactivate' : '▶️ Activate'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this quiz? This action cannot be undone.');">
                                <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                                <button type="submit" name="delete_quiz" class="btn btn-danger">🗑️ Delete</button>
                            </form>
                        </div>
                        
                        <div style="margin-top: 10px; font-size: 0.8rem; color: #666;">
                            Created: <?php echo date('M j, Y', strtotime($quiz['created_date'])); ?>
                            <?php if ($quiz['created_by']): ?>
                                by <?php echo htmlspecialchars($quiz['created_by']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 