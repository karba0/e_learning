<?php
session_start();
include '../config/conn.php';

// Check if user is admin
if (!isset($_SESSION['user_name']) || $_SESSION['user_name'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$user_name = isset($_GET['user_name']) ? $_GET['user_name'] : '';

// Get all quizzes for dropdown
$quizzes_sql = "SELECT id, title FROM quizzes ORDER BY title";
$quizzes_result = mysqli_query($conn, $quizzes_sql);
$quizzes = [];
while ($row = mysqli_fetch_assoc($quizzes_result)) {
    $quizzes[] = $row;
}

// Get all users who have taken quizzes
$users_sql = "SELECT DISTINCT user_name FROM quiz_results ORDER BY user_name";
$users_result = mysqli_query($conn, $users_sql);
$users = [];
while ($row = mysqli_fetch_assoc($users_result)) {
    $users[] = $row;
}

// Build the main query
$where_conditions = [];
$params = [];

if ($quiz_id > 0) {
    $where_conditions[] = "qr.quiz_id = $quiz_id";
}

if ($user_name) {
    $safe_user_name = mysqli_real_escape_string($conn, $user_name);
    $where_conditions[] = "qr.user_name = '$safe_user_name'";
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

$sql = "SELECT 
            qr.id as result_id,
            qr.user_name,
            qr.score,
            qr.total_questions,
            qr.submitted_date,
            q.title as quiz_title,
            qua.user_answer,
            qua.is_correct,
            qq.question_text,
            qq.option_a,
            qq.option_b,
            qq.option_c,
            qq.option_d,
            qq.correct_answer,
            qq.question_order
        FROM quiz_results qr
        JOIN quizzes q ON qr.quiz_id = q.id
        JOIN quiz_user_answers qua ON qr.id = qua.quiz_result_id
        JOIN quiz_questions qq ON qua.question_id = qq.id
        $where_clause
        ORDER BY qr.submitted_date DESC, qq.question_order";

$result = mysqli_query($conn, $sql);
$answers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $answers[] = $row;
}

// Group answers by quiz result
$grouped_answers = [];
foreach ($answers as $answer) {
    $result_id = $answer['result_id'];
    if (!isset($grouped_answers[$result_id])) {
        $grouped_answers[$result_id] = [
            'user_name' => $answer['user_name'],
            'quiz_title' => $answer['quiz_title'],
            'score' => $answer['score'],
            'total_questions' => $answer['total_questions'],
            'submitted_date' => $answer['submitted_date'],
            'answers' => []
        ];
    }
    $grouped_answers[$result_id]['answers'][] = $answer;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View All Answers</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(120deg, #43cea2 0%, #185a9d 100%);
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.18);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #185a9d 0%, #43cea2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 800;
        }
        .filters {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-weight: 600;
            color: #185a9d;
            font-size: 0.9rem;
        }
        .filter-group select, .filter-group input {
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
        }
        .btn {
            background: #43cea2;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
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
        .results-section {
            padding: 30px;
        }
        .result-container {
            margin-bottom: 40px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }
        .result-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .result-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .info-item {
            text-align: center;
        }
        .info-item .label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }
        .info-item .value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #185a9d;
        }
        .score-display {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 800;
            color: #43cea2;
        }
        .answers-list {
            padding: 20px;
        }
        .question-item {
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
        }
        .question-header {
            background: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .question-number {
            font-weight: 700;
            color: #185a9d;
        }
        .question-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .status-correct {
            background: #d4edda;
            color: #155724;
        }
        .status-incorrect {
            background: #f8d7da;
            color: #721c24;
        }
        .question-content {
            padding: 15px;
        }
        .question-text {
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        .options-grid {
            display: grid;
            gap: 8px;
        }
        .option {
            padding: 10px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .option.user-answer {
            border-color: #ffc107;
            background: #fff3cd;
        }
        .option.correct-answer {
            border-color: #28a745;
            background: #d4edda;
        }
        .option.incorrect-answer {
            border-color: #dc3545;
            background: #f8d7da;
        }
        .option-label {
            font-weight: 700;
            color: #185a9d;
            min-width: 25px;
        }
        .option-text {
            flex: 1;
        }
        .answer-indicator {
            font-size: 1rem;
            margin-left: auto;
        }
        .actions {
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        .no-results {
            text-align: center;
            padding: 50px;
            color: #666;
            font-size: 1.1rem;
        }
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            .header {
                padding: 20px;
            }
            .header h1 {
                font-size: 1.5rem;
            }
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .results-section {
                padding: 20px;
            }
            .result-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👨‍💼 Admin - View All Answers</h1>
            <p>Review all user quiz responses and performance</p>
        </div>
        
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="quiz_id">Quiz:</label>
                    <select name="quiz_id" id="quiz_id">
                        <option value="">All Quizzes</option>
                        <?php foreach ($quizzes as $quiz): ?>
                            <option value="<?php echo $quiz['id']; ?>" <?php echo $quiz_id == $quiz['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($quiz['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="user_name">User:</label>
                    <select name="user_name" id="user_name">
                        <option value="">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo htmlspecialchars($user['user_name']); ?>" <?php echo $user_name == $user['user_name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['user_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">🔍 Filter Results</button>
                <a href="admin_view_answers.php" class="btn btn-secondary">🔄 Clear Filters</a>
            </form>
        </div>
        
        <div class="results-section">
            <?php if (empty($grouped_answers)): ?>
                <div class="no-results">
                    <h3>📭 No Results Found</h3>
                    <p>No quiz answers match your current filters.</p>
                </div>
            <?php else: ?>
                <h2>📊 Quiz Results (<?php echo count($grouped_answers); ?> attempts)</h2>
                <?php foreach ($grouped_answers as $result_id => $result): ?>
                    <div class="result-container">
                        <div class="result-header">
                            <div class="result-info">
                                <div class="info-item">
                                    <div class="label">User</div>
                                    <div class="value"><?php echo htmlspecialchars($result['user_name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="label">Quiz</div>
                                    <div class="value"><?php echo htmlspecialchars($result['quiz_title']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="label">Date</div>
                                    <div class="value"><?php echo date('M d, Y H:i', strtotime($result['submitted_date'])); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="label">Time Taken</div>
                                    <div class="value"><?php echo date('H:i:s', strtotime($result['submitted_date'])); ?></div>
                                </div>
                            </div>
                            <div class="score-display">
                                Score: <?php echo $result['score']; ?>/<?php echo $result['total_questions']; ?> 
                                (<?php echo round(($result['score'] / $result['total_questions']) * 100, 1); ?>%)
                            </div>
                        </div>
                        
                        <div class="answers-list">
                            <?php foreach ($result['answers'] as $index => $answer): ?>
                                <div class="question-item">
                                    <div class="question-header">
                                        <span class="question-number">Question <?php echo $index + 1; ?></span>
                                        <span class="question-status <?php echo $answer['is_correct'] ? 'status-correct' : 'status-incorrect'; ?>">
                                            <?php echo $answer['is_correct'] ? '✅ Correct' : '❌ Incorrect'; ?>
                                        </span>
                                    </div>
                                    <div class="question-content">
                                        <div class="question-text"><?php echo htmlspecialchars($answer['question_text']); ?></div>
                                        <div class="options-grid">
                                            <?php
                                            $options = [
                                                'A' => $answer['option_a'],
                                                'B' => $answer['option_b'],
                                                'C' => $answer['option_c'],
                                                'D' => $answer['option_d']
                                            ];
                                            
                                            foreach ($options as $option_key => $option_text):
                                                $option_class = '';
                                                $indicator = '';
                                                
                                                if ($option_key == $answer['user_answer']) {
                                                    $option_class = 'user-answer';
                                                    $indicator = '👤 User Answer';
                                                }
                                                
                                                if ($option_key == $answer['correct_answer']) {
                                                    $option_class = 'correct-answer';
                                                    $indicator = '✅ Correct';
                                                }
                                                
                                                if ($option_key == $answer['user_answer'] && $option_key != $answer['correct_answer']) {
                                                    $option_class = 'incorrect-answer';
                                                    $indicator = '❌ Wrong';
                                                }
                                            ?>
                                                <div class="option <?php echo $option_class; ?>">
                                                    <span class="option-label"><?php echo $option_key; ?>.</span>
                                                    <span class="option-text"><?php echo htmlspecialchars($option_text); ?></span>
                                                    <?php if ($indicator): ?>
                                                        <span class="answer-indicator"><?php echo $indicator; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="actions">
            <a href="quiz_management.php" class="btn">📊 Quiz Management</a>
            <a href="../admin_panel.php" class="btn btn-secondary">🏠 Admin Panel</a>
        </div>
    </div>
</body>
</html> 