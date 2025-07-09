<?php
// File helper functions for e_learning project

/**
 * Get files by course ID
 * @param int $courseId The course ID
 * @return array Array of file records
 */
function getFilesByCourseId($conn, $courseId) {
    $sql = "SELECT * FROM files WHERE course_id = ? ORDER BY upload_date DESC";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $courseId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $files = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $files[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $files;
    }
    
    return [];
}

/**
 * Get file by ID
 * @param int $fileId The file ID
 * @return array|null File record or null if not found
 */
function getFileById($conn, $fileId) {
    $sql = "SELECT * FROM files WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $fileId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $file = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $file;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return null;
}

/**
 * Generate read.php URL for a file
 * @param int $fileId The file ID
 * @return string The URL
 */
function getReadUrl($fileId) {
    return "read.php?file_id=" . (int)$fileId;
}
?> 