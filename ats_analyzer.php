<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

// Configuration
$apiEndpoint = 'http://localhost:5000/ats-analyze'; // Adjust to your Flask server URL
$uploadDir = 'Uploads/';
$allowedTypes = ['application/pdf'];
$maxFileSize = 5 * 1024 * 1024; // 5MB
// Initialize variables
$score = null;
$key_points = [];
$suggestions = [];
$txt_path = null;
$errors = [];

// Create uploads directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {
    $file = $_FILES['resume'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading file: " . $file['error'];
    } elseif ($file['size'] > $maxFileSize) {
        $errors[] = "File size exceeds 5MB limit.";
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $errors[] = "Only PDF files are allowed.";
    } else {
        $filePath = $uploadDir . uniqid() . '.pdf';
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Prepare cURL request to Flask API
            $ch = curl_init($apiEndpoint);
            $postFields = [
                'resume' => new CURLFile($filePath, 'application/pdf', basename($filePath)),
                'user_id' => $_SESSION['user_id']
            ];
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if (isset($result['error'])) {
                    $errors[] = "API Error: " . $result['error'];
                } else {
                    $score = isset($result['score']) ? $result['score'] : 0;
                    $key_points = isset($result['key_points']) ? $result['key_points'] : [];
                    $suggestions = isset($result['suggestions']) ? $result['suggestions'] : [];
                    $txt_path = isset($result['txt_path']) ? $result['txt_path'] : null;
                }
            } else {
                $errors[] = "API request failed with status $httpCode: " . htmlspecialchars($response);
            }

            // Clean up uploaded file
            unlink($filePath);
        } else {
            $errors[] = "Failed to save the uploaded file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATS Analysis - AI Job Mentor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #1f2937;
            --light: #f8fafc;
            --glass: rgba(255, 255, 255, 0.1);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.2) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* Navbar Styles */
        .navbar {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Container and Card Styles */
        .main-container {
            max-width: 900px;
            margin: 120px auto 40px;
            padding: 0 20px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .card-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .card-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .card-body {
            padding: 40px;
        }

        /* Upload Section */
        .upload-section {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .upload-section:hover {
            border-color: var(--primary);
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            transform: translateY(-2px);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin-top: 20px;
        }

        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .file-input-button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Score Display */
        .score-container {
            text-align: center;
            margin: 40px 0;
        }

        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 20px auto;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .score-circle::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: rotate 3s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .score-label {
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .score-value {
            position: relative;
            z-index: 2;
        }

        /* Results Sections */
        .results-section {
            margin-top: 40px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .points-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }

        .point-item {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid var(--success);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .point-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-lg);
        }

        .suggestion-item {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            border-left: 4px solid var(--warning);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }

        .suggestion-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-lg);
        }

        /* Alert Styles */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, #5a6fd8, #6b5b95);
        }

        .btn-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-link:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* Download Section */
        .download-section {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin-top: 30px;
            border: 1px solid rgba(14, 165, 233, 0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 15px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                margin-top: 100px;
                padding: 0 15px;
            }

            .card-header {
                padding: 30px 20px;
            }

            .card-header h1 {
                font-size: 2rem;
            }

            .card-body {
                padding: 30px 20px;
            }

            .score-circle {
                width: 120px;
                height: 120px;
                font-size: 2rem;
            }

            .upload-section {
                padding: 20px;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
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
                            <i class="fas fa-history me-1"></i>Past Reports
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
        <div class="glass-card">
            <!-- Card Header -->
            <div class="card-header">
                <h1><i class="fas fa-search-plus me-3"></i>ATS Resume Analysis</h1>
                <p>Get your resume analyzed by our AI-powered ATS system and receive actionable insights</p>
            </div>

            <!-- Card Body -->
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <!-- Error Display -->
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Oops! Something went wrong:</strong>
                        <?php foreach ($errors as $error): ?>
                            <div class="mt-2"><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif (isset($score)): ?>
                    <!-- Results Display -->
                    <div class="score-container">
                        <div class="score-circle">
                            <div class="score-value"><?php echo round($score); ?>%</div>
                            <div class="score-label">ATS Score</div>
                        </div>
                        <h4 class="mt-3">
                            <?php if ($score >= 80): ?>
                                <i class="fas fa-trophy text-warning me-2"></i>Excellent ATS Compatibility!
                            <?php elseif ($score >= 60): ?>
                                <i class="fas fa-thumbs-up text-success me-2"></i>Good ATS Compatibility
                            <?php else: ?>
                                <i class="fas fa-tools text-primary me-2"></i>Room for Improvement
                            <?php endif; ?>
                        </h4>
                    </div>

                    <?php if (!empty($key_points)): ?>
                        <!-- Key Points Section -->
                        <div class="results-section">
                            <h3 class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-star"></i>
                                </div>
                                Key Strengths
                            </h3>
                            <div class="points-grid">
                                <?php foreach ((array)$key_points as $point): ?>
                                    <div class="point-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <?php echo htmlspecialchars($point); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($suggestions)): ?>
                        <!-- Suggestions Section -->
                        <div class="results-section">
                            <h3 class="section-title">
                                <div class="section-icon">
                                    <i class="fas fa-lightbulb"></i>
                                </div>
                                Improvement Suggestions
                            </h3>
                            <div class="points-grid">
                                <?php foreach ((array)$suggestions as $suggestion): ?>
                                    <div class="suggestion-item">
                                        <i class="fas fa-arrow-up text-warning me-2"></i>
                                        <?php echo htmlspecialchars($suggestion); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <i class="fas fa-medal me-2"></i>
                            <strong>Congratulations!</strong> Your resume is already well-optimized for ATS systems. No major improvements needed!
                        </div>
                    <?php endif; ?>

                    <?php if ($txt_path): ?>
                        <!-- Download Section -->
                      
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Upload Section -->
                    <div class="upload-section">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h4>Upload Your Resume</h4>
                        <p class="mb-0">Upload your PDF resume to get instant ATS analysis and improvement suggestions</p>
                        
                        <form method="POST" enctype="multipart/form-data" id="uploadForm" class="mt-4">
                            <div class="file-input-wrapper">
                                <input type="file" name="resume" accept=".pdf" required class="file-input" id="resumeFile">
                                <button type="button" class="file-input-button" id="fileButton">
                                    <i class="fas fa-file-pdf me-2"></i>Choose PDF File
                                </button>
                            </div>
                            <div class="selected-file mt-3" id="selectedFile" style="display: none;">
                                <i class="fas fa-file-pdf text-primary me-2"></i>
                                <span id="fileName"></span>
                                <button type="button" class="btn btn-sm btn-link text-danger ms-2" id="removeFile">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3" id="submitBtn">
                                <i class="fas fa-analysis me-2"></i>Analyze Resume
                            </button>
                        </form>
                        
                        <div class="mt-4">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Supported format: PDF only • Maximum file size: 5MB
                            </small>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Back to Dashboard Button -->
                <div class="text-center mt-4">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File input handling
        const fileInput = document.getElementById('resumeFile');
        const fileButton = document.getElementById('fileButton');
        const selectedFile = document.getElementById('selectedFile');
        const fileName = document.getElementById('fileName');
        const removeFile = document.getElementById('removeFile');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');

        if (fileInput && fileButton) {
            fileButton.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    const file = e.target.files[0];
                    fileName.textContent = file.name;
                    selectedFile.style.display = 'block';
                    fileButton.innerHTML = '<i class="fas fa-file-pdf me-2"></i>Change File';
                }
            });

            removeFile.addEventListener('click', () => {
                fileInput.value = '';
                selectedFile.style.display = 'none';
                fileButton.innerHTML = '<i class="fas fa-file-pdf me-2"></i>Choose PDF File';
            });

            // Form submission handling
            uploadForm.addEventListener('submit', (e) => {
                submitBtn.innerHTML = '<div class="loading me-2"></div>Analyzing...';
                submitBtn.disabled = true;
            });
        }

        // Add smooth scrolling for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll to results if they exist
            if (document.querySelector('.score-container')) {
                setTimeout(() => {
                    document.querySelector('.score-container').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 500);
            }
        });
    </script>
</body>
</html>
