<?php
if (!file_exists('../config/conn.php')) {
    die('conn.php not found!');
}
require_once __DIR__ . '/../config/conn.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_FILES['myfile'])) {
    $fileName = $_FILES['myfile']['name'];
    $fileTmpName = $_FILES['myfile']['tmp_name'];
    $fileSize = $_FILES['myfile']['size'];
    $fileType = $_FILES['myfile']['type'];
    $courseId = isset($_POST['course_id']) ? (int)$_POST['course_id'] : 1;
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    
    // Create upload folder if not exists
    $uploadPath = "../uploads/";
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    $targetFile = $uploadPath . basename($fileName);

    // Move the file to the uploads folder
    if (move_uploaded_file($fileTmpName, $targetFile)) {
        // Save the file information in the database
        $sql = "INSERT INTO files (filename, filepath, file_size, file_type, course_id, description) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssisis", $fileName, $targetFile, $fileSize, $fileType, $courseId, $description);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "File uploaded and information saved to database successfully.";
            } else {
                echo "Database error: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "Database error: " . mysqli_error($conn);
        }
    } else {
        echo "File upload failed.";
    }
}
?>
