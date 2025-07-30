<?php
ob_start(); // Start output buffering to prevent stray output
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
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Step 2: Get user email from session
$email = $_SESSION['otp_email'] ?? null;

if (!$email) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Session expired. Please register again.']);
        exit;
    } else {
        die("Session expired. Please register again. <a href='register.php'>Register</a>");
    }
}

// Step 3: Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'verify_otp') {
        $entered_otp = filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_STRING);

        try {
            $stmt = $conn->prepare("SELECT otp_code FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $entered_otp == $user['otp_code']) {
                $conn->prepare("UPDATE users SET otp_verified = 1 WHERE email = ?")->execute([$email]);
                unset($_SESSION['otp_email']);
                echo json_encode(['success' => true, 'message' => 'OTP Verified! Redirecting to login...']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid OTP. Try again.']);
            }
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } elseif ($action === 'resend_otp') {
        try {
            // Generate new OTP
            $new_otp = sprintf("%06d", rand(0, 999999));
            $stmt = $conn->prepare("UPDATE users SET otp_code = ? WHERE email = ?");
            $stmt->execute([$new_otp, $email]);

            // Simulate sending OTP via email (replace with actual email sending logic)
            $_SESSION['otp_code'] = $new_otp; // For testing; remove in production

            echo json_encode(['success' => true, 'message' => 'New OTP sent to your email!']);
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to resend OTP: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
    ob_end_flush(); // Send buffered output
    exit;
}

// Step 4: Handle non-AJAX POST for fallback
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_STRING);

    try {
        $stmt = $conn->prepare("SELECT otp_code FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $entered_otp == $user['otp_code']) {
            $conn->prepare("UPDATE users SET otp_verified = 1 WHERE email = ?")->execute([$email]);
            echo "<div class='alert alert-success text-center animate-alert'>✅ OTP Verified! Redirecting to login in 3 seconds...</div>";
            echo "<script>setTimeout(function() { window.location.href = 'login.php'; }, 3000);</script>";
            unset($_SESSION['otp_email']);
        } else {
            echo "<div class='alert alert-danger text-center animate-alert'>❌ Invalid OTP. Try again.</div>";
        }
    } catch(PDOException $e) {
        echo "<div class='alert alert-danger text-center animate-alert'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - SmartHire</title>
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

        .otp-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
            animation: slideInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
        }

        .otp-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: var(--shadow-heavy);
            padding: 40px 35px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .otp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .otp-card:hover::before {
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

        .otp-input-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 25px;
        }

        .otp-digit {
            width: 50px;
            height: 60px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .otp-digit:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
            background: white;
            outline: none;
        }

        .otp-digit.filled {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .btn-verify {
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-verify::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-verify:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-verify:hover::before {
            left: 100%;
        }

        .btn-verify:active {
            transform: translateY(-1px);
        }

        .btn-verify:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-resend {
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

        .btn-resend:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .resend-section {
            text-align: center;
            margin-top: 20px;
        }

        .resend-text {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .timer {
            color: #ef4444;
            font-weight: 600;
            font-size: 0.9rem;
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

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border-left: 4px solid #dc2626;
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

        .btn-verify.loading {
            pointer-events: none;
        }

        .btn-verify.loading::after {
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

        #otpValue {
            display: none;
        }

        .no-js #otpValue {
            display: block;
            width: 100%;
            padding: 16px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        @media (max-width: 480px) {
            .otp-container {
                padding: 15px;
            }
            .otp-card {
                padding: 30px 25px;
            }
            .brand-title {
                font-size: 1.8rem;
            }
            .otp-digit {
                width: 40px;
                height: 50px;
                font-size: 20px;
            }
        }

        .otp-digit:focus-visible {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="otp-card">
            <div class="brand-section">
                <div class="brand-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1 class="brand-title">SmartHire</h1>
                <p class="brand-subtitle">AI-Powered Recruitment Platform</p>
            </div>

            <div id="alertContainer"></div>

            <h4 class="text-center mb-3">Enter Verification Code</h4>
            <p class="text-center text-muted mb-4">We've sent a 6-digit code to your email</p>

            <noscript>
                <style>
                    .otp-input-group { display: none; }
                    #otpValue { display: block; }
                </style>
                <p class="text-center mb-3">Please enter the 6-digit OTP code below:</p>
            </noscript>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="otpForm">
                <div class="otp-input-group">
                    <input type="text" class="otp-digit" maxlength="1" data-index="0" aria-label="OTP digit 1" required>
                    <input type="text" class="otp-digit" maxlength="1" data-index="1" aria-label="OTP digit 2" required>
                    <input type="text" class="otp-digit" maxlength="1" data-index="2" aria-label="OTP digit 3" required>
                    <input type="text" class="otp-digit" maxlength="1" data-index="3" aria-label="OTP digit 4" required>
                    <input type="text" class="otp-digit" maxlength="1" data-index="4" aria-label="OTP digit 5" required>
                    <input type="text" class="otp-digit" maxlength="1" data-index="5" aria-label="OTP digit 6" required>
                </div>

                <input type="text" name="otp" id="otpValue" required>

                <button type="submit" class="btn-verify" id="verifyBtn" disabled>
                    <i class="fas fa-check-circle me-2"></i>
                    Verify OTP
                </button>
            </form>

            <div class="resend-section">
                <p class="resend-text">Didn't receive the code?</p>
                <button class="btn-resend" id="resendBtn">
                    <i class="fas fa-redo-alt me-2"></i>
                    Resend OTP
                </button>
                <span class="timer" id="timer" style="display: none;"></span>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // OTP Input Management
        const otpInputs = document.querySelectorAll('.otp-digit');
        const otpForm = document.getElementById('otpForm');
        const otpValue = document.getElementById('otpValue');
        const verifyBtn = document.getElementById('verifyBtn');
        const resendBtn = document.getElementById('resendBtn');
        const timer = document.getElementById('timer');
        const alertContainer = document.getElementById('alertContainer');

        // Auto-focus and navigation between OTP inputs
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                const value = e.target.value;
                
                // Only allow numbers and ensure single digit
                if (!/^\d$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                // Add filled class
                e.target.classList.add('filled');

                // Move to next input if available
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }

                updateOTPValue();
                checkFormCompletion();
            });

            input.addEventListener('keydown', function(e) {
                // Handle backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                    otpInputs[index - 1].value = '';
                    otpInputs[index - 1].classList.remove('filled');
                    updateOTPValue();
                    checkFormCompletion();
                }
                
                // Handle left/right arrow keys
                if (e.key === 'ArrowLeft' && index > 0) {
                    otpInputs[index - 1].focus();
                }
                if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text');
                const digits = pasteData.replace(/\D/g, '').slice(0, 6);
                
                digits.split('').forEach((digit, idx) => {
                    if (idx < otpInputs.length) {
                        otpInputs[idx].value = digit;
                        otpInputs[idx].classList.add('filled');
                    }
                });

                // Focus the last filled input or the first empty one
                const lastFilledIndex = Math.min(digits.length - 1, otpInputs.length - 1);
                otpInputs[lastFilledIndex].focus();
                
                updateOTPValue();
                checkFormCompletion();
            });
        });

        function updateOTPValue() {
            const otp = Array.from(otpInputs).map(input => input.value).join('');
            otpValue.value = otp;
        }

        function checkFormCompletion() {
            const allFilled = Array.from(otpInputs).every(input => input.value.length === 1);
            verifyBtn.disabled = !allFilled;
        }

        // Form submission with AJAX
        otpForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            verifyBtn.classList.add('loading');
            verifyBtn.innerHTML = '<i class="fas fa-spinner me-2"></i>Verifying...';

            try {
                const formData = new FormData(otpForm);
                formData.append('action', 'verify_otp');
                const response = await fetch(otpForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Log response for debugging
                const responseText = await response.text();
                console.log('Verify OTP Response:', responseText);

                // Parse JSON
                const result = JSON.parse(responseText);
                showAlert(result.message, result.success ? 'success' : 'danger');
                if (result.success) {
                    document.body.classList.add('success-redirect');
                    createSuccessParticles();
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                }
            } catch (error) {
                console.error('Verify OTP Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            } finally {
                verifyBtn.classList.remove('loading');
                verifyBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Verify OTP';
                checkFormCompletion();
            }
        });

        // Resend functionality with timer
        let resendTimer;
        let timeLeft = 0;

        function startResendTimer() {
            timeLeft = 60;
            resendBtn.style.display = 'none';
            timer.style.display = 'inline';
            
            resendTimer = setInterval(() => {
                timer.textContent = `Resend in ${timeLeft}s`;
                timeLeft--;
                
                if (timeLeft < 0) {
                    clearInterval(resendTimer);
                    resendBtn.style.display = 'inline-block';
                    timer.style.display = 'none';
                }
            }, 1000);
        }

        resendBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i class="fas fa-spinner me-2"></i>Resending...';

            try {
                const formData = new FormData();
                formData.append('action', 'resend_otp');
                const response = await fetch(otpForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Log response for debugging
                const responseText = await response.text();
                console.log('Resend OTP Response:', responseText);

                // Parse JSON
                const result = JSON.parse(responseText);
                showAlert(result.message, result.success ? 'success' : 'danger');
                if (result.success) {
                    startResendTimer();
                }
            } catch (error) {
                console.error('Resend OTP Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            } finally {
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="fas fa-redo-alt me-2"></i>Resend OTP';
            }
        });

        // Alert system
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} animate-alert`;
            alertDiv.textContent = message;
            
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            if (type === 'success') {
                setTimeout(() => {
                    alertDiv.style.opacity = '0';
                    setTimeout(() => alertDiv.remove(), 300);
                }, 3000);
            }
        }

        // Particle animation on success
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

        // Focus first input on load
        window.addEventListener('load', () => {
            otpInputs[0].focus();
        });

        // Handle PHP alerts for non-AJAX fallback
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])): ?>
            <?php if (isset($user) && $entered_otp == $user['otp_code']): ?>
                showAlert('✅ OTP Verified! Redirecting to login...', 'success');
                createSuccessParticles();
            <?php else: ?>
                showAlert('❌ Invalid OTP. Try again.', 'danger');
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>