<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Job Mentor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --accent: #f093fb;
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.3) 0%, transparent 50%);
            pointer-events: none;
            z-index: -2;
            animation: backgroundShift 10s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23ffffff" opacity="0.05"/><circle cx="75" cy="25" r="1" fill="%23ffffff" opacity="0.05"/><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.05"/><circle cx="25" cy="75" r="1" fill="%23ffffff" opacity="0.05"/><circle cx="75" cy="75" r="1" fill="%23ffffff" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
            z-index: -1;
        }

        @keyframes backgroundShift {
            0%, 100% { transform: translateX(0) translateY(0); }
            25% { transform: translateX(-20px) translateY(-10px); }
            50% { transform: translateX(20px) translateY(10px); }
            75% { transform: translateX(-10px) translateY(20px); }
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: -2s; }
        .particle:nth-child(3) { left: 30%; animation-delay: -4s; }
        .particle:nth-child(4) { left: 40%; animation-delay: -1s; }
        .particle:nth-child(5) { left: 50%; animation-delay: -3s; }
        .particle:nth-child(6) { left: 60%; animation-delay: -5s; }
        .particle:nth-child(7) { left: 70%; animation-delay: -1.5s; }
        .particle:nth-child(8) { left: 80%; animation-delay: -3.5s; }
        .particle:nth-child(9) { left: 90%; animation-delay: -2.5s; }

        @keyframes float {
            0%, 100% { transform: translateY(100vh) scale(0); }
            10% { transform: translateY(90vh) scale(1); }
            90% { transform: translateY(-10vh) scale(1); }
            100% { transform: translateY(-10vh) scale(0); }
        }

        /* Navbar Styles */
        .navbar {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.05);
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            transition: all 0.3s ease;
            position: relative;
            padding: 8px 16px !important;
            border-radius: 20px;
            margin: 0 4px;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            opacity: 0.1;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary) !important;
            transform: translateY(-2px);
        }

        /* Dashboard Container */
        .dashboard-container {
            max-width: 1400px;
            margin: 120px auto 40px;
            padding: 0 20px;
        }

        /* Welcome Section */
        .welcome-hero {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.85));
            backdrop-filter: blur(20px);
            border-radius: 32px;
            padding: 60px 40px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            animation: slideInDown 0.8s ease-out;
        }

        .welcome-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            animation: rotate 10s linear infinite;
        }

        .welcome-hero-content {
            position: relative;
            z-index: 2;
        }

        .welcome-title {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
            animation: textGlow 2s ease-in-out infinite alternate;
        }

        .welcome-subtitle {
            font-size: 1.3rem;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }

        .stat-label {
            color: #64748b;
            font-weight: 500;
            margin-top: 5px;
        }

        /* Feature Cards Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            animation: slideInUp 0.8s ease-out;
        }

        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .feature-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: rotate(-45deg);
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card:hover .feature-icon::before {
            transform: rotate(-45deg) translateX(100%);
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .feature-description {
            color: #64748b;
            font-weight: 500;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        /* Form Styles */
        .upload-area {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }

        .upload-area::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: all 0.5s ease;
        }

        .upload-area:hover::before {
            left: 100%;
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            transform: translateY(-2px);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 15px;
            animation: bounceUpDown 2s ease-in-out infinite;
        }

        @keyframes bounceUpDown {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-2px);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 16px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 20px 40px -12px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #5a6fd8, #6b5b95);
        }

        .btn-primary:active {
            transform: translateY(-1px) scale(0.98);
        }

        .btn-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .btn-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .btn-link:hover::after {
            width: 100%;
        }

        .btn-link:hover {
            color: var(--secondary);
            transform: translateX(5px);
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        @keyframes textGlow {
            from {
                text-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
            }
            to {
                text-shadow: 0 0 30px rgba(102, 126, 234, 0.8);
            }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                margin-top: 100px;
                padding: 0 15px;
            }

            .welcome-title {
                font-size: 2.5rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .feature-card {
                padding: 30px 25px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 480px) {
            .welcome-hero {
                padding: 40px 25px;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
        }

        /* Pulse animation for icons */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .feature-icon {
            animation: pulse 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <!-- Floating Particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
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
                        <a class="nav-link active" href="dashboard.php">
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

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <!-- Welcome Hero Section -->
        <div class="welcome-hero">
            <div class="welcome-hero-content">
                <h1 class="welcome-title">
                    Welcome Back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 🚀
                </h1>
                <p class="welcome-subtitle">
                    Ready to accelerate your career? Let's explore the tools that will transform your job search journey!
                </p>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number">95%</span>
                        <div class="stat-label">Success Rate</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">1M+</span>
                        <div class="stat-label">Jobs Analyzed</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">50K+</span>
                        <div class="stat-label">Happy Users</div>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">24/7</span>
                        <div class="stat-label">AI Support</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="features-grid">
            <!-- ATS Score Feature -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="feature-title">ATS Score & Analysis</h3>
                <p class="feature-description">
                    Get instant feedback on your resume's ATS compatibility with detailed suggestions for improvement. Our AI analyzes 50+ key factors.
                </p>
                <form id="atsResumeForm" action="ats_analyzer.php" method="POST" enctype="multipart/form-data">
                    <div class="upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h5>Upload Your Resume</h5>
                        <p class="mb-3">Drag & drop your PDF or click to browse</p>
                        <input type="file" name="resume" id="atsResume" class="form-control" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="atsBtn">
                        <i class="fas fa-magic me-2"></i>Analyze Resume
                    </button>
                </form>
            </div>

            <!-- Job Recommendations Feature -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h3 class="feature-title">Smart Job Matching</h3>
                <p class="feature-description">
                    Discover personalized job opportunities that match your skills, experience, and career goals. AI-powered matching technology.
                </p>
                <form action="upload.php" method="POST" enctype="multipart/form-data" id="jobForm">
                    <div class="upload-area">
                        <div class="upload-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5>Upload for Job Matching</h5>
                        <p class="mb-3">Let AI find the perfect jobs for you</p>
                        <input type="file" name="resume" id="jobResume" class="form-control" accept=".pdf" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="jobBtn">
                        <i class="fas fa-rocket me-2"></i>Find My Jobs
                    </button>
                </form>
            </div>

            <!-- Resume Builder Feature -->
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 class="feature-title">Professional Resume Builder</h3>
                <p class="feature-description">
                    Create stunning, ATS-optimized resumes with our professional templates. Stand out from the crowd with modern designs.
                </p>
                <div class="upload-area" style="border-style: solid; border-color: var(--success);">
                    <div class="upload-icon" style="color: var(--success);">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h5>Choose Your Template</h5>
                    <p class="mb-3">Professional designs that get results</p>
                </div>
                <a href="templates.html" class="btn btn-primary">
                    <i class="fas fa-paint-brush me-2"></i>Start Building
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhanced form submission handling
        document.getElementById('atsResumeForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('atsBtn');
            btn.innerHTML = '<div class="loading me-2"></div>Analyzing Your Resume...';
            btn.disabled = true;
        });

        document.getElementById('jobForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('jobBtn');
            btn.innerHTML = '<div class="loading me-2"></div>Finding Perfect Jobs...';
            btn.disabled = true;
        });

        // File input enhancements
        function enhanceFileInput(inputId, cardElement) {
            const fileInput = document.getElementById(inputId);
            const uploadArea = cardElement.querySelector('.upload-area');
            
            fileInput.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    const fileName = e.target.files[0].name;
                    uploadArea.style.borderColor = 'var(--success)';
                    uploadArea.style.background = 'linear-gradient(135deg, #f0fdf4, #dcfce7)';
                    
                    const iconElement = uploadArea.querySelector('.upload-icon i');
                    iconElement.className = 'fas fa-check-circle';
                    iconElement.style.color = 'var(--success)';
                    
                    uploadArea.querySelector('h5').textContent = 'File Selected!';
                    uploadArea.querySelector('p').textContent = fileName;
                }
            });

            // Drag and drop functionality
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.style.borderColor = 'var(--primary)';
                uploadArea.style.transform = 'scale(1.02)';
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.style.borderColor = '#cbd5e1';
                uploadArea.style.transform = 'scale(1)';
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.style.borderColor = '#cbd5e1';
                uploadArea.style.transform = 'scale(1)';
                
                const files = e.dataTransfer.files;
                if (files.length > 0 && files[0].type === 'application/pdf') {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });

            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });
        }

        // Initialize file inputs
        enhanceFileInput('atsResume', document.querySelector('.feature-card:nth-child(1)'));
        enhanceFileInput('jobResume', document.querySelector('.feature-card:nth-child(2)'));

        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease-out';
            observer.observe(card);
        });

        // Add sparkle effect on button hover
        document.querySelectorAll('.btn-primary').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                createSparkles(this);
            });
        });

        function createSparkles(element) {
            for (let i = 0; i < 6; i++) {
                const sparkle = document.createElement('div');
                sparkle.className = 'sparkle';
                sparkle.style.cssText = `
                    position: absolute;
                    width: 4px;
                    height: 4px;
                    background: white;
                    border-radius: 50%;
                    pointer-events: none;
                    opacity: 0;
                    animation: sparkleAnimation 0.8s ease-out forwards;
                `;
                
                const rect = element.getBoundingClientRect();
                sparkle.style.left = Math.random() * rect.width + 'px';
                sparkle.style.top = Math.random() * rect.height + 'px';
                
                element.style.position = 'relative';
                element.appendChild(sparkle);
                
                setTimeout(() => {
                    sparkle.remove();
                }, 800);
            }
        }

        // Add sparkle animation CSS
        const sparkleCSS = document.createElement('style');
        sparkleCSS.textContent = `
            @keyframes sparkleAnimation {
                0% {
                    opacity: 1;
                    transform: scale(0) translateY(0);
                }
                50% {
                    opacity: 1;
                    transform: scale(1) translateY(-10px);
                }
                100% {
                    opacity: 0;
                    transform: scale(0) translateY(-20px);
                }
            }
        `;
        document.head.appendChild(sparkleCSS);

        // Welcome message animation
        const welcomeTitle = document.querySelector('.welcome-title');
        if (welcomeTitle) {
            setTimeout(() => {
                welcomeTitle.style.animation = 'none';
                welcomeTitle.style.animation = 'textGlow 2s ease-in-out infinite alternate';
            }, 1000);
        }

        // Stats counter animation
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                if (element.textContent.includes('%')) {
                    element.textContent = Math.floor(current) + '%';
                } else if (element.textContent.includes('M+')) {
                    element.textContent = (current / 1000000).toFixed(1) + 'M+';
                } else if (element.textContent.includes('K+')) {
                    element.textContent = Math.floor(current / 1000) + 'K+';
                } else {
                    element.textContent = current.toFixed(0) + '/7';
                }
            }, 20);
        }

        // Animate stats when they come into view
        const statsObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumber = entry.target.querySelector('.stat-number');
                    const text = statNumber.textContent;
                    
                    if (text.includes('95%')) {
                        animateCounter(statNumber, 95);
                    } else if (text.includes('1M+')) {
                        animateCounter(statNumber, 1000000);
                    } else if (text.includes('50K+')) {
                        animateCounter(statNumber, 50000);
                    } else if (text.includes('24/7')) {
                        statNumber.textContent = '24/7';
                    }
                    
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat-item').forEach(stat => {
            statsObserver.observe(stat);
        });

        // Add typing effect to subtitle
        const subtitle = document.querySelector('.welcome-subtitle');
        const originalText = subtitle.textContent;
        subtitle.textContent = '';
        
        setTimeout(() => {
            let i = 0;
            const typeWriter = setInterval(() => {
                if (i < originalText.length) {
                    subtitle.textContent += originalText.charAt(i);
                    i++;
                } else {
                    clearInterval(typeWriter);
                }
            }, 30);
        }, 800);

        // Parallax effect for particles
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelectorAll('.particle');
            
            parallax.forEach((particle, index) => {
                const speed = 0.5 + (index * 0.1);
                particle.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

        // Add random delay to particle animations
        document.querySelectorAll('.particle').forEach((particle, index) => {
            particle.style.animationDelay = Math.random() * 6 + 's';
        });
    </script>
</body>
</html>