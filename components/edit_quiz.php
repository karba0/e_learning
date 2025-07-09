<?php
session_start();
include '../config/conn.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../auth/admin_login.php');
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if ($quiz_id <= 0) {
    die('Invalid quiz ID.');
}

// Fetch quiz
$quiz = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM quizzes WHERE id = $quiz_id"));
if (!$quiz) die('Quiz not found.');

// Fetch questions
$questions = [];
$qres = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id ORDER BY question_order");
while ($row = mysqli_fetch_assoc($qres)) $questions[] = $row;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $update_quiz = mysqli_query($conn, "UPDATE quizzes SET title='$title', description='$description' WHERE id=$quiz_id");
    $success = $update_quiz;
    // Delete questions if requested
    if ($success && isset($_POST['delete_questions']) && is_array($_POST['delete_questions'])) {
        foreach ($_POST['delete_questions'] as $delete_qid) {
            $delete_qid = intval($delete_qid);
            mysqli_query($conn, "DELETE FROM quiz_questions WHERE id=$delete_qid AND quiz_id=$quiz_id");
        }
    }
    // Update questions
    if ($success && isset($_POST['questions'])) {
        foreach ($_POST['questions'] as $i => $q) {
            $qid = isset($q['id']) ? intval($q['id']) : 0;
            $question_text = mysqli_real_escape_string($conn, $q['question']);
            $option_a = mysqli_real_escape_string($conn, $q['option_a']);
            $option_b = mysqli_real_escape_string($conn, $q['option_b']);
            $option_c = mysqli_real_escape_string($conn, $q['option_c']);
            $option_d = mysqli_real_escape_string($conn, $q['option_d']);
            $correct_answer = mysqli_real_escape_string($conn, $q['correct_answer']);
            $order = $i + 1;
            if ($qid > 0) {
                // Update existing question
                $update_q = mysqli_query($conn, "UPDATE quiz_questions SET question_text='$question_text', option_a='$option_a', option_b='$option_b', option_c='$option_c', option_d='$option_d', correct_answer='$correct_answer', question_order=$order WHERE id=$qid AND quiz_id=$quiz_id");
                if (!$update_q) {
                    $success = false;
                    $error = 'Error updating question: ' . mysqli_error($conn);
                    break;
                }
            } else {
                // Insert new question
                $insert_q = mysqli_query($conn, "INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, question_order) VALUES ($quiz_id, '$question_text', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_answer', $order)");
                if (!$insert_q) {
                    $success = false;
                    $error = 'Error adding new question: ' . mysqli_error($conn);
                    break;
                }
            }
        }
    }
    if ($success) {
        header('Location: quiz_management.php');
        exit();
    } else if (!$error) {
        $error = 'Error updating quiz: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quiz</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', Arial, sans-serif; background: linear-gradient(120deg, #43cea2 0%, #185a9d 100%); min-height: 100vh; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 15px; box-shadow: 0 8px 32px rgba(24,90,157,0.18); padding: 30px; }
        .form-title { text-align: center; color: #185a9d; font-size: 2rem; font-weight: 800; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        input[type="text"], textarea, select { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; font-family: inherit; box-sizing: border-box; }
        input[type="text"]:focus, textarea:focus, select:focus { outline: none; border-color: #43cea2; }
        .question-container { border: 2px solid #e0e0e0; border-radius: 10px; padding: 20px; margin-bottom: 20px; background: #f9f9f9; }
        .question-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .question-number { font-weight: 700; color: #185a9d; font-size: 1.1rem; }
        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        .option-group { display: flex; align-items: center; gap: 10px; }
        .option-group input[type="radio"] { margin: 0; }
        .btn { background: #43cea2; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-size: 16px; font-weight: 600; transition: background 0.3s; }
        .btn:hover { background: #3bb890; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .actions { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
        @media (max-width: 768px) { .options-grid { grid-template-columns: 1fr; } .actions { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="form-title">✏️ Edit Quiz</h1>
        <?php if ($message): ?><div class="message success"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="message error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="title">Quiz Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($quiz['description']); ?></textarea>
            </div>
            <?php foreach ($questions as $i => $q): ?>
            <div class="question-container" data-question-id="<?php echo $q['id']; ?>">
                <div class="question-header">
                    <span class="question-number">Question <?php echo $i+1; ?></span>
                    <button type="button" class="btn btn-danger btn-sm delete-question-btn" onclick="deleteQuestion(this, <?php echo $q['id']; ?>)">Delete</button>
                </div>
                <input type="hidden" name="questions[<?php echo $i; ?>][id]" value="<?php echo $q['id']; ?>">
                <div class="form-group">
                    <label>Question Text:</label>
                    <textarea name="questions[<?php echo $i; ?>][question]" required><?php echo htmlspecialchars($q['question_text']); ?></textarea>
                </div>
                <div class="options-grid">
                    <div class="option-group">
                        <input type="radio" name="questions[<?php echo $i; ?>][correct_answer]" value="A" <?php if($q['correct_answer']==='A') echo 'checked'; ?> required>
                        <input type="text" name="questions[<?php echo $i; ?>][option_a]" value="<?php echo htmlspecialchars($q['option_a']); ?>" required>
                    </div>
                    <div class="option-group">
                        <input type="radio" name="questions[<?php echo $i; ?>][correct_answer]" value="B" <?php if($q['correct_answer']==='B') echo 'checked'; ?> required>
                        <input type="text" name="questions[<?php echo $i; ?>][option_b]" value="<?php echo htmlspecialchars($q['option_b']); ?>" required>
                    </div>
                    <div class="option-group">
                        <input type="radio" name="questions[<?php echo $i; ?>][correct_answer]" value="C" <?php if($q['correct_answer']==='C') echo 'checked'; ?> required>
                        <input type="text" name="questions[<?php echo $i; ?>][option_c]" value="<?php echo htmlspecialchars($q['option_c']); ?>" required>
                    </div>
                    <div class="option-group">
                        <input type="radio" name="questions[<?php echo $i; ?>][correct_answer]" value="D" <?php if($q['correct_answer']==='D') echo 'checked'; ?> required>
                        <input type="text" name="questions[<?php echo $i; ?>][option_d]" value="<?php echo htmlspecialchars($q['option_d']); ?>" required>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <div id="new-questions"></div>
            <div class="actions">
                <button type="button" class="btn btn-secondary" onclick="addNewQuestion()">➕ Add Question</button>
                <button type="submit" class="btn">💾 Save Changes</button>
                <a href="quiz_management.php" class="btn btn-secondary">Back to Management</a>
            </div>
        </form>
    </div>
    <script>
    let newQuestionIndex = <?php echo count($questions); ?>;
    function addNewQuestion() {
        const container = document.getElementById('new-questions');
        const qNum = newQuestionIndex + 1;
        const div = document.createElement('div');
        div.className = 'question-container';
        div.innerHTML = `
            <div class="question-header">
                <span class="question-number">Question ${qNum}</span>
            </div>
            <div class="form-group">
                <label>Question Text:</label>
                <textarea name="questions[${newQuestionIndex}][question]" required></textarea>
            </div>
            <div class="options-grid">
                <div class="option-group">
                    <input type="radio" name="questions[${newQuestionIndex}][correct_answer]" value="A" required>
                    <input type="text" name="questions[${newQuestionIndex}][option_a]" placeholder="Option A" required>
                </div>
                <div class="option-group">
                    <input type="radio" name="questions[${newQuestionIndex}][correct_answer]" value="B" required>
                    <input type="text" name="questions[${newQuestionIndex}][option_b]" placeholder="Option B" required>
                </div>
                <div class="option-group">
                    <input type="radio" name="questions[${newQuestionIndex}][correct_answer]" value="C" required>
                    <input type="text" name="questions[${newQuestionIndex}][option_c]" placeholder="Option C" required>
                </div>
                <div class="option-group">
                    <input type="radio" name="questions[${newQuestionIndex}][correct_answer]" value="D" required>
                    <input type="text" name="questions[${newQuestionIndex}][option_d]" placeholder="Option D" required>
                </div>
            </div>
        `;
        container.appendChild(div);
        newQuestionIndex++;
    }
    function deleteQuestion(btn, questionId) {
        // Mark for deletion by adding a hidden input
        const form = btn.closest('form');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_questions[]';
        input.value = questionId;
        form.appendChild(input);
        // Remove the question from the DOM
        btn.closest('.question-container').remove();
        // Update question numbers
        updateQuestionNumbers();
    }
    function updateQuestionNumbers() {
        const questions = document.querySelectorAll('.question-container .question-number');
        questions.forEach((el, idx) => {
            el.textContent = 'Question ' + (idx + 1);
        });
    }
    </script>
</body>
</html> 