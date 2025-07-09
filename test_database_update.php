<?php
// Test script to verify database structure and add sample data
require_once 'config/conn.php';

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>Database Structure Test</h2>";

// Check if the new columns exist
$sql = "DESCRIBE files";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Current table structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Add sample data if table is empty
$checkSql = "SELECT COUNT(*) as count FROM files";
$checkResult = mysqli_query($conn, $checkSql);
$count = mysqli_fetch_assoc($checkResult)['count'];

if ($count == 0) {
    echo "<h3>Adding sample data...</h3>";
    
    $sampleData = [
        [
            'filename' => 'Computer Networks Chapter 1.pdf',
            'filepath' => 'uploads/note.pdf',
            'course_id' => 1,
            'description' => 'Introduction to Computer Networks - Basic concepts and protocols'
        ],
        [
            'filename' => 'Network Topologies.pdf',
            'filepath' => 'uploads/note.pdf',
            'course_id' => 1,
            'description' => 'Understanding different network topologies and their applications'
        ]
    ];
    
    foreach ($sampleData as $data) {
        $sql = "INSERT INTO files (filename, filepath, course_id, description) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssis", $data['filename'], $data['filepath'], $data['course_id'], $data['description']);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "<p>✓ Added: " . $data['filename'] . "</p>";
            } else {
                echo "<p>✗ Error adding: " . $data['filename'] . " - " . mysqli_stmt_error($stmt) . "</p>";
            }
            mysqli_stmt_close($stmt);
        }
    }
} else {
    echo "<h3>Sample data already exists. Current files:</h3>";
    
    $sql = "SELECT * FROM files ORDER BY id";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Filename</th><th>Course ID</th><th>Description</th></tr>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['filename'] . "</td>";
            echo "<td>" . $row['course_id'] . "</td>";
            echo "<td>" . $row['description'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

echo "<h3>Test Links:</h3>";
echo "<ul>";
echo "<li><a href='pages/read.php?file_id=1' target='_blank'>Read File ID 1</a></li>";
echo "<li><a href='pages/read.php?file_id=2' target='_blank'>Read File ID 2</a></li>";
echo "<li><a href='pages/single1.php' target='_blank'>Course Page with File Links</a></li>";
echo "</ul>";

mysqli_close($conn);
?> 