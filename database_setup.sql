-- Database setup for e_learning project
-- Run this SQL in your existing login_db database

USE login_db;

-- Create files table if it doesn't exist
CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_size INT,
    file_type VARCHAR(100),
    course_id INT DEFAULT 1,
    description TEXT
);

-- Insert sample data for testing (only if table is empty)
INSERT INTO files (filename, filepath, course_id, description) 
SELECT 'Sample Reading Material.pdf', 'uploads/note.pdf', 1, 'Computer Network For Dummies - Chapter 1'
WHERE NOT EXISTS (SELECT 1 FROM files LIMIT 1); 