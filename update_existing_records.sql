-- Update existing files table to add filename column if it doesn't exist
USE login_db;

-- Add filename column if it doesn't exist
ALTER TABLE files ADD COLUMN IF NOT EXISTS filename VARCHAR(255) NOT NULL DEFAULT 'Unknown File';

-- Update existing records with proper filenames based on filepath
UPDATE files 
SET filename = CASE 
    WHEN filepath LIKE '%note.pdf%' THEN 'Reading Material.pdf'
    WHEN filepath LIKE '%.pdf%' THEN CONCAT('Document_', id, '.pdf')
    WHEN filepath LIKE '%.doc%' THEN CONCAT('Document_', id, '.doc')
    WHEN filepath LIKE '%.txt%' THEN CONCAT('Document_', id, '.txt')
    ELSE CONCAT('File_', id)
END
WHERE filename = 'Unknown File' OR filename = '';

-- Show current table structure
DESCRIBE files;

-- Show all records
SELECT * FROM files; 