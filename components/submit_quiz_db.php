<?php
session_start();
include '../config/conn.php';

file_put_contents(__DIR__ . '/debug_post.txt', print_r($_POST, true));

$quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 1;
$file_id = isset($_POST['file_id']) ? intval($_POST['file_id']) : 6; // Default to 6 if not set
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$answers = isset($_POST['answers']) ? $_POST['answers'] : [];

// Fetch quiz details
$sql = "SELECT * FROM quizzes WHERE id = $quiz_id AND is_active = 1";
$result = mysqli_query($conn, $sql);
$quiz = mysqli_fetch_assoc($result);

if (!$quiz) {
    die("Quiz not found or inactive.");
}

// Fetch questions and correct answers
$sql = "SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id ORDER BY question_order";
$questions_result = mysqli_query($conn, $sql);
$questions = [];
while ($row = mysqli_fetch_assoc($questions_result)) {
    $questions[] = $row;
}

$score = 0;
$total = count($questions);

// Calculate score and prepare answers for storage
$user_answers_data = [];
foreach ($questions as $index => $question) {
    $user_answer = isset($answers[$index]) ? $answers[$index] : null;
    $is_correct = ($user_answer == $question['correct_answer']);
    
    if ($is_correct) {
        $score++;
    }
    
    // Store answer data for later insertion
    if ($user_answer) {
        $user_answers_data[] = [
            'question_id' => $question['id'],
            'user_answer' => $user_answer,
            'is_correct' => $is_correct
        ];
    }
}

// Save to database if username is available
if ($username) {
    $safe_username = mysqli_real_escape_string($conn, $username);
    
    // Insert quiz result
    $sql = "INSERT INTO quiz_results (quiz_id, user_name, score, total_questions, file_id) VALUES ($quiz_id, '$safe_username', $score, $total, " . ($file_id ? $file_id : "NULL") . ")";
    if (mysqli_query($conn, $sql)) {
        $quiz_result_id = mysqli_insert_id($conn);
        
        // Store individual answers
        foreach ($user_answers_data as $answer_data) {
            $question_id = intval($answer_data['question_id']);
            $user_answer = mysqli_real_escape_string($conn, $answer_data['user_answer']);
            $is_correct = $answer_data['is_correct'] ? 1 : 0;
            
            $sql = "INSERT INTO quiz_user_answers (quiz_result_id, question_id, user_answer, is_correct) 
                    VALUES ($quiz_result_id, $question_id, '$user_answer', $is_correct)";
            mysqli_query($conn, $sql);
        }
    }
}

// Emoji logic
if ($score == $total && $total > 0) {
    $emoji = '🏆'; // Perfect
} elseif ($score >= $total - 1 && $total > 0) {
    $emoji = '😃'; // Almost perfect
} elseif ($score >= ceil($total * 0.6)) {
    $emoji = '🙂'; // Good
} elseif ($score >= ceil($total * 0.4)) {
    $emoji = '😐'; // Average
} elseif ($score > 0) {
    $emoji = '😢'; // Low
} else {
    $emoji = '😭'; // Zero
}

// For Try Again link
$quiz_link = "quiz_template_db.php?quiz_id=$quiz_id";
if ($file_id) {
    $quiz_link .= "&file_id=$file_id";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(120deg, #43cea2 0%, #185a9d 100%);
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .result-card {
            background: #fff;
            max-width: 540px;
            width: 100%;
            margin: 48px auto;
            border-radius: 28px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.18);
            padding: 38px 32px 28px 32px;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .result-title {
            color: #185a9d;
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .result-emoji {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .result-score {
            font-size: 1.3rem;
            color: #388e8e;
            margin-bottom: 24px;
            font-weight: 700;
        }
        .result-percentage {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 20px;
        }
        .result-links {
            margin-top: 18px;
        }
        .result-link {
            color: #185a9d;
            text-decoration: none;
            font-weight: 600;
            margin: 0 10px;
            font-size: 1.08rem;
            transition: color 0.2s;
        }
        .result-link:hover {
            color: #43cea2;
            text-decoration: underline;
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
            margin: 10px;
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
        @media (max-width: 600px) {
            .result-card {
                padding: 18px 6px 12px 6px;
                border-radius: 14px;
            }
            .result-title {
                font-size: 1.3rem;
            }
        }
        .result-icon {
            width: 48px;
            height: 48px;
        }
    </style>
</head>
<body>
    <div class="result-card">
        <div class="result-title">
            <img src="https://cdn-icons-png.flaticon.com/512/3094/3094840.png" alt="Quiz Icon" class="result-icon">
            Thank you<?php echo $username ? ', ' . htmlspecialchars($username) : ''; ?>!
        </div>
        <div class="result-emoji"><?php echo $emoji; ?></div>
        <div class="result-score">Your Score: <?php echo $score; ?> out of <?php echo $total; ?></div>
        <div class="result-percentage">Percentage: <?php echo $total > 0 ? round(($score / $total) * 100, 1) : 0; ?>%</div>
        <div class="result-links">
            <a href="<?php echo $quiz_link; ?>" class="result-link">Try Again</a> |
            <a href="view_scores.php" class="result-link">View Your Rank</a> |
            <a href="view_answers.php?quiz_id=<?php echo $quiz_id; ?>" class="result-link">View Your Answers</a>
        </div>
        <div style="margin-top: 20px;">
            <button onclick="window.location.href='<?php echo $quiz_link; ?>'" class="btn">📝 Try Again</button>
            <button onclick="window.location.href='../index.php'" class="btn btn-secondary">🏠 Back to Home</button>
        </div>
    </div>
</body>
</html> 