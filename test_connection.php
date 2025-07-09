<?php
// Test database connection and table structure
require_once 'config/conn.php';

echo "<h2>Database Connection Test</h2>";

// Test connection
if ($conn) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
    exit;
}

// Test if files table exists
$sql = "SHOW TABLES LIKE 'files'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "✅ Files table exists<br>";
} else {
    echo "❌ Files table does not exist<br>";
    echo "Please run the database_setup.sql file in your MySQL database<br>";
}

// Test if there are any files in the table
$sql = "SELECT COUNT(*) as count FROM files";
$result = mysqli_query($conn, $sql);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "📁 Number of files in database: " . $row['count'] . "<br>";
    
    if ($row['count'] > 0) {
        // Show the latest file
        $sql = "SELECT * FROM files ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $file = mysqli_fetch_assoc($result);
        
        echo "📄 Latest file: " . $file['filename'] . "<br>";
        echo "📂 File path: " . $file['filepath'] . "<br>";
        
        // Check if file exists on disk
        if (file_exists($file['filepath'])) {
            echo "✅ File exists on disk<br>";
        } else {
            echo "❌ File not found on disk<br>";
        }
    }
} else {
    echo "❌ Error querying files table: " . mysqli_error($conn) . "<br>";
}

// Test table structure
$sql = "DESCRIBE files";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<br><h3>Files Table Structure:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Error describing files table: " . mysqli_error($conn) . "<br>";
}

mysqli_close($conn);
?> 