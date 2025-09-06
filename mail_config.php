<?php
// mail_config.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';   // Composer autoloader

function sendOTP($toEmail, $toName, $otp) {
    $mail = new PHPMailer(true);

    try {
        // ---------- SMTP SETTINGS ----------
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';      // e.g. smtp.gmail.com
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pavanmalith3@gmail.com';
        $mail->Password   = '';   // Gmail App Password (see tip below)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        // ------------------------------------

        // Sender & recipient
        $mail->setFrom('pavanmalith3@gmail.com', 'SmartHire');
        $mail->addAddress($toEmail, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your SmartHire Verification Code';
        
        // Enhanced OTP Email Template
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SmartHire OTP Verification</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f8f9fa;
                }
                
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: #ffffff;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                }
                
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 40px 30px;
                    text-align: center;
                    position: relative;
                    overflow: hidden;
                }
                
                .header::before {
                    content: '';
                    position: absolute;
                    top: -50%;
                    left: -50%;
                    width: 200%;
                    height: 200%;
                    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
                    background-size: 30px 30px;
                    animation: float 20s ease-in-out infinite;
                }
                
                @keyframes float {
                    0%, 100% { transform: translateY(0px) rotate(0deg); }
                    50% { transform: translateY(-20px) rotate(180deg); }
                }
                
                .logo {
                    display: inline-flex;
                    align-items: center;
                    font-size: 28px;
                    font-weight: 700;
                    color: white;
                    margin-bottom: 10px;
                    position: relative;
                    z-index: 2;
                }
                
                .logo-icon {
                    width: 40px;
                    height: 40px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 12px;
                    font-size: 20px;
                }
                
                .header-subtitle {
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 16px;
                    position: relative;
                    z-index: 2;
                }
                
                .content {
                    padding: 50px 40px;
                    text-align: center;
                }
                
                .greeting {
                    font-size: 24px;
                    font-weight: 600;
                    color: #2d3748;
                    margin-bottom: 20px;
                }
                
                .message {
                    font-size: 16px;
                    color: #718096;
                    margin-bottom: 40px;
                    line-height: 1.8;
                }
                
                .otp-container {
                    background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
                    border-radius: 16px;
                    padding: 30px;
                    margin: 30px 0;
                    border: 2px solid #e2e8f0;
                    position: relative;
                    overflow: hidden;
                }
                
                .otp-container::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
                    animation: shimmer 2s infinite;
                }
                
                @keyframes shimmer {
                    0% { left: -100%; }
                    100% { left: 100%; }
                }
                
                .otp-label {
                    font-size: 14px;
                    color: #667eea;
                    font-weight: 600;
                    margin-bottom: 15px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                
                .otp-code {
                    font-size: 36px;
                    font-weight: 700;
                    color: #667eea;
                    letter-spacing: 8px;
                    margin: 20px 0;
                    font-family: 'Courier New', monospace;
                    text-shadow: 0 2px 4px rgba(102, 126, 234, 0.2);
                }
                
                .otp-instructions {
                    font-size: 14px;
                    color: #718096;
                    margin-top: 15px;
                }
                
                .security-notice {
                    background: #fff8e1;
                    border-left: 4px solid #ffc107;
                    padding: 20px;
                    margin: 30px 0;
                    border-radius: 8px;
                    text-align: left;
                }
                
                .security-notice-title {
                    font-weight: 600;
                    color: #f57c00;
                    margin-bottom: 8px;
                    display: flex;
                    align-items: center;
                }
                
                .security-notice-text {
                    font-size: 14px;
                    color: #795548;
                    line-height: 1.6;
                }
                
                .footer {
                    background: #f8f9fa;
                    padding: 30px 40px;
                    text-align: center;
                    border-top: 1px solid #e2e8f0;
                }
                
                .footer-text {
                    font-size: 14px;
                    color: #718096;
                    margin-bottom: 15px;
                }
                
                .social-links {
                    margin-top: 20px;
                }
                
                .social-link {
                    display: inline-block;
                    width: 40px;
                    height: 40px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 50%;
                    margin: 0 10px;
                    text-decoration: none;
                    color: white;
                    line-height: 40px;
                    font-size: 16px;
                    transition: transform 0.3s ease;
                }
                
                .social-link:hover {
                    transform: translateY(-2px);
                }
                
                .divider {
                    height: 2px;
                    background: linear-gradient(90deg, transparent, #667eea, transparent);
                    margin: 30px 0;
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        margin: 0;
                        border-radius: 0;
                    }
                    
                    .content {
                        padding: 30px 20px;
                    }
                    
                    .header {
                        padding: 30px 20px;
                    }
                    
                    .otp-code {
                        font-size: 28px;
                        letter-spacing: 4px;
                    }
                    
                    .greeting {
                        font-size: 20px;
                    }
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <div class='logo'>
                        <div class='logo-icon'>🧠</div>
                        SmartHire
                    </div>
                    <div class='header-subtitle'>AI-Powered Career Intelligence</div>
                </div>
                
                <div class='content'>
                    <h1 class='greeting'>Hello $toName! 👋</h1>
                    <p class='message'>
                        Welcome to SmartHire! We're excited to help you discover your perfect career path. 
                        To get started, please verify your email address using the code below.
                    </p>
                    
                    <div class='otp-container'>
                        <div class='otp-label'>Your Verification Code</div>
                        <div class='otp-code'>$otp</div>
                        <div class='otp-instructions'>
                            This code will expire in 10 minutes for your security.
                        </div>
                    </div>
                    
                    <div class='security-notice'>
                        <div class='security-notice-title'>
                            🔒 Security Notice
                        </div>
                        <div class='security-notice-text'>
                            Never share this code with anyone. SmartHire will never ask for your verification code via phone or email. 
                            If you didn't request this code, please ignore this email.
                        </div>
                    </div>
                    
                    <div class='divider'></div>
                    
                    <p style='font-size: 14px; color: #718096;'>
                        Need help? Contact our support team at 
                        <a href='mailto:support@smarthire.com' style='color: #667eea; text-decoration: none;'>support@smarthire.com</a>
                    </p>
                </div>
                
                <div class='footer'>
                    <p class='footer-text'>
                        Thank you for choosing SmartHire - Where AI meets your career aspirations.
                    </p>
                    <div class='social-links'>
                        <a href='#' class='social-link'>📧</a>
                        <a href='#' class='social-link'>💼</a>
                        <a href='#' class='social-link'>🌐</a>
                    </div>
                    <p style='font-size: 12px; color: #a0aec0; margin-top: 20px;'>
                        © 2024 SmartHire. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // You can log $mail->ErrorInfo here
        return false;
    }
}

function sendReportEmail($toEmail, $toName, $skills, $jobs, $learning, $skillGaps, $careerAdvice, $salaries) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'pavanmalith3@gmail.com';
        $mail->Password   = 'qsqa drxj xflr ergx';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('pavanmalith3@gmail.com', 'SmartHire');
        $mail->addAddress($toEmail, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Your SmartHire Career Intelligence Report 📊";

        // Ensure all arrays are valid and format them nicely
        $skillsList     = is_array($skills)     ? $skills     : explode(", ", $skills);
        $jobsList       = is_array($jobs)       ? $jobs       : explode(", ", $jobs);
        $learningList   = is_array($learning)   ? $learning   : explode(", ", $learning);
        $skillGapsList  = is_array($skillGaps)  ? $skillGaps  : explode(", ", $skillGaps);
        $salariesList   = is_array($salaries)   ? $salaries   : explode(", ", $salaries);

        // Format skills as badges
        $skillsBadges = '';
        foreach ($skillsList as $skill) {
            $skillsBadges .= "<span class='skill-badge'>$skill</span> ";
        }

        // Format jobs as cards
        $jobsCards = '';
        foreach ($jobsList as $index => $job) {
            $icons = ['💼', '🚀', '⭐', '🎯', '💡'];
            $icon = $icons[$index % count($icons)];
            $jobsCards .= "<div class='job-card'><span class='job-icon'>$icon</span><span class='job-title'>$job</span></div>";
        }

        // Format learning paths
        $learningPaths = '';
        foreach ($learningList as $path) {
            $learningPaths .= "<li class='learning-item'>🎓 $path</li>";
        }

        // Format skill gaps
        $skillGapsItems = '';
        foreach ($skillGapsList as $gap) {
            $skillGapsItems .= "<div class='skill-gap'>📈 $gap</div>";
        }

        // Format salaries
        $salaryInfo = '';
        foreach ($salariesList as $salary) {
            $salaryInfo .= "<div class='salary-item'>💰$jobs $salary</div>";
        }

        // Enhanced Report Email Template
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SmartHire Career Report</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f8f9fa;
                }
                
                .email-container {
                    max-width: 700px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 20px;
                    overflow: hidden;
                    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                }
                
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 40px 30px;
                    text-align: center;
                    position: relative;
                    overflow: hidden;
                }
                
                .header::before {
                    content: '';
                    position: absolute;
                    top: -50%;
                    left: -50%;
                    width: 200%;
                    height: 200%;
                    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
                    background-size: 30px 30px;
                    animation: float 20s ease-in-out infinite;
                }
                
                @keyframes float {
                    0%, 100% { transform: translateY(0px) rotate(0deg); }
                    50% { transform: translateY(-20px) rotate(180deg); }
                }
                
                .logo {
                    display: inline-flex;
                    align-items: center;
                    font-size: 32px;
                    font-weight: 700;
                    color: white;
                    margin-bottom: 10px;
                    position: relative;
                    z-index: 2;
                }
                
                .logo-icon {
                    width: 45px;
                    height: 45px;
                    background: rgba(255, 255, 255, 0.2);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 15px;
                    font-size: 24px;
                }
                
                .header-subtitle {
                    color: rgba(255, 255, 255, 0.9);
                    font-size: 18px;
                    position: relative;
                    z-index: 2;
                }
                
                .report-badge {
                    background: rgba(255, 255, 255, 0.2);
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 14px;
                    color: white;
                    margin-top: 15px;
                    display: inline-block;
                    position: relative;
                    z-index: 2;
                }
                
                .content {
                    padding: 40px;
                }
                
                .greeting {
                    font-size: 28px;
                    font-weight: 600;
                    color: #2d3748;
                    margin-bottom: 20px;
                    text-align: center;
                }
                
                .intro-message {
                    font-size: 16px;
                    color: #718096;
                    margin-bottom: 40px;
                    text-align: center;
                    line-height: 1.8;
                }
                
                .section {
                    margin-bottom: 40px;
                    background: #f8f9ff;
                    border-radius: 16px;
                    padding: 30px;
                    border-left: 5px solid #667eea;
                    position: relative;
                    overflow: hidden;
                }
                
                .section::before {
                    content: '';
                    position: absolute;
                    top: 0;
                    right: 0;
                    width: 100px;
                    height: 100px;
                    background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
                }
                
                .section-title {
                    font-size: 20px;
                    font-weight: 600;
                    margin-bottom: 20px;
                    color: #2d3748;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .section-icon {
                    width: 40px;
                    height: 40px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 18px;
                    color: white;
                }
                
                .skill-badge {
                    display: inline-block;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 14px;
                    font-weight: 500;
                    margin: 5px;
                    box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
                }
                
                .job-card {
                    background: white;
                    border-radius: 12px;
                    padding: 20px;
                    margin: 10px 0;
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
                    display: flex;
                    align-items: center;
                    gap: 15px;
                    border-left: 4px solid #667eea;
                    transition: transform 0.3s ease;
                }
                
                .job-card:hover {
                    transform: translateX(5px);
                }
                
                .job-icon {
                    font-size: 24px;
                    width: 50px;
                    height: 50px;
                    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .job-title {
                    font-size: 16px;
                    font-weight: 600;
                    color: #2d3748;
                }
                
                .learning-item {
                    background: white;
                    border-radius: 10px;
                    padding: 15px;
                    margin: 10px 0;
                    list-style: none;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
                    border-left: 4px solid #4facfe;
                    font-weight: 500;
                    color: #2d3748;
                }
                
                .skill-gap {
                    background: white;
                    border-radius: 10px;
                    padding: 15px;
                    margin: 10px 0;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
                    border-left: 4px solid #f093fb;
                    font-weight: 500;
                    color: #2d3748;
                }
                
                .salary-item {
                    background: white;
                    border-radius: 10px;
                    padding: 15px;
                    margin: 10px 0;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
                    border-left: 4px solid #00f2fe;
                    font-weight: 600;
                    color: #2d3748;
                    font-size: 16px;
                }
                
                .career-advice {
                    background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
                    border: 2px solid #ffc107;
                    border-radius: 16px;
                    padding: 25px;
                    margin: 20px 0;
                    color: #f57c00;
                    font-style: italic;
                    line-height: 1.8;
                    position: relative;
                }
                
                .career-advice::before {
                    content: '💡';
                    position: absolute;
                    top: -10px;
                    left: 20px;
                    font-size: 24px;
                    background: white;
                    padding: 5px;
                    border-radius: 50%;
                }
                
                .cta-section {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 16px;
                    padding: 30px;
                    text-align: center;
                    margin: 40px 0;
                    color: white;
                }
                
                .cta-title {
                    font-size: 24px;
                    font-weight: 600;
                    margin-bottom: 15px;
                }
                
                .cta-button {
                    display: inline-block;
                    background: white;
                    color: #667eea;
                    padding: 15px 30px;
                    border-radius: 50px;
                    text-decoration: none;
                    font-weight: 600;
                    font-size: 16px;
                    margin-top: 15px;
                    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
                    transition: transform 0.3s ease;
                }
                
                .cta-button:hover {
                    transform: translateY(-2px);
                }
                
                .footer {
                    background: #f8f9fa;
                    padding: 30px 40px;
                    text-align: center;
                    border-top: 1px solid #e2e8f0;
                }
                
                .footer-text {
                    font-size: 14px;
                    color: #718096;
                    margin-bottom: 15px;
                }
                
                .social-links {
                    margin-top: 20px;
                }
                
                .social-link {
                    display: inline-block;
                    width: 45px;
                    height: 45px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 50%;
                    margin: 0 10px;
                    text-decoration: none;
                    color: white;
                    line-height: 45px;
                    font-size: 18px;
                    transition: transform 0.3s ease;
                }
                
                .social-link:hover {
                    transform: translateY(-3px);
                }
                
                .divider {
                    height: 3px;
                    background: linear-gradient(90deg, transparent, #667eea, transparent);
                    margin: 30px 0;
                    border-radius: 2px;
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        margin: 10px;
                        border-radius: 16px;
                    }
                    
                    .content {
                        padding: 20px;
                    }
                    
                    .header {
                        padding: 30px 20px;
                    }
                    
                    .section {
                        padding: 20px;
                    }
                    
                    .greeting {
                        font-size: 24px;
                    }
                    
                    .job-card {
                        flex-direction: column;
                        text-align: center;
                    }
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='header'>
                    <div class='logo'>
                        <div class='logo-icon'>🧠</div>
                        SmartHire
                    </div>
                    <div class='header-subtitle'>AI-Powered Career Intelligence</div>
                    <div class='report-badge'>📊 Personal Career Report</div>
                </div>
                
                <div class='content'>
                    <h1 class='greeting'>Hello $toName! 🎉</h1>
                    <p class='intro-message'>
                        Your personalized career intelligence report is ready! Our AI has analyzed your profile 
                        and generated insights to accelerate your career growth. Let's explore your potential!
                    </p>
                    
                    <div class='section'>
                        <h2 class='section-title'>
                            <div class='section-icon'>🎯</div>
                            Your Core Skills
                        </h2>
                        <div>$skillsBadges</div>
                    </div>
                    
                    <div class='section'>
                        <h2 class='section-title'>
                            <div class='section-icon'>💼</div>
                            Recommended Job Roles
                        </h2>
                        $jobsCards
                    </div>
                    
                    <div class='section'>
                        <h2 class='section-title'>
                            <div class='section-icon'>🎓</div>
                            Learning Pathways
                        </h2>
                        <ul style='padding: 0; margin: 0;'>
                            $learningPaths
                        </ul>
                    </div>
                    
                
               
                    
                    <div class='section'>
                        <h2 class='section-title'>
                            <div class='section-icon'>💡</div>
                            Career Advice
                        </h2>
                        <div class='career-advice'>
                            $careerAdvice
                        </div>
                    </div>
                    
                    <div class='cta-section'>
                        <h3 class='cta-title'>Ready to Take the Next Step?</h3>
                        <p style='margin-bottom: 0; opacity: 0.9;'>
                            Visit your SmartHire dashboard to explore more opportunities and track your progress.
                        </p>
                        <a href='#' class='cta-button'>View Dashboard</a>
                    </div>
                    
                    <div class='divider'></div>
                    
                    <p style='text-align: center; font-size: 14px; color: #718096;'>
                        Questions about your report? Contact our career experts at 
                        <a href='mailto:support@smarthire.com' style='color: #667eea; text-decoration: none;'>support@smarthire.com</a>
                    </p>
                </div>
                
                <div class='footer'>
                    <p class='footer-text'>
                        Thank you for choosing SmartHire - Where AI meets your career aspirations.
                    </p>
                    <div class='social-links'>
                        <a href='#' class='social-link'>📧</a>
                        <a href='#' class='social-link'>💼</a>
                        <a href='#' class='social-link'>🌐</a>
                    </div>
                    <p style='font-size: 12px; color: #a0aec0; margin-top: 20px;'>
                        © 2024 SmartHire. All rights reserved.<br>
                        This report was generated on " . date('F j, Y \a\t g:i A') . "
                    </p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
        return true;

    } catch (Exception $e) {
        // You can log the error for debugging
        // error_log($mail->ErrorInfo);
        return false;
    }
}
