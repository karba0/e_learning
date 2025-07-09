<?php
session_start();

// Include database connection
require_once '../config/conn.php';

// Check if connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get file ID from URL parameter
$fileId = isset($_GET['file_id']) ? (int)$_GET['file_id'] : null;

if ($fileId) {
    // Get specific file by ID
    $sql = "SELECT * FROM files WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $fileId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // Fallback to latest uploaded file
    $sql = "SELECT * FROM files ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);
}

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $filePath = $row['filepath'];
    $fileName = $row['filename'] ?? 'Reading Material';
    $description = $row['description'] ?? '';
    
    // Always use a web path for the browser
    $webFilePath = '../uploads/' . basename($filePath);
    if (!file_exists($webFilePath)) {
        echo "File not found: $webFilePath";
        exit;
    }
} else {
    echo "No reading material found.";
    exit;
}

$testPath = '../uploads/Chapter_2.pdf';
echo file_exists($testPath) ? '' : 'File does NOT exist!';

// Map file_id to quiz in database
// First, try to find a quiz that matches the file_id or course_id
$quiz_id = null;

// Check if there's a quiz specifically mapped to this file_id
$sql = "SELECT id FROM quizzes WHERE course_id = ? AND is_active = 1 ORDER BY id ASC LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $fileId);
mysqli_stmt_execute($stmt);
$quiz_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($quiz_result) > 0) {
    $quiz_row = mysqli_fetch_assoc($quiz_result);
    $quiz_id = $quiz_row['id'];
} else {
    // Fallback: try to find any active quiz
    $sql = "SELECT id FROM quizzes WHERE is_active = 1 ORDER BY id ASC LIMIT 1";
    $fallback_result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($fallback_result) > 0) {
        $fallback_row = mysqli_fetch_assoc($fallback_result);
        $quiz_id = $fallback_row['id'];
    }
}

// Set quiz file path
if ($quiz_id) {
    $quizFile = '../components/quiz_template_db.php?quiz_id=' . $quiz_id . '&file_id=' . $fileId;
} else {
    // If no quiz found, redirect to quiz management to create one
    $quizFile = '../components/quiz_management.php';
}

// Debug output to help diagnose mapping issues
// Remove or comment out in production
// --- DEBUG START ---
// echo "<pre>";
// echo "Current file_id: $fileId\n";
// echo "Quiz found for this chapter: $quiz_id\n";
// echo "</pre>";
// --- DEBUG END ---

$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

$stats_result = $conn->query("SELECT 
    COUNT(*) as total_participants,
    AVG(score) as average_score,
    MAX(score) as highest_score,
    MIN(score) as lowest_score
    FROM quiz_results WHERE user_name IS NOT NULL AND user_name != ''");
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($fileName) ?> - Reading Material</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', 'Segoe UI', 'Roboto', Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
            /* Techy animated gradient background */
            background: linear-gradient(120deg, #232526 0%, #414345 100%);
            background-size: 200% 200%;
            animation: techGradientMove 12s ease-in-out infinite;
            position: relative;
        }
        @keyframes techGradientMove {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 0;
            pointer-events: none;
            background: url('data:image/svg+xml;utf8,<svg width="100%25" height="100%25" xmlns="http://www.w3.org/2000/svg"><rect width="100%25" height="100%25" fill="none"/><g stroke="%2366e0ff" stroke-width="0.7" opacity="0.13"><line x1="0" y1="0" x2="100%25" y2="100%25"/><line x1="100%25" y1="0" x2="0" y2="100%25"/><line x1="50%25" y1="0" x2="50%25" y2="100%25"/><line x1="0" y1="50%25" x2="100%25" y2="50%25"/></g></svg>');
            background-size: cover;
        }
        .container {
            max-width: 900px;
            margin: 48px auto 0 auto;
            background: rgba(30, 34, 44, 0.92);
            padding: 40px 28px 48px 28px;
            border-radius: 28px;
            box-shadow: 0 8px 32px rgba(66, 224, 255, 0.18), 0 1.5px 4px rgba(60, 72, 100, 0.09);
            backdrop-filter: blur(12px) saturate(1.2);
            animation: fadeIn 1.2s cubic-bezier(.4,0,.2,1);
            position: relative;
            z-index: 1;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 {
            font-family: 'Montserrat', 'Nunito', 'Segoe UI', Arial, sans-serif;
            color: #66e0ff;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: 1px;
            text-shadow: 0 0 12px #66e0ff88, 0 2px 8px #000a;
        }
        .description {
            text-align: center;
            color: #b2eaff;
            margin-bottom: 32px;
            font-style: italic;
            font-size: 1.18rem;
        }
        .file-display {
            margin: 28px 0 36px 0;
            padding: 0;
            border: none;
            border-radius: 18px;
            background: rgba(44, 54, 74, 0.92);
            box-shadow: 0 4px 24px rgba(66, 224, 255, 0.10);
            overflow: hidden;
            transition: box-shadow 0.3s;
        }
        .file-display embed {
            border-radius: 0;
            box-shadow: none;
            background: none;
        }
        .quiz-btn {
            background: linear-gradient(90deg, #66e0ff 0%, #7f53ff 100%);
            color: #fff;
            padding: 16px 44px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.25rem;
            font-weight: 700;
            box-shadow: 0 4px 18px rgba(66, 224, 255, 0.13);
            display: none;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            margin: 0 auto;
            display: block;
            letter-spacing: 0.7px;
            outline: none;
            animation: fadeIn 1.5s 0.5s backwards;
            text-shadow: 0 0 8px #66e0ff88;
        }
        .quiz-btn:hover, .quiz-btn:focus {
            background: linear-gradient(90deg, #7f53ff 0%, #66e0ff 100%);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 32px rgba(66, 224, 255, 0.18);
        }
        .file-link {
            color: #66e0ff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.12rem;
            transition: color 0.2s;
        }
        .file-link:hover {
            color: #7f53ff;
            text-decoration: underline;
        }
        .error-message {
            color: #fff;
            background: #e74c3c;
            padding: 14px;
            border-radius: 10px;
            margin: 14px 0;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(60, 72, 100, 0.10);
        }
        .debug-info {
            background: #232526;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 13px;
            color: #6c757d;
        }
        @media (max-width: 600px) {
            .container {
                padding: 12px 2vw 24px 2vw;
                border-radius: 12px;
            }
            h2 {
                font-size: 1.4rem;
            }
            .file-display {
                margin: 12px 0 18px 0;
                border-radius: 10px;
            }
            .quiz-btn {
                width: 100%;
                padding: 14px 0;
                font-size: 1.05rem;
                border-radius: 8px;
            }
        }
        #pdf-viewer {
            width: 95vw;
            min-height: 90vh;
            max-height: 80vh;
            overflow-y: auto;
            background: #222;
            border-radius: 12px;
        }
    </style>
</head>
<body style="margin:0; padding:0;">
    <a href="single1.php" class="back-btn" style="
        position:absolute;
        top:12px;
        left:12px;
        z-index:10000;
        display: inline-block;
        padding: 4px 12px;
        background: #7f53ff;
        color: #fff;
        border-radius: 5px;
        font-weight: 500;
        font-size: 0.85rem;
        letter-spacing: 0.1px;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(66,224,255,0.08);
        transition: background 0.2s, transform 0.1s;
        text-shadow: none;
        margin: 0;
    "
    onmouseover="this.style.background='#66e0ff';this.style.transform='scale(1.01)';"
    onmouseout="this.style.background='#7f53ff';this.style.transform='none';"
    >
        ← Back To Chapter
    </a>
    <!-- PDF Viewer -->
    <div id="pdf-viewer" style="width:95vw; min-height:90vh; margin:0 auto;"></div>

    <!-- Start Quiz Button directly below PDF -->
    <?php if ($quiz_id): ?>
        <div style="text-align:center; margin: 32px 0;">
            <a href="<?php echo $quizFile; ?>" class="quiz-btn" id="quiz-btn" style="display:none; background:linear-gradient(90deg,#66e0ff 0%,#7f53ff 100%); color:#fff; padding:16px 44px; border:none; border-radius:12px; font-size:1.25rem; font-weight:700; text-decoration:none;">Start Quiz</a>
        </div>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tsparticles/3.1.0/tsparticles.min.js"></script>
    <script>
// --- NEW PER-PAGE TIMER LOGIC ---
let minPageViewTime = 13; // seconds per page
let minTotalTime = 20; // seconds for the whole reading (already in your code)
let pageAccumulatedTime = {}; // { pageId: seconds }
let pageTimers = {}; // { pageId: intervalId }
let pageViewed = {}; // { pageId: true/false }
let totalPages = 0;
const quizBtn = document.getElementById("quiz-btn");
if (quizBtn) quizBtn.style.display = "none";
let minTimePassed = false;
let lastAllowedPage = 1; // The last page the user is allowed to scroll to

function scrollToPage(pageNum) {
    const canvas = document.getElementById('pdf-page-' + pageNum);
    if (canvas) {
        canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function showQuizBtnIfReady() {
    const allViewed = Object.values(pageViewed).filter(Boolean).length === totalPages;
    if (minTimePassed && allViewed) {
        if (quizBtn) quizBtn.style.display = "block";
    }
}

// Minimum total time for the whole reading
setTimeout(function() {
    minTimePassed = true;
    showQuizBtnIfReady();
}, minTotalTime * 1000);

pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
var url = '<?= htmlspecialchars($webFilePath) ?>';
var loadingTask = pdfjsLib.getDocument(url);
loadingTask.promise.then(function(pdf) {
    var viewer = document.getElementById('pdf-viewer');
    totalPages = pdf.numPages;
    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        pdf.getPage(pageNum).then(function(page) {
            var scale = 1.2;
            var viewport = page.getViewport({scale: scale});
            var canvas = document.createElement('canvas');
            canvas.id = 'pdf-page-' + pageNum;
            var context = canvas.getContext('2d');
            var dpr = window.devicePixelRatio || 1;
            canvas.width = viewport.width * dpr;
            canvas.height = viewport.height * dpr;
            canvas.style.width = '95vw';
            canvas.style.maxWidth = '1200px';
            canvas.style.height = (viewport.height) + 'px';
            canvas.style.display = 'block';
            canvas.style.margin = '24px auto';
            canvas.style.boxShadow = '0 2px 8px rgba(60,72,100,0.07)';
            context.setTransform(dpr, 0, 0, dpr, 0, 0);
            viewer.appendChild(canvas);
            page.render({canvasContext: context, viewport: viewport});
        });
    }
    setTimeout(() => {
        for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
            const pageId = 'pdf-page-' + pageNum;
            pageAccumulatedTime[pageId] = 0;
            pageViewed[pageId] = false;
            pageTimers[pageId] = null;
        }
        // Intersection Observer for per-page view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                const pageId = entry.target.id;
                const currentPageNum = parseInt(pageId.replace('pdf-page-', ''));
                if (entry.isIntersecting) {
                    // Prevent scrolling to next page if previous not finished
                    if (currentPageNum > lastAllowedPage) {
                        // User tried to scroll down too soon
                        alert('Please spend at least 13 seconds on the current page before proceeding.');
                        scrollToPage(lastAllowedPage);
                        return;
                    }
                    // Start/resume timer for this page
                    if (!pageViewed[pageId] && !pageTimers[pageId]) {
                        pageTimers[pageId] = setInterval(() => {
                            pageAccumulatedTime[pageId]++;
                            if (pageAccumulatedTime[pageId] >= minPageViewTime) {
                                clearInterval(pageTimers[pageId]);
                                pageTimers[pageId] = null;
                                pageViewed[pageId] = true;
                                // Allow scrolling to the next page
                                if (currentPageNum === lastAllowedPage && lastAllowedPage < totalPages) {
                                    lastAllowedPage++;
                                }
                                showQuizBtnIfReady();
                            }
                        }, 1000);
                    }
                } else {
                    // Pause timer for this page
                    if (pageTimers[pageId]) {
                        clearInterval(pageTimers[pageId]);
                        pageTimers[pageId] = null;
                    }
                }
            });
            showQuizBtnIfReady();
        }, { threshold: 0.5 }); // At least 50% of the page in view
        // Observe all canvases
        for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
            const canvas = document.getElementById('pdf-page-' + pageNum);
            if (canvas) observer.observe(canvas);
        }
        // Scroll to the first page on load
        scrollToPage(1);
    }, 1200); // Wait a bit for rendering
}, function (reason) {
    document.getElementById('pdf-viewer').innerText = 'Failed to load PDF: ' + reason;
});

    // Particle background as a network
    tsParticles.load("particles-bg", {
        fullScreen: { enable: false },
        background: { color: "transparent" },
        particles: {
            number: { value: 45, density: { enable: true, value_area: 900 } },
            color: { value: ["#66e0ff", "#7f53ff", "#b2eaff"] },
            shape: { type: "circle" },
            opacity: { value: 0.7, random: true },
            size: { value: 4, random: { enable: true, minimumValue: 2 } },
            move: { enable: true, speed: 1.1, direction: "none", random: false, straight: false, outModes: { default: "out" } },
            links: { enable: true, distance: 160, color: "#66e0ff", opacity: 0.35, width: 2 }
        },
        interactivity: {
            events: { onHover: { enable: true, mode: "grab" }, resize: true },
            modes: { grab: { distance: 180, links: { opacity: 0.7 } } }
        },
        detectRetina: true
    });
    // Style the particles background
    const particlesBg = document.getElementById('particles-bg');
    particlesBg.style.position = 'fixed';
    particlesBg.style.top = 0;
    particlesBg.style.left = 0;
    particlesBg.style.width = '100vw';
    particlesBg.style.height = '100vh';
    particlesBg.style.zIndex = 1;
    particlesBg.style.pointerEvents = 'none';
    // Ensure SVG overlay is behind particles
    document.getElementById('network-svg').style.zIndex = 0;
    </script>
</body>
</html>
