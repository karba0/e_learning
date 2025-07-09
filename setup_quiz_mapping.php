<?php
session_start();
include 'config/conn.php';

$message = '';
$error = '';

// Handle form submission for mapping files to quizzes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['map_quiz'])) {
    $file_id = intval($_POST['file_id']);
    $quiz_id = intval($_POST['quiz_id']);
    
    // Update the quiz's course_id to match the file_id
    $sql = "UPDATE quizzes SET course_id = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $file_id, $quiz_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Quiz mapping updated successfully! File ID $file_id is now mapped to Quiz ID $quiz_id";
    } else {
        $error = "Error updating quiz mapping: " . mysqli_error($conn);
    }
}

// Fetch all files
$sql = "SELECT * FROM files ORDER BY id ASC";
$files_result = mysqli_query($conn, $sql);
$files = [];
while ($row = mysqli_fetch_assoc($files_result)) {
    $files[] = $row;
}

// Fetch all quizzes
$sql = "SELECT * FROM quizzes ORDER BY id ASC";
$quizzes_result = mysqli_query($conn, $sql);
$quizzes = [];
while ($row = mysqli_fetch_assoc($quizzes_result)) {
    $quizzes[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Mapping Setup</title>
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
            max-width: 1000px;
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
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 1.1rem;
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
        .mapping-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        .section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .section h3 {
            color: #185a9d;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .file-item, .quiz-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .file-title, .quiz-title {
            font-weight: 700;
            color: #185a9d;
            margin-bottom: 5px;
        }
        .file-id, .quiz-id {
            color: #666;
            font-size: 0.9rem;
        }
        .mapping-form {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
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
        .current-mappings {
            background: #fff3cd;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .mapping-item {
            background: white;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 8px;
        }
        @media (max-width: 768px) {
            .mapping-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">🔗 Quiz Mapping Setup</h1>
            <p class="subtitle">Connect your reading materials with quizzes</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="mapping-grid">
            <div class="section">
                <h3>📚 Available Files</h3>
                <?php if (empty($files)): ?>
                    <p>No files found in the database.</p>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <div class="file-item">
                            <div class="file-title"><?php echo htmlspecialchars($file['filename']); ?></div>
                            <div class="file-id">File ID: <?php echo $file['id']; ?></div>
                            <?php if ($file['description']): ?>
                                <div style="color: #666; font-size: 0.9rem; margin-top: 5px;">
                                    <?php echo htmlspecialchars($file['description']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="section">
                <h3>📝 Available Quizzes</h3>
                <?php if (empty($quizzes)): ?>
                    <p>No quizzes found. <a href="components/quiz_upload_form.php">Create a quiz first</a>.</p>
                <?php else: ?>
                    <?php foreach ($quizzes as $quiz): ?>
                        <div class="quiz-item">
                            <div class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></div>
                            <div class="quiz-id">Quiz ID: <?php echo $quiz['id']; ?> | Chapter: <?php echo 'Chapter ' . (int)$quiz['course_id']; ?></div>
                            <div style="color: #666; font-size: 0.9rem; margin-top: 5px;">
                                Status: <?php echo $quiz['is_active'] ? '✅ Active' : '❌ Inactive'; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($files) && !empty($quizzes)): ?>
            <div class="mapping-form">
                <h3>🔗 Map File to Quiz</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="file_id">Select File:</label>
                        <select name="file_id" id="file_id" required>
                            <option value="">Choose a file...</option>
                            <?php foreach ($files as $file): ?>
                                <option value="<?php echo $file['id']; ?>">
                                    <?php echo htmlspecialchars($file['filename']); ?> (ID: <?php echo $file['id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quiz_id">Select Quiz:</label>
                        <select name="quiz_id" id="quiz_id" required>
                            <option value="">Choose a quiz...</option>
                            <?php foreach ($quizzes as $quiz): ?>
                                <option value="<?php echo $quiz['id']; ?>">
                                    <?php echo htmlspecialchars($quiz['title']); ?> (<?php echo 'Chapter ' . (int)$quiz['course_id']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="map_quiz" class="btn">🔗 Map Quiz to File</button>
                </form>
            </div>
            
            <div class="current-mappings">
                <h3>📋 Current Mappings</h3>
                <?php
                $sql = "SELECT q.id as quiz_id, q.title as quiz_title, q.course_id as file_id, f.filename 
                        FROM quizzes q 
                        LEFT JOIN files f ON q.course_id = f.id 
                        WHERE q.course_id > 0 
                        ORDER BY q.course_id";
                $mappings_result = mysqli_query($conn, $sql);
                $has_mappings = false;
                while ($mapping = mysqli_fetch_assoc($mappings_result)) {
                    $has_mappings = true;
                    ?>
                    <div class="mapping-item">
                        <strong>File:</strong> <?php echo htmlspecialchars($mapping['filename'] ?? 'Unknown'); ?> (ID: <?php echo $mapping['file_id']; ?>) 
                        → <strong>Quiz:</strong> <?php echo htmlspecialchars($mapping['quiz_title']); ?> (ID: <?php echo $mapping['quiz_id']; ?>)
                    </div>
                    <?php
                }
                if (!$has_mappings) {
                    echo '<p>No mappings found. Create mappings using the form above.</p>';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn btn-secondary">🏠 Back to Home</a>
            <a href="components/quiz_management.php" class="btn">📊 Manage Quizzes</a>
        </div>
    </div>
</body>
</html> 