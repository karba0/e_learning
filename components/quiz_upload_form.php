<?php
session_start();
include '../config/conn.php';

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $chapter = intval($_POST['chapter']);
    $created_by = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'admin';

    // Check if a quiz already exists for this chapter
    $check = mysqli_query($conn, "SELECT id FROM quizzes WHERE course_id = $chapter");
    if (mysqli_num_rows($check) > 0) {
        $error = "A quiz already exists for this chapter. Only one quiz per chapter is allowed.";
    } else {
        // Insert quiz (store chapter instead of course_id)
        $sql = "INSERT INTO quizzes (title, description, course_id, created_by) VALUES ('$title', '$description', $chapter, '$created_by')";
        if (mysqli_query($conn, $sql)) {
            $quiz_id = mysqli_insert_id($conn);
            // Insert questions
            if (!empty($_POST['questions']) && is_array($_POST['questions'])) {
                $questions = $_POST['questions'];
                $success = true;
                for ($i = 0; $i < count($questions); $i++) {
                    if (!empty($questions[$i]['question'])) {
                        $question_text = mysqli_real_escape_string($conn, $questions[$i]['question']);
                        $option_a = mysqli_real_escape_string($conn, $questions[$i]['option_a']);
                        $option_b = mysqli_real_escape_string($conn, $questions[$i]['option_b']);
                        $option_c = mysqli_real_escape_string($conn, $questions[$i]['option_c']);
                        $option_d = mysqli_real_escape_string($conn, $questions[$i]['option_d']);
                        $correct_answer = mysqli_real_escape_string($conn, $questions[$i]['correct_answer']);
                        $sql = "INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, question_order) 
                                VALUES ($quiz_id, '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_answer', " . ($i + 1) . ")";
                        if (!mysqli_query($conn, $sql)) {
                            $success = false;
                            $error = "Error inserting question " . ($i + 1) . ": " . mysqli_error($conn);
                            break;
                        }
                    }
                }
                if ($success) {
                    header('Location: quiz_management.php');
                    exit();
                }
            } else {
                $error = "Please add at least one question to the quiz.";
            }
        } else {
            $error = "Error creating quiz: " . mysqli_error($conn);
        }
    }
}

// Before the HTML output, fetch chapters from the files table
$chapters = [];
$chapter_result = mysqli_query($conn, "SELECT id, filename FROM files ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($chapter_result)) {
    $chapters[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Quiz</title>
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
        .form-title {
            text-align: center;
            color: #185a9d;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"], textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            box-sizing: border-box;
        }
        input[type="text"]:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #43cea2;
        }
        .question-container {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f9f9f9;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .question-number {
            font-weight: 700;
            color: #185a9d;
            font-size: 1.1rem;
        }
        .remove-question {
            background: #ff4757;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .remove-question:hover {
            background: #ff3742;
        }
        .options-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        .option-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .option-group input[type="radio"] {
            margin: 0;
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
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        @media (max-width: 768px) {
            .options-grid {
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
        <h1 class="form-title">📝 Upload Quiz</h1>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="quizForm">
            <div class="form-group">
                <label for="title">Quiz Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="chapter">Chapter:</label>
                <select id="chapter" name="chapter" required>
                    <?php 
                    $chapter_num = 1;
                    foreach ($chapters as $chapter): ?>
                        <option value="<?php echo $chapter['id']; ?>">Chapter <?php echo $chapter_num++; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="questions-container">
                <!-- Questions will be added here dynamically -->
            </div>
            
            <div class="actions">
                <button type="button" class="btn btn-secondary" onclick="addQuestion()">➕ Add Question</button>
                <button type="submit" class="btn">📤 Upload Quiz</button>
                <button type="button" class="btn btn-danger" onclick="window.location.href='../index.php'">❌ Cancel</button>
            </div>
        </form>
    </div>

    <script>
        let questionCount = 0;
        
        function addQuestion() {
            questionCount++;
            const container = document.getElementById('questions-container');
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question-container';
            questionDiv.innerHTML = `
                <div class="question-header">
                    <span class="question-number">Question ${questionCount}</span>
                    <button type="button" class="remove-question" onclick="removeQuestion(this)">🗑️ Remove</button>
                </div>
                <div class="form-group">
                    <label>Question Text:</label>
                    <textarea name="questions[${questionCount-1}][question]" required></textarea>
                </div>
                <div class="options-grid">
                    <div class="option-group">
                        <input type="text" name="questions[${questionCount-1}][option_a]" placeholder="Option A" required>
                    </div>
                    <div class="option-group">
                        <input type="text" name="questions[${questionCount-1}][option_b]" placeholder="Option B" required>
                    </div>
                    <div class="option-group">
                        <input type="text" name="questions[${questionCount-1}][option_c]" placeholder="Option C" required>
                    </div>
                    <div class="option-group">
                        <input type="text" name="questions[${questionCount-1}][option_d]" placeholder="Option D" required>
                    </div>
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label style="font-weight:700; color:#185a9d;">Select Correct Answer:</label>
                    <label><input type="radio" name="questions[${questionCount-1}][correct_answer]" value="A" required> A</label>
                    <label><input type="radio" name="questions[${questionCount-1}][correct_answer]" value="B"> B</label>
                    <label><input type="radio" name="questions[${questionCount-1}][correct_answer]" value="C"> C</label>
                    <label><input type="radio" name="questions[${questionCount-1}][correct_answer]" value="D"> D</label>
                </div>
            `;
            container.appendChild(questionDiv);
        }
        
        function removeQuestion(button) {
            button.closest('.question-container').remove();
            updateQuestionNumbers();
        }
        
        function updateQuestionNumbers() {
            const questions = document.querySelectorAll('.question-container');
            questions.forEach((question, index) => {
                question.querySelector('.question-number').textContent = `Question ${index + 1}`;
            });
        }
        
        // Add five questions by default
        document.addEventListener('DOMContentLoaded', function() {
            // addQuestion(); // Uncomment to add one by default, or leave empty for zero
        });
    </script>
</body>
</html> 