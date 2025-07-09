<?php
session_start();
include '../config/conn.php';

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 1;
$file_id = isset($_GET['file_id']) ? intval($_GET['file_id']) : 6; // Default to 6 if not set

// Fetch quiz details
$sql = "SELECT * FROM quizzes WHERE id = $quiz_id AND is_active = 1";
$result = mysqli_query($conn, $sql);
$quiz = mysqli_fetch_assoc($result);

if (!$quiz) {
    die("Quiz not found or inactive.");
}

// Fetch questions for this quiz
$sql = "SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id ORDER BY question_order";
$questions_result = mysqli_query($conn, $sql);
$questions = [];
while ($row = mysqli_fetch_assoc($questions_result)) {
    $questions[] = $row;
}

if (empty($questions)) {
    die("No questions found for this quiz.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?></title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #43cea2 0%, #185a9d 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.18);
            padding: 30px;
        }
        .quiz-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .quiz-title {
            color: #185a9d;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .quiz-description {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        .quiz-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .question-container {
            margin-bottom: 30px;
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            background: #f9f9f9;
        }
        .question-number {
            font-weight: 700;
            color: #185a9d;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .question-text {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: #333;
            line-height: 1.5;
        }
        .options-container {
            display: grid;
            gap: 12px;
        }
        .option {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .option:hover {
            border-color: #43cea2;
            background: #f0f8f5;
        }
        .option input[type="radio"] {
            margin-right: 12px;
            transform: scale(1.2);
        }
        .option label {
            cursor: pointer;
            flex: 1;
            font-size: 1rem;
        }
        .submit-btn {
            background: #43cea2;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .submit-btn:hover {
            background: #3bb890;
        }
        .timer {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: #185a9d;
            margin-bottom: 20px;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #43cea2;
            transition: width 0.3s;
        }
        @media (max-width: 768px) {
            .quiz-container {
                padding: 20px;
            }
            .quiz-title {
                font-size: 1.5rem;
            }
            .question-text {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <?php if (isset($_SESSION['user_name']) && $_SESSION['user_name']): ?>
            <div style="text-align:center; margin-bottom: 10px; font-size:1.2rem; color:#185a9d; font-weight:700;">
                Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            </div>
        <?php endif; ?>
        <div class="quiz-header">
            <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h1>
            <p class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></p>
            <div class="quiz-info">
                <strong>Total Questions:</strong> <?php echo count($questions); ?> | 
                <strong>Time Limit:</strong> 1 minute per question
            </div>
        </div>

        <form method="POST" action="submit_quiz_db.php" id="quizForm">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <input type="hidden" name="file_id" value="<?php echo $file_id; ?>">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="timer" id="globalTimer">
                Total time left: <span id="globalTimeLeft"></span>
            </div>
            <div id="questionArea">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-container">
                    <div class="question-number">Question <?php echo $index + 1; ?></div>
                    <div class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></div>
                    <div class="options-container">
                        <label class="option"><input type="radio" name="answers[<?php echo $index; ?>]" value="A" required> <?php echo htmlspecialchars($question['option_a']); ?></label>
                        <label class="option"><input type="radio" name="answers[<?php echo $index; ?>]" value="B"> <?php echo htmlspecialchars($question['option_b']); ?></label>
                        <label class="option"><input type="radio" name="answers[<?php echo $index; ?>]" value="C"> <?php echo htmlspecialchars($question['option_c']); ?></label>
                        <label class="option"><input type="radio" name="answers[<?php echo $index; ?>]" value="D"> <?php echo htmlspecialchars($question['option_d']); ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="actions" style="display: flex; justify-content: center; margin-top: 20px;">
                <button type="submit" class="submit-btn" id="submitBtn">Submit Quiz</button>
            </div>
        </form>
    </div>
    <script>
    const totalQuestions = <?php echo count($questions); ?>;
    let globalTimer = totalQuestions * 60;
    let globalTimerInterval = null;

    function startGlobalTimer() {
        document.getElementById('globalTimeLeft').textContent = formatTime(globalTimer);
        globalTimerInterval = setInterval(() => {
            globalTimer--;
            document.getElementById('globalTimeLeft').textContent = formatTime(globalTimer);
            if (globalTimer <= 0) {
                clearInterval(globalTimerInterval);
                alert('Total quiz time is up! Submitting quiz automatically.');
                document.getElementById('quizForm').submit();
            }
        }, 1000);
    }

    function formatTime(seconds) {
        const min = Math.floor(seconds / 60);
        const sec = seconds % 60;
        return min + ':' + sec.toString().padStart(2, '0');
    }

    // Start the global timer
    startGlobalTimer();
    </script>
</body>
</html> 