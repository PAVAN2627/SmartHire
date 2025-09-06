<?php
session_start();
// Step 1: DB connection
$host = "localhost";
$dbname = "smarthire";
$user = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Step 2: Handle login request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password_input = $_POST['password'] ?? '';

    // Prevent SQL injection by using prepared statements
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (!$user['otp_verified']) {
            echo "<div class='alert alert-danger text-center animate-alert'>❌ OTP not verified. Please complete verification.</div>";
        } elseif (password_verify($password_input, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set cache-control headers to prevent caching
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

            // PHP redirect as fallback
            header("Location: dashboard.php");
            // JavaScript redirect for visual effect
            echo "<script>
                document.body.classList.add('success-redirect');
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1500);
            </script>";
            exit; // Ensure no further output
        } else {
            echo "<div class='alert alert-danger text-center animate-alert'>❌ Incorrect password.</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center animate-alert'>❌ Email not found.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SmartHire</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --shadow-light: 0 8px 32px rgba(102, 126, 234, 0.2);
            --shadow-heavy: 0 20px 60px rgba(102, 126, 234, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background particles */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 119, 198, 0.2) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(5deg); }
            66% { transform: translateY(10px) rotate(-3deg); }
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
            animation: slideInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-heavy);
            padding: 40px 35px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .login-card:hover::before {
            left: 100%;
        }

        .brand-section {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
        }

        .brand-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-light);
            animation: pulse 2s infinite;
            position: relative;
        }

        .brand-icon::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: var(--primary-gradient);
            border-radius: 25px;
            z-index: -1;
            opacity: 0.3;
            animation: rotate 10s linear infinite;
        }

        .brand-icon i {
            font-size: 32px;
            color: white;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .brand-title {
            font-size: 2.2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .brand-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            font-weight: 400;
        }

        .form-floating {
            position: relative;
            margin-bottom: 25px;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px 20px;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
            background: white;
        }

        .form-floating label {
            color: #9ca3af;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .input-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: color 0.3s ease;
        }

        .form-control:focus + .input-icon {
            color: #667eea;
        }

        .btn-login {
            background: var(--primary-gradient);
            border: none;
            border-radius: 16px;
            padding: 16px 20px;
            width: 100%;
            font-weight: 600;
            font-size: 1.1rem;
            color: white;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(-1px);
        }

        .btn-register {
            background: transparent;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            padding: 14px 20px;
            width: 100%;
            font-weight: 600;
            font-size: 1rem;
            color: #6b7280;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .btn-register:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
        }

        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 20px;
            position: relative;
        }

        .alert {
            border-radius: 16px;
            margin-bottom: 25px;
            border: none;
            padding: 16px 20px;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border-left: 4px solid #059669;
        }

        .animate-alert {
            animation: slideInDown 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
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

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-redirect {
            animation: successFadeOut 1.5s ease-out forwards;
        }

        @keyframes successFadeOut {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.02); }
            100% { opacity: 0; transform: scale(0.98); }
        }

        /* Loading animation */
        .btn-login.loading {
            pointer-events: none;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
            }
            .login-card {
                padding: 30px 25px;
            }
            .brand-title {
                font-size: 1.8rem;
            }
        }

        /* Focus trap for better accessibility */
        .form-control:focus-visible {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="brand-section">
                <div class="brand-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h1 class="brand-title">SmartHire</h1>
                <p class="brand-subtitle">AI-Powered Resume Analyzer</p>
            </div>

            <form method="POST" action="" id="loginForm">
                <div class="form-floating">
                    <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" required>
                    <label for="email">Email Address</label>
                    <i class="fas fa-envelope input-icon"></i>
                </div>

                <div class="form-floating">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
                    <label for="password">Password</label>
                    <i class="fas fa-lock input-icon"></i>
                </div>

                <button type="submit" class="btn btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Sign In to SmartHire
                </button>

                <div class="divider">
                    <span>Don't have an account?</span>
                </div>

                <a href="register.php" class="btn-register">
                    <i class="fas fa-user-plus me-2"></i>
                    Create New Account
                </a>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced form interactions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const inputs = document.querySelectorAll('.form-control');

            // Add loading state to login button
            form.addEventListener('submit', function() {
                loginBtn.classList.add('loading');
                loginBtn.innerHTML = '<i class="fas fa-spinner me-2"></i>Signing In...';
            });

            // Enhanced input animations
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.01)';
                });

                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });

                // Real-time validation feedback
                input.addEventListener('input', function() {
                    if (this.checkValidity()) {
                        this.style.borderColor = '#10b981';
                        this.nextElementSibling.nextElementSibling.style.color = '#10b981';
                    } else {
                        this.style.borderColor = '#ef4444';
                        this.nextElementSibling.nextElementSibling.style.color = '#ef4444';
                    }
                });
            });

            // Keyboard navigation enhancement
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
                    e.preventDefault();
                    const formElements = Array.from(form.elements);
                    const currentIndex = formElements.indexOf(e.target);
                    const nextElement = formElements[currentIndex + 1];
                    if (nextElement) {
                        nextElement.focus();
                    } else {
                        loginBtn.click();
                    }
                }
            });

            // Add particle animation on successful login
            function createSuccessParticles() {
                for (let i = 0; i < 20; i++) {
                    const particle = document.createElement('div');
                    particle.style.cssText = `
                        position: fixed;
                        width: 8px;
                        height: 8px;
                        background: #10b981;
                        border-radius: 50%;
                        pointer-events: none;
                        z-index: 1000;
                        left: ${Math.random() * 100}vw;
                        top: 100vh;
                        animation: particleFloat ${2 + Math.random() * 3}s ease-out forwards;
                    `;
                    document.body.appendChild(particle);
                    setTimeout(() => particle.remove(), 5000);
                }
            }

            // Add CSS for particle animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes particleFloat {
                    to {
                        transform: translateY(-100vh) rotate(720deg);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>
