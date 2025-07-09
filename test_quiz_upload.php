<?php
session_start();
include 'config/conn.php';

// Check if database tables exist
$tables_exist = true;
$required_tables = ['quizzes', 'quiz_questions', 'quiz_results'];

foreach ($required_tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) == 0) {
        $tables_exist = false;
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz System Test</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.18);
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            color: #185a9d;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        .status-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #43cea2;
        }
        .status-card.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .btn {
            background: #43cea2;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #3bb890;
            color: white;
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
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .feature-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            background: #f9f9f9;
            text-align: center;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .feature-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #185a9d;
            margin-bottom: 10px;
        }
        .feature-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .setup-instructions {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .setup-instructions h3 {
            color: #1976d2;
            margin-bottom: 15px;
        }
        .setup-instructions ol {
            color: #333;
            line-height: 1.6;
        }
        .setup-instructions li {
            margin-bottom: 8px;
        }
        .code-block {
            background: #f5f5f5;
            border-radius: 5px;
            padding: 10px;
            font-family: monospace;
            margin: 10px 0;
            border-left: 3px solid #43cea2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">🧪 Quiz System Test</h1>
            <p class="subtitle">Test and manage your quiz system</p>
        </div>

        <?php if (!$tables_exist): ?>
            <div class="status-card error">
                <h3>⚠️ Database Setup Required</h3>
                <p>The quiz database tables are not set up yet. Please follow the setup instructions below.</p>
            </div>
            
            <div class="setup-instructions">
                <h3>📋 Setup Instructions</h3>
                <ol>
                    <li><strong>Import the database schema:</strong>
                        <div class="code-block">
                            Run the SQL from: quiz_database_setup.sql
                        </div>
                    </li>
                    <li><strong>Or manually create the tables:</strong>
                        <div class="code-block">
                            phpMyAdmin → login_db → SQL → Paste the contents of quiz_database_setup.sql
                        </div>
                    </li>
                    <li><strong>Refresh this page</strong> after the database setup is complete.</li>
                </ol>
            </div>
        <?php else: ?>
            <div class="status-card">
                <h3>✅ Database Ready</h3>
                <p>All quiz database tables are properly set up and ready to use!</p>
            </div>
        <?php endif; ?>

        <div class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <div class="feature-title">Create Quiz</div>
                <div class="feature-description">
                    Upload new quizzes with multiple choice questions. Add as many questions as you need with 4 options each.
                </div>
                <a href="components/quiz_upload_form.php" class="btn">Create New Quiz</a>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <div class="feature-title">Manage Quizzes</div>
                <div class="feature-description">
                    View all quizzes, activate/deactivate them, and see statistics like question count and attempts.
                </div>
                <a href="components/quiz_management.php" class="btn">Manage Quizzes</a>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <div class="feature-title">Take Quiz</div>
                <div class="feature-description">
                    Test the new database-based quiz system with sample quizzes. Includes timer and progress tracking.
                </div>
                <a href="components/quiz_template_db.php?quiz_id=1" class="btn">Take Sample Quiz</a>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📈</div>
                <div class="feature-title">View Results</div>
                <div class="feature-description">
                    Check quiz results and rankings. See how users perform on different quizzes.
                </div>
                <a href="components/view_scores.php" class="btn">View Results</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn btn-secondary">🏠 Back to Home</a>
            <?php if ($tables_exist): ?>
                <a href="components/quiz_upload_form.php" class="btn">🚀 Quick Start - Create Quiz</a>
            <?php endif; ?>
        </div>

        <?php if ($tables_exist): ?>
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                <h3>📋 Quick Database Info</h3>
                <?php
                // Show some quick stats
                $quiz_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM quizzes"))[0];
                $question_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM quiz_questions"))[0];
                $result_count = mysqli_fetch_array(mysqli_query($conn, "SELECT COUNT(*) FROM quiz_results"))[0];
                ?>
                <p><strong>Total Quizzes:</strong> <?php echo $quiz_count; ?></p>
                <p><strong>Total Questions:</strong> <?php echo $question_count; ?></p>
                <p><strong>Total Quiz Attempts:</strong> <?php echo $result_count; ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 