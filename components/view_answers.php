<?php
session_start();
include '../config/conn.php';

if (!isset($_SESSION['user_name'])) {
    header('Location: ../auth/login.php');
    exit();
}

$username = $_SESSION['user_name'];
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// Get the latest quiz result for this user and quiz
$sql = "SELECT * FROM quiz_results WHERE user_name = '" . mysqli_real_escape_string($conn, $username) . "' AND quiz_id = $quiz_id ORDER BY submitted_date DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$quiz_result = mysqli_fetch_assoc($result);

if (!$quiz_result) {
    die("No quiz results found for this quiz.");
}

// Get quiz details
$quiz_sql = "SELECT * FROM quizzes WHERE id = $quiz_id";
$quiz_result_query = mysqli_query($conn, $quiz_sql);
$quiz = mysqli_fetch_assoc($quiz_result_query);

// Get user answers with question details
$sql = "SELECT 
            qua.user_answer,
            qua.is_correct,
            qua.submitted_date,
            qq.question_text,
            qq.option_a,
            qq.option_b,
            qq.option_c,
            qq.option_d,
            qq.correct_answer,
            qq.question_order
        FROM quiz_user_answers qua
        JOIN quiz_questions qq ON qua.question_id = qq.id
        WHERE qua.quiz_result_id = " . $quiz_result['id'] . "
        ORDER BY qq.question_order";

$answers_result = mysqli_query($conn, $sql);
$answers = [];
while ($row = mysqli_fetch_assoc($answers_result)) {
    $answers[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Quiz Answers</title>
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
            max-width: 800px;
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
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .score-summary {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        .score-summary h2 {
            color: #185a9d;
            margin: 0 0 10px 0;
        }
        .score-details {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
        }
        .score-item {
            text-align: center;
        }
        .score-item .number {
            font-size: 2rem;
            font-weight: 800;
            color: #43cea2;
        }
        .score-item .label {
            color: #666;
            font-size: 0.9rem;
        }
        .answers-section {
            padding: 30px;
        }
        .question-container {
            margin-bottom: 30px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }
        .question-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .question-number {
            font-weight: 700;
            color: #185a9d;
            font-size: 1.1rem;
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
            padding: 20px;
        }
        .question-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .options-list {
            display: grid;
            gap: 10px;
        }
        .option {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
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
            min-width: 30px;
        }
        .option-text {
            flex: 1;
        }
        .answer-indicator {
            font-size: 1.2rem;
            margin-left: auto;
        }
        .actions {
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
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
            margin: 0 10px;
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
            .answers-section {
                padding: 20px;
            }
            .score-details {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Your Quiz Answers</h1>
            <p><?php echo htmlspecialchars($quiz['title']); ?></p>
        </div>
        
        <div class="score-summary">
            <h2>📊 Quiz Summary</h2>
            <div class="score-details">
                <div class="score-item">
                    <div class="number"><?php echo $quiz_result['score']; ?>/<?php echo $quiz_result['total_questions']; ?></div>
                    <div class="label">Your Score</div>
                </div>
                <div class="score-item">
                    <div class="number"><?php echo round(($quiz_result['score'] / $quiz_result['total_questions']) * 100, 1); ?>%</div>
                    <div class="label">Percentage</div>
                </div>
                <div class="score-item">
                    <div class="number"><?php echo date('M d, Y', strtotime($quiz_result['submitted_date'])); ?></div>
                    <div class="label">Date Taken</div>
                </div>
            </div>
        </div>
        
        <div class="answers-section">
            <h2>📋 Question Details</h2>
            <?php foreach ($answers as $index => $answer): ?>
                <div class="question-container">
                    <div class="question-header">
                        <span class="question-number">Question <?php echo $index + 1; ?></span>
                        <span class="question-status <?php echo $answer['is_correct'] ? 'status-correct' : 'status-incorrect'; ?>">
                            <?php echo $answer['is_correct'] ? '✅ Correct' : '❌ Incorrect'; ?>
                        </span>
                    </div>
                    <div class="question-content">
                        <div class="question-text"><?php echo htmlspecialchars($answer['question_text']); ?></div>
                        <div class="options-list">
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
                                    $indicator = '👤 Your Answer';
                                }
                                
                                if ($option_key == $answer['correct_answer']) {
                                    $option_class = 'correct-answer';
                                    $indicator = '✅ Correct Answer';
                                }
                                
                                if ($option_key == $answer['user_answer'] && $option_key != $answer['correct_answer']) {
                                    $option_class = 'incorrect-answer';
                                    $indicator = '❌ Wrong Answer';
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
        
        <div class="actions">
            <a href="quiz_template_db.php?quiz_id=<?php echo $quiz_id; ?>" class="btn">📝 Try Again</a>
            <a href="view_scores.php" class="btn btn-secondary">📊 View Leaderboard</a>
            <a href="../index.php" class="btn btn-secondary">🏠 Back to Home</a>
        </div>
    </div>
</body>
</html> 