<?php
session_start();
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$pdo = new PDO("mysql:host=localhost;dbname=smarthire", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$userId = $_SESSION['user_id'];

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_resume_id'])) {
    $resumeId = $_POST['delete_resume_id'];
    
    // Verify the resume belongs to the user
    $stmt = $pdo->prepare("SELECT pdf_path FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resumeId, $userId]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resume) {
        try {
            $pdo->beginTransaction();
            
            // Delete from recommendations table
            $pdo->prepare("DELETE FROM recommendations WHERE resume_id = ?")
                ->execute([$resumeId]);
            
            // Delete from resumes table
            $pdo->prepare("DELETE FROM resumes WHERE id = ?")
                ->execute([$resumeId]);
            
            $pdo->commit();
            
            // Delete the PDF file if it exists
            $pdfPath = __DIR__ . '/' . ltrim($resume['pdf_path'], '/');
            if (file_exists($pdfPath)) {
                if (unlink($pdfPath)) {
                    error_log("Deleted PDF file: $pdfPath");
                } else {
                    error_log("Failed to delete PDF file: $pdfPath");
                }
            }
            
            // Return success response
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Delete error for resume $resumeId: " . $e->getMessage());
            header('Content-Type: application/json', true, 500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete record']);
        }
    } else {
        header('Content-Type: application/json', true, 404);
        echo json_encode(['success' => false, 'message' => 'Record not found or unauthorized']);
    }
    exit();
}

// Fetch history
$stmt = $pdo->prepare(
    "SELECT r.id, r.filename, r.pdf_path, r.uploaded_at,
            rec.recommended_jobs, rec.learning_paths
     FROM resumes r
     JOIN recommendations rec ON rec.resume_id = r.id
     WHERE r.user_id = ?
     ORDER BY r.uploaded_at DESC"
);
$stmt->execute([$userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - SmartHire</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --card-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-radius: 20px;
            --accent-color: #667eea;
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background particles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.08) 2px, transparent 2px),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.08) 2px, transparent 2px),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 60px 60px, 80px 80px, 40px 40px;
            animation: float 25s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(120deg); }
            66% { transform: translateY(10px) rotate(240deg); }
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: none;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: var(--text-primary) !important;
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
            color: var(--accent-color) !important;
            transform: translateY(-2px);
        }

        /* Main Container */
        .main-container {
            padding-top: 100px;
            padding-bottom: 50px;
            position: relative;
            z-index: 1;
        }

        .history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Section */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            animation: slideInFromTop 0.8s ease-out;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
            font-weight: 300;
        }

        /* History Card */
        .history-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            animation: slideInFromBottom 0.8s ease-out 0.2s both;
            position: relative;
        }

        .history-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
            padding: 30px;
            margin: -1px;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out;
            animation-delay: calc(var(--i) * 0.1s);
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
        }

        /* Table Section */
        .table-section {
            padding: 30px;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .table {
            margin: 0;
            border: none;
        }

        .table thead {
            background: var(--primary-gradient);
        }

        .table th {
            border: none;
            color: white;
            font-weight: 600;
            padding: 20px 15px;
            text-align: center;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .table td {
            border: none;
            padding: 20px 15px;
            text-align: center;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            animation: slideInFromLeft 0.6s ease-out;
            animation-delay: calc(var(--i) * 0.1s);
            animation-fill-mode: both;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced cells */
        .date-cell {
            font-weight: 600;
            color: var(--text-primary);
        }

        .filename-cell {
            font-weight: 500;
            color: var(--accent-color);
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .jobs-cell {
            max-width: 300px;
        }

        .job-tag {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            margin: 2px;
            animation: popIn 0.3s ease-out;
        }

        .download-btn, .delete-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .download-btn {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
        }

        .download-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
        }

        .delete-btn {
            background: var(--danger-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .delete-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--text-secondary);
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .empty-description {
            color: var(--text-secondary);
            margin-bottom: 30px;
        }

        /* Back button */
        .back-section {
            text-align: center;
            padding: 30px;
            border-top: 1px solid #f1f5f9;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            background: var(--secondary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(240, 147, 251, 0.3);
        }

        .btn-back:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(240, 147, 251, 0.4);
        }

        /* Animations */
        @keyframes slideInFromTop {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInFromBottom {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInFromLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-title { font-size: 2rem; }
            .history-container { padding: 10px; }
            .stats-grid { grid-template-columns: 1fr; }
            .table-container { overflow-x: auto; }
            .table { min-width: 600px; }
            .table th, .table td { padding: 15px 10px; font-size: 0.9rem; }
            .filename-cell { max-width: 150px; }
            .jobs-cell { max-width: 200px; }
        }

        /* Loading skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Tooltip styles */
        .tooltip-custom {
            position: relative;
            cursor: help;
        }

        .tooltip-custom::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--text-primary);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .tooltip-custom:hover::after {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-brain me-2"></i>SmartHire
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
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
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user me-1"></i>Profile
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

    <!-- Main Content -->
    <div class="main-container">
        <div class="history-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-history me-3"></i>Your Journey History
                </h1>
                <p class="page-subtitle">Track your progress and revisit your career recommendations</p>
            </div>

            <!-- History Card -->
            <div class="history-card">
                <!-- Stats Section -->
                <div class="stats-section">
                    <div class="stats-grid">
                        <div class="stat-item" style="--i: 0">
                            <div class="stat-number"><?= count($rows) ?></div>
                            <div class="stat-label">Total Reports</div>
                        </div>
                        <div class="stat-item" style="--i: 1">
                            <div class="stat-number">
                                <?php 
                                $totalJobs = 0;
                                foreach($rows as $row) {
                                    $jobs = json_decode($row['recommended_jobs'], true);
                                    $totalJobs += is_array($jobs) ? count($jobs) : 0;
                                }
                                echo $totalJobs;
                                ?>
                            </div>
                            <div class="stat-label">Job Recommendations</div>
                        </div>
                        <div class="stat-item" style="--i: 2">
                            <div class="stat-number">
                                <?= count($rows) > 0 ? date('M Y', strtotime($rows[0]['uploaded_at'])) : 'N/A' ?>
                            </div>
                            <div class="stat-label">Latest Report</div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="table-section">
                    <?php if (count($rows) > 0): ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-calendar me-2"></i>Date</th>
                                      
                                        <th><i class="fas fa-briefcase me-2"></i>Jobs Suggested</th>
                          
                                        <th><i class="fas fa-trash me-2"></i>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($rows as $index => $row): ?>
                                    <tr style="--i: <?= $index ?>">
                                        <td class="date-cell">
                                            <div class="tooltip-custom" data-tooltip="<?= date('l, F j, Y \a\t g:i A', strtotime($row['uploaded_at'])) ?>">
                                                <div style="font-weight: 600;"><?= date('d M Y', strtotime($row['uploaded_at'])) ?></div>
                                                <div style="font-size: 0.8rem; color: #718096;"><?= date('H:i', strtotime($row['uploaded_at'])) ?></div>
                                            </div>
                                        </td>
                                        
                                        <td class="jobs-cell">
                                            <?php 
                                            $jobs = json_decode($row['recommended_jobs'], true);
                                            if (is_array($jobs) && count($jobs) > 0): 
                                                foreach($jobs as $job): 
                                            ?>
                                                <span class="job-tag"><?= htmlspecialchars($job) ?></span>
                                            <?php 
                                                endforeach;
                                            else: 
                                            ?>
                                                <span class="text-muted"><i class="fas fa-exclamation-circle me-1"></i>No jobs found</span>
                                            <?php endif; ?>
                                        </td>
                                       
                                        <td>
                                            <button class="delete-btn" data-resume-id="<?= $row['id'] ?>" onclick="deleteResume(<?= $row['id'] ?>, this)">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <h3 class="empty-title">No Reports Yet</h3>
                            <p class="empty-description">
                                Upload your first resume to start building your career history and get personalized job recommendations.
                            </p>
                            <a href="dashboard.php" class="btn-back">
                                <i class="fas fa-upload"></i>
                                Upload Resume
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Back Section -->
                <?php if (count($rows) > 0): ?>
                <div class="back-section">
                    <a href="dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Delete resume function
        function deleteResume(resumeId, button) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                return;
            }

            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            button.disabled = true;

            fetch('history.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'delete_resume_id=' + encodeURIComponent(resumeId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.closest('tr').style.transition = 'opacity 0.3s ease';
                    button.closest('tr').style.opacity = '0';
                    setTimeout(() => {
                        button.closest('tr').remove();
                        // Update stats
                        const totalReports = document.querySelector('.stat-item:nth-child(1) .stat-number');
                        totalReports.textContent = parseInt(totalReports.textContent) - 1;
                        const totalJobs = document.querySelector('.stat-item:nth-child(2) .stat-number');
                        const jobs = button.closest('tr').querySelectorAll('.job-tag').length;
                        totalJobs.textContent = parseInt(totalJobs.textContent) - jobs;
                        // Show empty state if no rows left
                        if (document.querySelectorAll('.table tbody tr').length === 0) {
                            const tableSection = document.querySelector('.table-section');
                            tableSection.innerHTML = `
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <h3 class="empty-title">No Reports Yet</h3>
                                    <p class="empty-description">
                                        Upload your first resume to start building your career history and get personalized job recommendations.
                                    </p>
                                    <a href="dashboard.php" class="btn-back">
                                        <i class="fas fa-upload"></i> Upload Resume
                                    </a>
                                </div>
                            `;
                            document.querySelector('.back-section').remove();
                        }
                        alert(data.message);
                    }, 300);
                } else {
                    alert(data.message || 'Failed to delete record');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the record');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        // Enhanced table interactions
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('.table tbody tr');
            
            // Add hover effects with enhanced animations
            tableRows.forEach((row, index) => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.02) translateY(-2px)';
                    this.style.zIndex = '10';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) translateY(0)';
                    this.style.zIndex = '1';
                });
            });

            // Add loading animation to download buttons
            const downloadBtns = document.querySelectorAll('.download-btn');
            downloadBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
                    this.style.pointerEvents = 'none';
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.pointerEvents = 'auto';
                    }, 2000);
                });
            });

            // Add loading animation to delete buttons
            const deleteBtns = document.querySelectorAll('.delete-btn');
            deleteBtns.forEach(btn => {
                btn.addEventListener('error', function() {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
                    this.style.background = 'var(--danger-gradient)';
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.background = 'var(--danger-gradient)';
                        this.disabled = false;
                    }, 3000);
                });
            });

            // Add intersection observer for scroll animations
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

            // Observe stat items and table rows
            document.querySelectorAll('.stat-item, .table tbody tr').forEach(item => {
                observer.observe(item);
            });

            // Add smooth scrolling
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
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

            // Add keyboard navigation for table
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    const focusedRow = document.activeElement.closest('tr');
                    if (focusedRow) {
                        e.preventDefault();
                        const rows = Array.from(document.querySelectorAll('.table tbody tr'));
                        const currentIndex = rows.indexOf(focusedRow);
                        let nextIndex = e.key === 'ArrowDown' ? currentIndex + 1 : currentIndex - 1;
                        
                        if (nextIndex >= 0 && nextIndex < rows.length) {
                            rows[nextIndex].focus();
                        }
                    }
                }
            });

            // Make table rows focusable
            tableRows.forEach(row => {
                row.setAttribute('tabindex', '0');
            });
        });

        // Add performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.now();
            if (loadTime < 100) {
                console.log('⚡ SmartHire History loaded in', Math.round(loadTime), 'ms');
            }
        });
    </script>
</body>
</html>
