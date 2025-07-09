-- Quiz Database Setup for e_learning project
-- Run this SQL in your existing login_db database

USE login_db;

-- Create quizzes table
CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    course_id INT DEFAULT 1,
    created_by VARCHAR(100),
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Create quiz_questions table
CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(500) NOT NULL,
    option_b VARCHAR(500) NOT NULL,
    option_c VARCHAR(500) NOT NULL,
    option_d VARCHAR(500) NOT NULL,
    correct_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    question_order INT DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Create quiz_results table (if not exists)
CREATE TABLE IF NOT EXISTS quiz_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    submitted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

-- Create quiz_user_answers table to store individual answers
CREATE TABLE IF NOT EXISTS quiz_user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_result_id INT NOT NULL,
    question_id INT NOT NULL,
    user_answer ENUM('A', 'B', 'C', 'D') NOT NULL,
    is_correct BOOLEAN NOT NULL,
    submitted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_result_id) REFERENCES quiz_results(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- Insert sample quiz data
INSERT INTO quizzes (title, description, course_id, created_by) VALUES
('Computer Networks Quiz 1', 'Basic concepts of computer networks including LAN, WAN, and network topologies', 1, 'admin'),
('Computer Networks Quiz 2', 'Network devices and protocols including TCP/IP model', 1, 'admin'),
('Computer Networks Quiz 3', 'Transmission media and switching techniques', 1, 'admin');

-- Insert sample questions for Quiz 1
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, question_order) VALUES
(1, 'Which of the following best describes a Local Area Network (LAN)?', 'A network that spans multiple countries', 'A privately owned network within a single building', 'A network that uses satellite links for communication', 'A network covering an entire city', 'B', 1),
(1, 'What is the primary disadvantage of a bus topology?', 'High fault tolerance', 'Requires the least amount of cabling', 'A single cable break can disrupt the entire network', 'Easy to troubleshoot', 'C', 2),
(1, 'Which network topology connects every device to every other device, resulting in high fault tolerance but high cabling complexity?', 'Star', 'Ring', 'Mesh', 'Bus', 'C', 3),
(1, 'In a client-server architecture, what is the role of the server?', 'To act as a low-end workstation for daily tasks', 'To provide services and resources to requesting clients', 'To share resources without a centralized administrator', 'To function as both client and server simultaneously', 'B', 4),
(1, 'Which IEEE standard is associated with wireless LANs (Wi-Fi)?', 'IEEE 802.3', 'IEEE 802.11', 'IEEE 802.16', 'IEEE 802.1', 'B', 5);

-- Insert sample questions for Quiz 2
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, question_order) VALUES
(2, 'What is the primary purpose of a repeater in a network?', 'To filter data packets based on MAC addresses', 'To regenerate signals to extend communication distance', 'To route packets between different networks', 'To provide encryption for data security', 'B', 1),
(2, 'Which layer of the TCP/IP model combines the functions of the OSI model\'s Application, Presentation, and Session layers?', 'Transport Layer', 'Internet Layer', 'Application Layer', 'Network Access Layer', 'C', 2),
(2, 'What is a key difference between TCP and UDP?', 'TCP is connectionless, while UDP is connection-oriented', 'TCP provides reliable data transfer, while UDP does not', 'UDP includes flow control, while TCP does not', 'TCP operates at the Internet Layer, while UDP operates at the Application Layer', 'B', 3),
(2, 'Which networking device operates at the Data Link layer of the OSI model and forwards packets based on MAC addresses?', 'Router', 'Hub', 'Switch', 'Repeater', 'C', 4),
(2, 'What is the main drawback of using hubs in a network?', 'They cannot filter data and send packets to all connected devices', 'They operate at the Network layer, causing high latency', 'They require complex configuration for small networks', 'They only support wireless connections', 'A', 5);

-- Insert sample questions for Quiz 3
INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer, question_order) VALUES
(3, 'What is the primary purpose of twisting the cables in a twisted pair cable?', 'To increase the data transmission speed', 'To protect against electromagnetic interference (EMI)', 'To reduce the cost of the cable', 'To make the cable more flexible', 'B', 1),
(3, 'Which type of fiber optic cable is typically used for long-distance applications like telephone and cable television?', 'Multi-mode fiber', 'Step-index fiber', 'Graded-index fiber', 'Single-mode fiber', 'D', 2),
(3, 'What is the main difference between baseband and broadband coaxial cable?', 'Baseband uses analog signals, while broadband uses digital signals', 'Baseband is bidirectional, while broadband is unidirectional', 'Baseband is more expensive than broadband', 'Baseband can transmit multiple signals simultaneously', 'B', 3),
(3, 'In satellite communication, what is the purpose of using different frequencies for uplink and downlink?', 'To reduce the cost of the satellite', 'To avoid interference between transmitted and received signals', 'To increase the bandwidth of the transmission', 'To simplify the installation process', 'B', 4),
(3, 'Which of the following is a characteristic of circuit switching?', 'Resources are reserved for the duration of the session', 'Packets can take different routes to the destination', 'It uses store-and-forward transmission', 'It is connectionless', 'A', 5); 