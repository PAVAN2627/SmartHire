
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['user_id'];

$pdo = new PDO("mysql:host=localhost;dbname=smarthire", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once 'mail_config.php'; // sendReportEmail()

/* ----------------------------------------------------------- */
/* 1) POST — Upload + AI analysis                             */
/* ----------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {
    /* Save file */
    $baseDir = __DIR__ . "/Uploads/resumes/user_$userId/";
    if (!is_dir($baseDir)) mkdir($baseDir, 0777, true);

    $file = $_FILES['resume'];
    if ($file['error'] !== UPLOAD_ERR_OK) die("❌ File upload failed.");
    $filename = basename($file['name']);
    $fullPath = $baseDir . $filename;
    move_uploaded_file($file['tmp_name'], $fullPath);

    /* Call Flask */
    $curl = curl_init("http://127.0.0.1:5000/analyze");
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => [
            'user_id' => $userId,
            'resume' => new CURLFILE($fullPath)
        ],
    ]);
    $response = curl_exec($curl);
    $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    error_log("Flask Response for user $userId: " . $response); // Debug log
    curl_close($curl);
    if (!$response || $http !== 200) die("❌ Flask Error $http<br><pre>$response</pre>");
    $data = json_decode($response, true) ?: die("❌ Invalid JSON<br><pre>$response</pre>");
    error_log("Parsed pdf_path for user $userId: " . $data['pdf_path']); // Debug log

    /* DB insert */
    $rec = $data['recommendations'];
    $skills = $data['resume_data']['skills'];
    $pdfPath = $data['pdf_path']; // e.g., /Uploads/resumes/user_13/ai_report.pdf

    $pdo->beginTransaction();
    $pdo->prepare("INSERT INTO resumes (user_id, filename, pdf_path) VALUES (?,?,?)")
        ->execute([$userId, $filename, $pdfPath]);
    $resumeId = $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO recommendations
               (resume_id, skills, recommended_jobs, learning_paths,
                skill_gaps, career_advice, average_salaries)
               VALUES (?,?,?,?,?,?,?)")
        ->execute([
            $resumeId,
            json_encode($skills),
            json_encode($rec['recommended_jobs']),
            json_encode($rec['weekly_plan']),
            json_encode($rec['skill_gaps']),
            $rec['career_advice'],
            json_encode($rec['average_salaries'])
        ]);

    $pdo->commit();

/* ----------------------------------------------------------- */
/* 2) GET — Re-send last report (Manual Trigger)              */
/* ----------------------------------------------------------- */
} elseif (isset($_GET['send_email'])) {
  $u = $pdo->query("SELECT name, email FROM users WHERE id = $userId")->fetch(PDO::FETCH_ASSOC);
$row = $pdo->query("
    SELECT 
           rec.skills, rec.recommended_jobs, rec.learning_paths,
           rec.career_advice, rec.skill_gaps,
           rec.average_salaries
    FROM resumes r
    JOIN recommendations rec ON rec.resume_id = r.id
    WHERE r.user_id = $userId
    ORDER BY r.id DESC LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if (!$row) die("❌ No previous report found.");

// ✅ Extract and decode
$name = $u['name'] ?? 'User';
$email = $u['email'] ?? 'default@example.com';
$skills = json_decode($row['skills'], true);

$rec = [
    'recommended_jobs' => json_decode($row['recommended_jobs'], true),
    'weekly_plan' => json_decode($row['learning_paths'], true),
    'skill_gaps' => json_decode($row['skill_gaps'], true),
    'career_advice' => $row['career_advice'],
    'average_salaries' => json_decode($row['average_salaries'], true)
];

$ok = sendReportEmail(
    $email,
    $name,
    $skills,
    $rec['recommended_jobs'],
    $rec['weekly_plan'],
    $rec['skill_gaps'],
    $rec['career_advice'],
    $rec['average_salaries']
);

if ($ok) {
    echo "<script>
        alert('✅ Report sent!');
        window.open('upload.php', '_blank');
    </script>";
} else {
    echo "<script>alert('❌ Email failed');</script>";
}



    /* Build $data & $rec for shared display */

    $data['resume_data']['skills'] = json_decode($row['skills'], true);
    $rec = [
        'recommended_jobs' => json_decode($row['recommended_jobs'], true),
        'weekly_plan' => json_decode($row['learning_paths'], true),
        'career_advice' => $row['career_advice'],
        'skill_gaps' => json_decode($row['skill_gaps'], true),
        'course_links' => [],
        'job_scope' => [],
        'average_salaries' => json_decode($row['average_salaries'], true)
    ];
}

/* ----------------------------------------------------------- */
/* 3) Shared Result Display                                   */
/* ----------------------------------------------------------- */
if (!empty($data)):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartHire Results</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --glass-bg: rgba(255, 255, 255, 0.25);
            --glass-border: rgba(255, 255, 255, 0.18);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --shadow-light: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 20px 60px rgba(0, 0, 0, 0.15);
            --shadow-heavy: 0 25px 80px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-attachment: fixed;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.08)"/><circle cx="20" cy="80" r="0.8" fill="rgba(255,255,255,0.06)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.4;
            z-index: -1;
        }

        .floating-orbs {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            animation: float 20s infinite linear;
        }

        .orb:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .orb:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            right: 10%;
            animation-delay: -5s;
        }

        .orb:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            25% { transform: translateY(-20px) rotate(90deg); }
            50% { transform: translateY(-40px) rotate(180deg); }
            75% { transform: translateY(-20px) rotate(270deg); }
        }

        .navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow-light);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .result-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 20px;
            position: relative;
        }

        .result-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: var(--shadow-heavy);
            padding: 50px;
            position: relative;
            overflow: hidden;
            animation: slideInUp 0.8s ease-out;
        }

        .result-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
            animation: shimmer 2s infinite;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .main-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 3rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeInDown 1s ease-out 0.3s both;
        }

        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            position: relative;
            padding-left: 20px;
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 100%;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        .content-section {
            margin-bottom: 3rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out both;
        }

        .content-section:nth-child(even) {
            animation-delay: 0.1s;
        }

        .content-section:nth-child(odd) {
            animation-delay: 0.2s;
        }

        .content-section:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
            background: rgba(255, 255, 255, 0.15);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 1rem;
        }

        .skill-tag {
            background: var(--primary-gradient);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: var(--shadow-light);
            transition: all 0.3s ease;
            animation: popIn 0.5s ease-out both;
        }

        .skill-tag:nth-child(even) {
            animation-delay: 0.1s;
        }

        .skill-tag:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-medium);
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .job-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: all 0.5s;
        }

        .job-card:hover::before {
            left: 100%;
        }

        .job-card:hover {
            transform: translateX(10px);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: var(--shadow-light);
        }

        .learning-step {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid transparent;
            border-image: var(--primary-gradient) 1;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-radius: 0 12px 12px 0;
            position: relative;
            transition: all 0.3s ease;
        }

        .learning-step::before {
            content: counter(step-counter);
            counter-increment: step-counter;
            position: absolute;
            left: -20px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: var(--shadow-light);
        }

        .learning-plan {
            counter-reset: step-counter;
            padding-left: 30px;
        }

        .learning-step:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.15);
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            position: relative;
            overflow: hidden;
        }

        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .chart-container:hover::before {
            opacity: 1;
        }

        canvas {
            max-width: 100%;
            height: 300px !important;
            border-radius: 12px;
        }

        .action-btn {
            display: inline-block;
            padding: 12px 24px;
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            margin: 0 10px 10px 0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-light);
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s;
        }

        .action-btn:hover::before {
            left: 100%;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
            color: white;
            text-decoration: none;
        }

        .action-btn.secondary {
            background: var(--secondary-gradient);
        }

        .action-btn.success {
            background: var(--success-gradient);
        }

        .footer-actions {
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .result-container {
                margin-top: 80px;
                padding: 10px;
            }

            .result-card {
                padding: 20px;
            }

            .main-title {
                font-size: 2rem;
            }

            .content-section {
                padding: 1rem;
            }

            .skill-tag {
                font-size: 0.8rem;
                padding: 6px 12px;
            }

            .learning-plan {
                padding-left: 20px;
            }

            .learning-step::before {
                width: 30px;
                height: 30px;
                left: -15px;
                font-size: 0.8rem;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Floating Orbs -->
    <div class="floating-orbs">
        <div class="orb"></div>
        <div class="orb"></div>
        <div class="orb"></div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-robot me-2"></i>SmartHire
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">
                            <i class="fas fa-history me-1"></i>View History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Result Content -->
    <div class="result-container">
        <div class="result-card">
            <h1 class="main-title">
                <i class="fas fa-check-circle me-3"></i>Analysis Complete
            </h1>

            <!-- Skills Section -->
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-code me-2"></i>Detected Skills
                </h3>
                <div class="skills-container">
                    <?php foreach ($data['resume_data']['skills'] as $index => $skill): ?>
                        <span class="skill-tag" style="animation-delay: <?= $index * 0.1 ?>s">
                            <?= htmlspecialchars($skill) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Career Advice Section -->
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-lightbulb me-2"></i>Career Guidance
                </h3>
                <p style="color: var(--text-primary); line-height: 1.8; font-size: 1.1rem;">
                    <?= nl2br(htmlspecialchars($rec['career_advice'])) ?>
                </p>
            </div>

            <!-- Recommended Jobs Section -->
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-briefcase me-2"></i>Recommended Job Roles
                </h3>
                <?php foreach ($rec['recommended_jobs'] as $index => $job): ?>
                    <div class="job-card" style="animation-delay: <?= $index * 0.1 ?>s">
                        <i class="fas fa-arrow-right me-2" style="color: #667eea;"></i>
                        <strong><?= htmlspecialchars($job) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Learning Plan Section -->
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-graduation-cap me-2"></i>8-Week Learning Roadmap
                </h3>
                <div class="learning-plan">
                    <?php foreach ($rec['weekly_plan'] as $step): ?>
                        <div class="learning-step">
                            <?= htmlspecialchars($step) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Course Links Section -->
            <?php if ($rec['course_links']): ?>
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-book me-2"></i>Recommended Courses
                </h3>
                <?php foreach ($rec['course_links'] as $c): 
                    $title = htmlspecialchars($c['title']);
                    $url = htmlspecialchars($c['url']);
                    $type = ucfirst($c['type']); ?>
                    <div class="job-card">
                        <a href="<?= $url ?>" target="_blank" class="action-btn" style="margin: 0;">
                            <i class="fas fa-external-link-alt me-2"></i><?= $title ?>
                        </a>
                        <small style="color: var(--text-secondary); margin-left: 10px;">(<?= $type ?>)</small>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Skill Gaps Section -->
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-chart-line me-2"></i>Skill Development Areas
                </h3>
                <?php foreach ($rec['skill_gaps'] as $job => $gaps): ?>
                    <div class="job-card">
                        <strong style="color: var(--text-primary);"><?= htmlspecialchars($job) ?>:</strong>
                        <div class="skills-container mt-2">
                            <?php foreach ($gaps as $gap): ?>
                                <span class="skill-tag" style="background: var(--secondary-gradient);">
                                    <?= htmlspecialchars($gap) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Job Market Demand Chart -->
            <?php if (!empty($rec['job_scope']) && is_array($rec['job_scope'])): ?>
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-chart-bar me-2"></i>Job Market Demand Analysis
                </h3>
                <div class="chart-container">
                    <canvas id="scopeChart"></canvas>
                </div>
                <script>
                    const ctx = document.getElementById('scopeChart').getContext('2d');
                    const labels = <?= json_encode(array_keys($rec['job_scope'])); ?>;
                    const values = <?= json_encode(array_values($rec['job_scope'])); ?>;
                    
                    if (labels.length > 0 && values.length > 0) {
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Market Demand (1-10)',
                                    data: values,
                                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                                    borderColor: 'rgba(102, 126, 234, 1)',
                                    borderWidth: 2,
                                    borderRadius: 8,
                                    borderSkipped: false,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        labels: {
                                            color: 'rgba(255, 255, 255, 0.9)',
                                            font: { size: 14, weight: 'bold' }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 10,
                                        grid: {
                                            color: 'rgba(255, 255, 255, 0.1)',
                                            lineWidth: 1
                                        },
                                        ticks: {
                                            color: 'rgba(255, 255, 255, 0.8)',
                                            font: { size: 12, weight: 'bold' }
                                        },
                                        title: {
                                            display: true,
                                            text: 'Demand Level',
                                            color: 'rgba(255, 255, 255, 0.9)',
                                            font: { size: 14, weight: 'bold' }
                                        }
                                    },
                                    x: {
                                        grid: {
                                            color: 'rgba(255, 255, 255, 0.1)',
                                            lineWidth: 1
                                        },
                                        ticks: {
                                            color: 'rgba(255, 255, 255, 0.8)',
                                            font: { size: 12, weight: 'bold' }
                                        },
                                        title: {
                                            display: true,
                                            text: 'Job Roles',
                                            color: 'rgba(255, 255, 255, 0.9)',
                                            font: { size: 14, weight: 'bold' }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        document.getElementById('scopeChart').style.display = 'none';
                        const noData = document.createElement('div');
                        noData.innerHTML = '<p style="color: rgba(255, 255, 255, 0.8); text-align: center; font-size: 1.1rem;"><i class="fas fa-info-circle me-2"></i>No job market demand data available at this time.</p>';
                        document.querySelector('.chart-container').appendChild(noData);
                    }
                </script>
            </div>
            <?php else: ?>
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-chart-bar me-2"></i>Job Market Demand Analysis
                </h3>
                <div class="chart-container">
                    <p style="color: rgba(255, 255, 255, 0.8); text-align: center; font-size: 1.1rem;">
                        <i class="fas fa-info-circle me-2"></i>No job market demand data available at this time.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Average Salary Chart -->
            <?php if (!empty($rec['average_salaries'])): 
                $salLabels = json_encode(array_keys($rec['average_salaries']));
                $salValues = json_encode(array_values($rec['average_salaries'])); ?>
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-rupee-sign me-2"></i>Average Salary Insights in INR
                </h3>
                <div class="chart-container">
                    <canvas id="salaryChart"></canvas>
                </div>
                <script>
                    const ctxSal = document.getElementById('salaryChart').getContext('2d');
                    new Chart(ctxSal, {
                        type: 'bar',
                        data: {
                            labels: <?= $salLabels; ?>,
                            datasets: [{
                                label: 'Average Salary (LPA)',
                                data: <?= $salValues; ?>,
                                backgroundColor: 'rgba(240, 147, 251, 0.8)',
                                borderColor: 'rgba(240, 147, 251, 1)',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: {
                                        color: 'rgba(255, 255, 255, 0.9)',
                                        font: { size: 14, weight: 'bold' }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)',
                                        lineWidth: 1
                                    },
                                    ticks: {
                                        color: 'rgba(255, 255, 255, 0.8)',
                                        font: { size: 12, weight: 'bold' }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Salary in LPA',
                                        color: 'rgba(255, 255, 255, 0.9)',
                                        font: { size: 14, weight: 'bold' }
                                    }
                                },
                                x: {
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)',
                                        lineWidth: 1
                                    },
                                    ticks: {
                                        color: 'rgba(255, 255, 255, 0.8)',
                                        font: { size: 12, weight: 'bold' }
                                    }
                                }
                            }
                        }
                    });
                </script>
            </div>
            <?php endif; ?>

            <!-- Download and Email Section -->
            <div class="content-section">
                <h3 class="section-title">
                    <i class="fas fa-download me-2"></i>Export & Share
                </h3>
                <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                    
                    <a href="upload.php?send_email=1" class="action-btn secondary">
                        <i class="fas fa-envelope me-2"></i>Send to Email
                    </a>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="footer-actions">
                <a href="dashboard.php" class="action-btn">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
                <a href="history.php" class="action-btn success">
                    <i class="fas fa-folder-open me-2"></i>View History
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.getElementById('loadingOverlay').classList.remove('active');
            }, 500);

            const sections = document.querySelectorAll('.content-section');
            sections.forEach((section, index) => {
                section.style.animationDelay = `${index * 0.1}s`;
            });

            const interactiveElements = document.querySelectorAll('.job-card, .learning-step, .skill-tag');
            interactiveElements.forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.02)';
                });
                
                el.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });

            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const orbs = document.querySelectorAll('.orb');
                orbs.forEach((orb, index) => {
                    const speed = 0.5 + (index * 0.1);
                    orb.style.transform = `translateY(${scrolled * speed}px)`;
                });
            });

            document.querySelectorAll('.action-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const left = e.clientX - rect.left - size / 2;
                    const top = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${left}px;
                        top: ${top}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                    `;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            .action-btn {
                position: relative;
                overflow: hidden;
            }
        `;
        document.head.appendChild(style);

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                document.getElementById('loadingOverlay').classList.add('active');
            });
        });

        function typeWriter(element, text, speed = 100) {
            let i = 0;
            element.innerHTML = '';
            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.content-section').forEach(section => {
            observer.observe(section);
        });
    </script>
</body>
</html>
<?php endif; ?>
