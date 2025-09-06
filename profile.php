<?php
ob_start(); // Start output buffering
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$userId = $_SESSION['user_id'];
try {
    $pdo = new PDO("mysql:host=localhost;dbname=smarthire", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Get user info
$stmt = $pdo->prepare("SELECT name, email, password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found. <a href='logout.php'>Logout</a>");
}

// Get latest recommended job roles
$recQuery = $pdo->prepare("
    SELECT r.recommended_jobs
    FROM recommendations r
    JOIN resumes rs ON rs.id = r.resume_id
    WHERE rs.user_id = ?
    ORDER BY rs.id DESC
    LIMIT 1
");
$recQuery->execute([$userId]);
$rec = $recQuery->fetch(PDO::FETCH_ASSOC);
$recommendedJobs = $rec ? json_decode($rec['recommended_jobs'], true) : null;

// Handle AJAX profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $newName = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($newName) || strlen($newName) > 100) {
        echo json_encode(['success' => false, 'message' => 'Name is required and must be less than 100 characters.']);
        exit;
    }
    if (empty($oldPassword)) {
        echo json_encode(['success' => false, 'message' => 'Current password is required.']);
        exit;
    }

    try {
        // Verify old password
        if (!password_verify($oldPassword, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            exit;
        }

        // Validate new password
        if (!empty($newPassword) && $newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
            exit;
        }
        if (!empty($newPassword) && strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long.']);
            exit;
        }

        // Update database
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
            $stmt->execute([$newName, $hashedPassword, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$newName, $userId]);
        }

        // Update session name
        $_SESSION['user_name'] = $newName;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.', 'newName' => $newName]);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . htmlspecialchars($e->getMessage())]);
    }
    ob_end_flush();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SmartHire</title>
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

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 2px, transparent 2px),
                radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 2px, transparent 2px);
            background-size: 50px 50px;
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

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
            color: #667eea !important;
            transform: translateY(-2px);
        }

        .main-container {
            padding-top: 100px;
            padding-bottom: 50px;
            position: relative;
            z-index: 1;
        }

        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .profile-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 40px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: slideInFromTop 0.8s ease-out;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 3rem;
            color: white;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
        }

        .profile-email {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .info-card, .update-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .info-card:hover, .update-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }

        .card-header {
            background: var(--primary-gradient);
            color: white;
            padding: 20px 30px;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 30px;
        }

        .job-list {
            list-style: none;
            padding: 0;
        }

        .job-item {
            background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-left: 4px solid;
            border-image: var(--primary-gradient) 1;
            transition: all 0.3s ease;
            animation: slideInFromLeft 0.6s ease-out;
            animation-delay: calc(var(--i) * 0.1s);
        }

        .job-item:hover {
            transform: translateX(10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: var(--secondary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(240, 147, 251, 0.3);
        }

        .btn-back:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(240, 147, 251, 0.4);
        }

        .alert {
            border: none;
            border-radius: 15px;
            padding: 20px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
            animation: slideInFromRight 0.5s ease-out;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInFromLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInFromRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .info-card {
            animation: slideInFromLeft 0.8s ease-out 0.2s both;
        }

        .update-card {
            animation: slideInFromLeft 0.8s ease-out 0.4s both;
        }

        @media (max-width: 768px) {
            .profile-container {
                padding: 10px;
            }
            
            .profile-header, .card-body {
                padding: 20px;
            }
            
            .profile-name {
                font-size: 1.5rem;
            }
            
            .card-header {
                padding: 15px 20px;
                font-size: 1.1rem;
            }
        }

        .btn-primary.loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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
                        <a class="nav-link" href="history.php">
                            <i class="fas fa-history me-1"></i>History
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
        <div class="profile-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <h1 class="profile-name"><?= htmlspecialchars($user['name']) ?></h1>
                <p class="profile-email">
                    <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($user['email']) ?>
                </p>
                <div class="mt-4">
                    <a href="dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i>Back to Dashboard
                    </a>
                </div>
            </div>



            <!-- Update Profile Card -->
            <div class="update-card">
                <div class="card-header">
                    <i class="fas fa-edit"></i>
                    Update Profile Information
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>
                    <form method="post" id="updateForm">
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="fas fa-user"></i>Full Name
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="old_password" class="form-label">
                                <i class="fas fa-lock"></i>Current Password
                            </label>
                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password" class="form-label">
                                <i class="fas fa-key"></i>New Password (Optional)
                            </label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-check-circle"></i>Confirm New Password
                            </label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" id="updateBtn">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form submission with AJAX
        document.getElementById('updateForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('updateBtn');
            btn.classList.add('loading');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner me-2"></i>Updating...';

            const alertContainer = document.getElementById('alertContainer');
            const formData = new FormData(this);

            try {
                const response = await fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const responseText = await response.text();
                console.log('Update Profile Response:', responseText);

                const result = JSON.parse(responseText);
                showAlert(result.message, result.success ? 'success' : 'error');

                if (result.success) {
                    // Update displayed name
                    document.querySelector('.profile-name').textContent = result.newName;
                    // Re-run typewriter effect
                    const profileName = document.querySelector('.profile-name');
                    const nameText = profileName.textContent;
                    profileName.textContent = '';
                    let i = 0;
                    const typeWriter = () => {
                        if (i < nameText.length) {
                            profileName.textContent += nameText.charAt(i);
                            i++;
                            setTimeout(typeWriter, 100);
                        }
                    };
                    setTimeout(typeWriter, 500);
                }
            } catch (error) {
                console.error('Update Profile Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
            } finally {
                btn.classList.remove('loading');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-2"></i>Update Profile';
            }
        });

        // Alert system
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} mt-4`;
            alertDiv.textContent = message;
            
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            if (type === 'success') {
                setTimeout(() => {
                    alertDiv.style.opacity = '0';
                    setTimeout(() => alertDiv.remove(), 300);
                }, 3000);
            }
        }

        // Add smooth scrolling to navbar links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add floating animation to cards on scroll
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

        document.querySelectorAll('.info-card, .update-card').forEach(card => {
            observer.observe(card);
        });

        // Add typing effect to profile name
        const profileName = document.querySelector('.profile-name');
        const nameText = profileName.textContent;
        profileName.textContent = '';
        
        let i = 0;
        const typeWriter = () => {
            if (i < nameText.length) {
                profileName.textContent += nameText.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            }
        };
        
        setTimeout(typeWriter, 500);
    </script>
</body>
</html>
