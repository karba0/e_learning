<?php
// Setup script for answer storage system
include 'config/conn.php';

echo "<h2>Setting up Answer Storage System...</h2>";

// Read and execute the SQL setup
$sql_file = 'quiz_database_setup.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    
    // Split SQL into individual statements
    $statements = explode(';', $sql_content);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if (mysqli_query($conn, $statement)) {
                $success_count++;
                echo "<p style='color: green;'>✅ Success: " . substr($statement, 0, 50) . "...</p>";
            } else {
                $error_count++;
                echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
            }
        }
    }
    
    echo "<h3>Setup Complete!</h3>";
    echo "<p>✅ Successful operations: $success_count</p>";
    echo "<p>❌ Errors: $error_count</p>";
    
    if ($error_count == 0) {
        echo "<h3>🎉 Answer Storage System is Ready!</h3>";
        echo "<p>Your system now supports:</p>";
        echo "<ul>";
        echo "<li>✅ Storing individual user answers</li>";
        echo "<li>✅ Tracking correct/incorrect responses</li>";
        echo "<li>✅ Viewing detailed answer breakdowns</li>";
        echo "<li>✅ Admin interface for answer review</li>";
        echo "</ul>";
        
        echo "<h3>📋 Available Features:</h3>";
        echo "<ul>";
        echo "<li><a href='components/view_answers.php?quiz_id=1'>View Your Answers</a> - For users to see their detailed responses</li>";
        echo "<li><a href='components/admin_view_answers.php'>Admin View All Answers</a> - For administrators to review all responses</li>";
        echo "<li><a href='components/quiz_template_db.php?quiz_id=1'>Take a Quiz</a> - Test the new answer storage system</li>";
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>❌ Error: SQL setup file not found!</p>";
}

echo "<p><a href='index.php'>← Back to Home</a></p>";
?> 