<?php
// Test read.php functionality
echo "<h2>Testing read.php Functionality</h2>";

// Simulate what read.php does
require_once 'config/conn.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the latest file
$sql = "SELECT * FROM files ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $filePath = $row['filepath'];
    $fileName = $row['filename'];
    
    echo "✅ Database query successful<br>";
    echo "📄 Filename: " . htmlspecialchars($fileName) . "<br>";
    echo "📂 Original filepath: " . htmlspecialchars($filePath) . "<br>";
    
    // Fix file path to be relative to project root (like read.php does)
    if (strpos($filePath, 'uploads/') === 0) {
        $filePath = '../' . $filePath;
    }
    
    echo "📂 Corrected filepath: " . htmlspecialchars($filePath) . "<br>";
    
    if (file_exists($filePath)) {
        echo "✅ File exists on disk<br>";
        echo "📏 File size: " . number_format(filesize($filePath) / 1024 / 1024, 2) . " MB<br>";
        
        // Test if it's a PDF
        if (str_ends_with(strtolower($filePath), ".pdf")) {
            echo "📄 File is a PDF - can be embedded<br>";
        } else {
            echo "📄 File is not a PDF - will show as download link<br>";
        }
        
        echo "<br><strong>read.php should now work correctly!</strong><br>";
        echo "<a href='pages/read.php' target='_blank'>Open read.php</a><br>";
        
    } else {
        echo "❌ File not found on disk<br>";
        echo "Expected path: " . htmlspecialchars($filePath) . "<br>";
        
        // Try to find the file
        echo "<br>Searching for the file...<br>";
        $searchPath = "uploads/note.pdf";
        if (file_exists($searchPath)) {
            echo "✅ Found file at: " . $searchPath . "<br>";
        } else {
            echo "❌ File not found at: " . $searchPath . "<br>";
        }
    }
} else {
    echo "❌ No files found in database<br>";
}

mysqli_close($conn);
?> 