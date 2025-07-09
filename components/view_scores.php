<?php
session_start();
$conn = new mysqli("localhost", "root", "", "login_db");
$current_user = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$file_id = isset($_GET['file_id']) ? (int)$_GET['file_id'] : 0;
$files = $conn->query("SELECT id, filename FROM files ORDER BY id ASC");

// Build dropdown options
$chapter_options = '';
$chapter_map = [];
if ($files->num_rows > 0) {
    $chapter_num = 1;
    while ($file = $files->fetch_assoc()) {
        $chapter_map[$file['id']] = $file['filename'];
        $selected = ($file['id'] == $file_id) ? ' selected' : '';
        $chapter_options .= '<option value="' . $file['id'] . '"' . $selected . '>Chapter ' . $chapter_num . '</option>';
        $chapter_num++;
    }
}

// Query for leaderboard and stats
$where = "user_name IS NOT NULL AND user_name != ''";
if ($file_id > 0) {
    $where .= " AND qr.file_id = $file_id";
} else {
    // Default to first chapter if none selected
    $first_chapter_id = array_key_first($chapter_map);
    $file_id = $first_chapter_id;
    $where .= " AND qr.file_id = $file_id";
}
$result = $conn->query("SELECT qr.*, f.filename FROM quiz_results qr LEFT JOIN files f ON qr.file_id = f.id WHERE $where ORDER BY qr.file_id, qr.score DESC");
$stats_result = $conn->query("SELECT 
    COUNT(*) as total_participants,
    AVG(score) as average_score,
    MAX(score) as highest_score,
    MIN(score) as lowest_score
    FROM quiz_results qr WHERE $where");
$stats = $stats_result->fetch_assoc();

// Get all results for comparison
$all_results = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $all_results[] = $row;
    }
}

// For per-chapter ranking, build a map of user to highest score for the selected chapter
$ranking_map = [];
if ($file_id > 0) {
    $rank_query = $conn->query("SELECT user_name, MAX(score) as max_score FROM quiz_results WHERE file_id = $file_id AND user_name IS NOT NULL AND user_name != '' GROUP BY user_name ORDER BY max_score DESC");
    $rank = 1;
    while ($row = $rank_query->fetch_assoc()) {
        $ranking_map[$row['user_name']] = $rank++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quiz Leaderboard & Comparison</title>
    <style>
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: linear-gradient(120deg, #43cea2 0%, #185a9d 100%); 
            min-height: 100vh; 
            margin: 0; 
            position: relative; 
        }
        body::before { 
            content: ''; 
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            bottom: 0; 
            background: url(https://www.transparenttextures.com/patterns/cubes.png); 
            opacity: 0.08; 
            z-index: 0; 
        }
        .result-container { 
            background: #fff; 
            max-width: 800px; 
            margin: 40px auto 0 auto; 
            border-radius: 18px; 
            box-shadow: 0 8px 32px rgba(24,90,157,0.18); 
            padding: 38px 32px 28px 32px; 
            position: relative; 
            z-index: 1; 
            text-align: center; 
        }
        .result-header { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin-bottom: 20px; 
        }
        .result-header img { 
            width: 48px; 
            height: 48px; 
            margin-right: 16px; 
        }
        h2 { 
            color: #185a9d; 
            font-size: 2rem; 
            margin-bottom: 18px; 
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #185a9d;
            display: block;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .leaderboard { 
            margin: 0 auto; 
            text-align: left; 
            max-width: 100%; 
        }
        .leaderboard-header {
            background: #185a9d;
            color: white;
            padding: 12px 18px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            display: grid;
            grid-template-columns: 60px 1fr 100px 120px 100px;
            gap: 15px;
            align-items: center;
        }
        .leaderboard-entry { 
            background: #f0f7fa; 
            border-radius: 0; 
            padding: 12px 18px; 
            border: 1px solid #e0eaf3; 
            display: grid;
            grid-template-columns: 60px 1fr 100px 120px 100px;
            gap: 15px;
            align-items: center;
            font-size: 1.08rem;
            transition: background-color 0.2s;
        }
        .leaderboard-entry:hover {
            background: #e3f2fd;
        }
        .leaderboard-entry:last-child {
            border-radius: 0 0 8px 8px;
        }
        .rank {
            font-weight: bold;
            color: #185a9d;
            text-align: center;
        }
        .rank-1 { color: #ffd700; }
        .rank-2 { color: #c0c0c0; }
        .rank-3 { color: #cd7f32; }
        .user { 
            font-weight: 600; 
            color: #185a9d; 
        }
        .score { 
            color: #388e8e; 
            font-weight: 500; 
            text-align: center;
        }
        .percentage {
            color: #28a745;
            font-weight: 500;
            text-align: center;
        }
        .date {
            color: #6c757d;
            font-size: 0.9rem;
            text-align: center;
        }
        form { 
            margin-bottom: 24px; 
        }
        select { 
            padding: 8px 16px; 
            border-radius: 8px; 
            border: 1px solid #b2c9e2; 
            font-size: 1rem; 
            background: white;
        }
        label { 
            font-weight: 500; 
            color: #185a9d; 
            margin-right: 8px; 
        }
        .comparison-note {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #155724;
            font-size: 0.95rem;
        }
        .back-btn {
            background: linear-gradient(90deg, #66e0ff 0%, #7f53ff 100%);
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 224, 255, 0.3);
        }
        @media (max-width: 768px) { 
            .result-container { 
                padding: 20px 15px; 
                margin: 20px 10px;
            } 
            .result-header img { 
                width: 36px; 
                height: 36px; 
                margin-right: 10px; 
            } 
            .leaderboard-header,
            .leaderboard-entry {
                grid-template-columns: 40px 1fr 80px;
                gap: 10px;
            }
            .date {
                display: none;
            }
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
                padding: 15px;
            }
        }
        .current-user {
            background: #ffe082 !important;
            border: 2px solid #ffb300 !important;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="result-header">
            <img src="https://cdn-icons-png.flaticon.com/512/3062/3062634.png" alt="Networking Icon">
            <h2>Quiz Leaderboard & Comparison</h2>
        </div>
        
        <form method="get" style="margin-bottom: 24px;">
            <label for="file_id">Select Chapter: </label>
            <select name="file_id" id="file_id" onchange="this.form.submit()">
                <?php echo $chapter_options; ?>
            </select>
        </form>

        <?php if ($stats['total_participants'] > 0): ?>
        <div class="stats-container">
            <div class="stat-item">
                <span class="stat-value"><?php echo $stats['total_participants']; ?></span>
                <span class="stat-label">Total Participants</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo round($stats['average_score'], 1); ?></span>
                <span class="stat-label">Average Score</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo $stats['highest_score']; ?></span>
                <span class="stat-label">Highest Score</span>
            </div>
            <div class="stat-item">
                <span class="stat-value"><?php echo $stats['lowest_score']; ?></span>
                <span class="stat-label">Lowest Score</span>
            </div>
        </div>
        <?php endif; ?>

        <div class="comparison-note">
            <strong>📊 Compare your performance:</strong> See how you rank against other participants. The leaderboard shows rankings, scores, percentages, and completion dates.
        </div>

        <div class="leaderboard">
            <?php if (count($all_results) > 0): ?>
                <div class="leaderboard-header">
                    <div>Rank</div>
                    <div>Chapter</div>
                    <div>Participant</div>
                    <div>Score</div>
                    <div>Percentage</div>
                    <div>Date</div>
                </div>
                <?php 
                $rank = 1;
                foreach ($all_results as $row): 
                    $percentage = round(($row['score'] / (int)$row['total_questions']) * 100);
                    $is_current_user = ($current_user && $row['user_name'] === $current_user);
                    // Per-chapter ranking
                    $display_rank = $file_id > 0 ? ($ranking_map[$row['user_name']] ?? '-') : $rank;
                ?>
                    <div class="leaderboard-entry<?php if($is_current_user) echo ' current-user'; ?>">
                        <div class="rank rank-<?php echo $display_rank <= 3 ? $display_rank : ''; ?>">
                            <?php echo $display_rank; ?>
                        </div>
                        <div class="chapter"><?php echo htmlspecialchars($row['filename'] ?? ('Chapter ' . $row['file_id'])); ?></div>
                        <div class="user"><?php echo htmlspecialchars($row['user_name']); ?></div>
                        <div class="score"><?php echo htmlspecialchars($row['score']); ?>/<?php echo htmlspecialchars($row['total_questions']); ?></div>
                        <div class="percentage"><?php echo $percentage; ?>%</div>
                        <div class="date"><?php echo date('M d, Y', strtotime($row['submitted_date'] ?? 'now')); ?></div>
                    </div>
                <?php 
                    $rank++;
                endforeach; 
                ?>
            <?php else: ?>
                <div class="leaderboard-entry" style="text-align: center; grid-template-columns: 1fr;">
                    <div>No results yet. Be the first to take the quiz!</div>
                </div>
            <?php endif; ?>
        </div>
        
        <a href="../pages/single1.php" class="back-btn" style="margin-right: 10px;">Back to Chapters</a>
        <a href="quiz_template.php" class="back-btn">Back to Quiz</a>
    </div>
</body>
</html>
