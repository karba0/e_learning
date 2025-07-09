<?php
// Fix database structure and test read.php
require_once 'config/conn.php';

echo "<h2>Fixing Database Structure</h2>";

// Add filename column if it doesn't exist
$sql = "ALTER TABLE files ADD COLUMN IF NOT EXISTS filename VARCHAR(255) NOT NULL DEFAULT 'Unknown File'";
if (mysqli_query($conn, $sql)) {
    echo "✅ Added filename column<br>";
} else {
    echo "❌ Error adding filename column: " . mysqli_error($conn) . "<br>";
}

// Update existing records with proper filenames
$sql = "UPDATE files 
        SET filename = CASE 
            WHEN filepath LIKE '%note.pdf%' THEN 'Reading Material.pdf'
            WHEN filepath LIKE '%.pdf%' THEN CONCAT('Document_', id, '.pdf')
            WHEN filepath LIKE '%.doc%' THEN CONCAT('Document_', id, '.doc')
            WHEN filepath LIKE '%.txt%' THEN CONCAT('Document_', id, '.txt')
            ELSE CONCAT('File_', id)
        END
        WHERE filename = 'Unknown File' OR filename = ''";

if (mysqli_query($conn, $sql)) {
    echo "✅ Updated existing records<br>";
} else {
    echo "❌ Error updating records: " . mysqli_error($conn) . "<br>";
}

// Show current records
echo "<h3>Current Files in Database:</h3>";
$sql = "SELECT * FROM files ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Filename</th><th>Filepath</th><th>Upload Date</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['filename']) . "</td>";
        echo "<td>" . htmlspecialchars($row['filepath']) . "</td>";
        echo "<td>" . $row['upload_date'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error querying files: " . mysqli_error($conn) . "<br>";
}

// Test read.php functionality
echo "<h3>Testing read.php:</h3>";
$sql = "SELECT * FROM files ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $filePath = $row['filepath'];
    $fileName = $row['filename'];
    
    // Fix file path to be relative to project root
    if (strpos($filePath, 'uploads/') === 0) {
        $filePath = '../' . $filePath;
    }
    
    echo "📄 Latest file: " . htmlspecialchars($fileName) . "<br>";
    echo "📂 File path: " . htmlspecialchars($filePath) . "<br>";
    
    if (file_exists($filePath)) {
        echo "✅ File exists on disk<br>";
        echo "<a href='pages/read.php' target='_blank'>Test read.php</a><br>";
    } else {
        echo "❌ File not found on disk<br>";
        echo "Expected path: " . htmlspecialchars($filePath) . "<br>";
    }
} else {
    echo "❌ No files found in database<br>";
}

mysqli_close($conn);
?> 