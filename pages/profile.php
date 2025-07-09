<?php

@include '../config/conn.php';

session_start();

if(!isset($_SESSION['email'])){
   header('location:../auth/login.php');
   exit;
}

$user = $_SESSION['user_name'];

// Fetch quiz history
$history = [];
$sql = "SELECT qr.file_id, qr.score, qr.created_at, f.filename FROM quiz_results qr LEFT JOIN files f ON qr.file_id = f.id WHERE qr.user_name = '" . mysqli_real_escape_string($conn, $user) . "' ORDER BY qr.created_at DESC LIMIT 10";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $quiz_id = $row['file_id'];
        $history[] = [
            'quiz_id' => $quiz_id,
            'chapter_name' => isset($row['filename']) ? $row['filename'] : 'Chapter ' . $quiz_id,
            'score' => $row['score'],
            'date' => isset($row['created_at']) ? $row['created_at'] : ''
        ];
    }
}
?>
<head>
     <link rel="stylesheet" href="../assets/css/style1.css">
     <style>
        body {
            background: #f4f8fb;
            min-height: 100vh;
            margin: 0;
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
        }
        .profile-main {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding-top: 60px;
        }
        .profile-card {
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.13);
            max-width: 480px;
            width: 100%;
            padding: 38px 32px 28px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 40px;
        }
        .profile-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(120deg, #43cea2 0%, #185a9d 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(24,90,157,0.10);
        }
        .profile-icon img {
            width: 54px;
            height: 54px;
        }
        .profile-username {
            font-size: 2.1rem;
            font-weight: 800;
            color: #185a9d;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }
        .profile-greeting {
            font-size: 1.15rem;
            color: #388e8e;
            margin-bottom: 18px;
            font-style: italic;
        }
        .profile-actions {
            margin-top: 18px;
            display: flex;
            gap: 18px;
        }
        .profile-actions .btn {
            background: #232526;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 28px;
            font-size: 1.08rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
        }
        .profile-actions .btn:hover {
            background: #185a9d;
        }
        .divider {
            width: 100%;
            height: 2px;
            background: #eaf6fb;
            margin: 36px 0 32px 0;
            border-radius: 2px;
        }
        .score-history-title {
            text-align: center;
            color: #185a9d;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 0.5px;
        }
        .score-history-card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 8px 32px rgba(24,90,157,0.13);
            max-width: 600px;
            width: 100%;
            padding: 28px 18px 18px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 40px;
        }
        .score-history-table {
            border-collapse: collapse;
            width: 100%;
            background: transparent;
            border-radius: 12px;
            overflow: hidden;
        }
        .score-history-table th, .score-history-table td {
            padding: 14px 18px;
            text-align: center;
        }
        .score-history-table th {
            background: #185a9d;
            color: #fff;
            font-weight: 700;
            font-size: 1.08rem;
            border: none;
        }
        .score-history-table tr:nth-child(even) {
            background: #f0f7fa;
        }
        .score-history-table tr:nth-child(odd) {
            background: #eaf6fb;
        }
        .score-history-table td {
            color: #185a9d;
            font-weight: 600;
            font-size: 1.05rem;
            border: none;
        }
        @media (max-width: 700px) {
            .profile-card, .score-history-card {
                max-width: 98vw;
                padding: 10px 2vw 10px 2vw;
            }
            .score-history-table th, .score-history-table td {
                padding: 8px 4px;
                font-size: 0.98rem;
            }
        }
     </style>
</head>

<div class="profile-main">
    <div class="profile-card">
        <div class="profile-icon">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="User Icon">
        </div>
        <div class="profile-username"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
        <div class="profile-greeting">You Look Handsome Today.</div>
        <div class="profile-actions">
            <a href="../auth/logout.php" class="btn">Logout</a>
            <a href="../index1.php" class="btn">Home</a>
        </div>
    </div>
    <div class="divider"></div>
    <?php if (!empty($history)): ?>
    <div class="score-history-title">Your Recent Quiz Scores</div>
    <div class="score-history-card">
        <table class="score-history-table">
            <tr>
                <th>Quiz</th>
                <th>Score</th>
                <th>Date</th>
            </tr>
            <?php foreach ($history as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['chapter_name']); ?></td>
                <td><?php echo htmlspecialchars($row['score']); ?></td>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php else: ?>
    <div class="score-history-title">No quiz history yet. Take a quiz to see your scores here!</div>
    <?php endif; ?>
</div>
