<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartHire - AI-Powered Career Platform</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --accent-color: #6366f1;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --shadow-soft: 0 10px 40px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 20px 60px rgba(0, 0, 0, 0.15);
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
            color: var(--text-dark);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Animated Background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .bg-shape:nth-child(1) {
            width: 300px;
            height: 300px;
            background: var(--primary-gradient);
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .bg-shape:nth-child(2) {
            width: 200px;
            height: 200px;
            background: var(--secondary-gradient);
            top: 70%;
            right: 10%;
            animation-delay: 2s;
        }

        .bg-shape:nth-child(3) {
            width: 150px;
            height: 150px;
            background: var(--success-gradient);
            top: 40%;
            right: 30%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(20px) rotate(240deg); }
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
        }

        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-brand img {
            height: 45px;
            margin-right: 10px;
            transition: transform 0.3s ease;
        }

        .navbar-brand:hover img {
            transform: scale(1.1) rotate(5deg);
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 10px;
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--accent-color);
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

        /* Hero Section */
        .hero {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 0 20px;
        }

        .hero h1 {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 700;
            margin-bottom: 20px;
            animation: slideUp 1s ease-out;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: clamp(1.1rem, 2vw, 1.3rem);
            margin-bottom: 30px;
            opacity: 0.9;
            animation: slideUp 1s ease-out 0.2s both;
        }

        .hero-cta {
            animation: slideUp 1s ease-out 0.4s both;
        }

        .btn-primary {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        /* Glass Card Sections */
        .glass-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            margin: 30px auto;
            max-width: 1200px;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
            animation: fadeInUp 0.8s ease-out;
        }

        .glass-section:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
            background: rgba(255, 255, 255, 0.15);
        }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: white;
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            width: 60px;
            height: 3px;
            background: var(--success-gradient);
            border-radius: 2px;
            transform: translateX(-50%);
        }

        /* About Section */
        .about-content {
            color: white;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .about-image {
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s ease;
        }

        .about-image:hover {
            transform: scale(1.05);
        }

        /* Services Accordion */
        .accordion {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .accordion-item {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 10px;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .accordion-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .accordion-button {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 20px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .accordion-button:not(.collapsed) {
            background: var(--accent-color);
            color: white;
            box-shadow: 0 5px 20px rgba(99, 102, 241, 0.3);
        }

        .accordion-button:focus {
            box-shadow: none;
            border: none;
        }

        .accordion-body {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            padding: 20px;
            font-size: 1rem;
            line-height: 1.6;
        }

        .service-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .accordion-button:hover .service-icon {
            transform: scale(1.1) rotate(5deg);
            border-color: rgba(255, 255, 255, 0.6);
        }

        /* Team Cards */
        .team-grid {
            gap: 30px;
        }

        .team-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .team-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }

        .team-card:hover::before {
            left: 100%;
        }

        .team-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .team-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 20px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .team-card:hover img {
            border-color: rgba(255, 255, 255, 0.6);
            transform: scale(1.1);
        }

        .team-card h4 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .team-card p {
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .team-card a {
            color: #4facfe;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .team-card a:hover {
            color: #00f2fe;
            text-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
        }

        /* Contact Section */
        .contact-content {
            color: white;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .contact-content a {
            color: #4facfe;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .contact-content a:hover {
            color: #00f2fe;
            text-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
        }

        .btn-contact {
            background: var(--success-gradient);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-contact::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-contact:hover::before {
            left: 100%;
        }

        .btn-contact:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(79, 172, 254, 0.4);
            color: white;
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Scroll animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .glass-section {
                padding: 20px;
                margin: 20px 15px;
            }
            
            .section-title {
                font-size: 1.8rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .btn-primary {
                padding: 12px 30px;
                font-size: 1rem;
            }
        }

        /* Particle Effect */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: particleFloat 8s linear infinite;
        }

        @keyframes particleFloat {
            0% {
                opacity: 0;
                transform: translateY(100vh) translateX(0);
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translateY(-100px) translateX(100px);
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>

    <!-- Particles -->
    <div class="particles" id="particles"></div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="Uploads/smarthire.jpg" alt="SmartHire Logo">
                SmartHire
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#about"><i class="fas fa-info-circle me-1"></i>About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services"><i class="fas fa-cogs me-1"></i>Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#team"><i class="fas fa-users me-1"></i>Team</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-1"></i>Signup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact"><i class="fas fa-envelope me-1"></i>Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Transform Your Career with <span style="background: linear-gradient(45deg, #4facfe, #00f2fe); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">AI Intelligence</span></h1>
            <p>Unlock your potential with AI-powered resume analysis, ATS scoring, personalized job recommendations, and professional resume building</p>
            <div class="hero-cta">
                <a href="upload.php" class="btn btn-primary me-3">
                    <i class="fas fa-rocket me-2"></i>Get Started Now
                </a>
                <a href="#services" class="btn btn-outline-light">
                    <i class="fas fa-play me-2"></i>Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="container-fluid py-5">
        <div class="glass-section animate-on-scroll">
            <h2 class="section-title">About SmartHire</h2>
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="about-content">
                        <p class="lead">SmartHire revolutionizes career development through cutting-edge AI technology. Our platform analyzes your skills, provides detailed ATS scoring, and delivers personalized career recommendations.</p>
                        <p>We bridge the gap between your current abilities and your dream career by offering:</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success me-2"></i>Advanced resume analysis with ATS scoring</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>AI-powered job matching and recommendations</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Professional resume builder with multiple templates</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Personalized learning paths and certifications</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="Uploads/smarthire.jpg" alt="AI Technology" class="img-fluid about-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="container-fluid py-5">
        <div class="glass-section animate-on-scroll">
            <h2 class="section-title">Our AI-Powered Services</h2>
            <div class="accordion" id="servicesAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#service1" aria-expanded="true" aria-controls="service1">
                            <img src="Uploads/resumeiconnew.png" alt="Resume Analysis" class="service-icon">
                            <div>
                                <strong>AI Resume Analysis</strong>
                                <small class="d-block opacity-75">Comprehensive skill evaluation</small>
                            </div>
                        </button>
                    </h2>
                    <div id="service1" class="accordion-collapse collapse show" data-bs-parent="#servicesAccordion">
                        <div class="accordion-body">
                            <i class="fas fa-brain text-info me-2"></i>
                            Upload your resume for intelligent analysis that identifies your core skills, highlights strengths, and reveals improvement areas. Our AI provides actionable insights to enhance your professional profile.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#service2" aria-expanded="false" aria-controls="service2">
                            <img src="Uploads/ats.png" alt="ATS Scoring" class="service-icon">
                            <div>
                                <strong>ATS Score & Optimization</strong>
                                <small class="d-block opacity-75">Beat applicant tracking systems</small>
                            </div>
                        </button>
                    </h2>
                    <div id="service2" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                        <div class="accordion-body">
                            <i class="fas fa-chart-line text-success me-2"></i>
                            Get your resume's ATS compatibility score (1-100) with detailed feedback on keywords, formatting, and structure. Receive specific suggestions to optimize your resume for automated screening systems.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#service3" aria-expanded="false" aria-controls="service3">
                            <img src="Uploads/jobicon.png" alt="Job Recommendations" class="service-icon">
                            <div>
                                <strong>Smart Job Matching</strong>
                                <small class="d-block opacity-75">Personalized career opportunities</small>
                            </div>
                        </button>
                    </h2>
                    <div id="service3" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                        <div class="accordion-body">
                            <i class="fas fa-bullseye text-warning me-2"></i>
                            Discover job roles perfectly matched to your skills with market demand ratings (1-10). Our AI analyzes current industry trends to suggest high-potential career paths.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#service4" aria-expanded="false" aria-controls="service4">
                            <img src="Uploads/learningicon.png" alt="Learning Plans" class="service-icon">
                            <div>
                                <strong>Personalized Learning Paths</strong>
                                <small class="d-block opacity-75">8-week skill development programs</small>
                            </div>
                        </button>
                    </h2>
                    <div id="service4" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                        <div class="accordion-body">
                            <i class="fas fa-graduation-cap text-primary me-2"></i>
                            Receive customized 8-week learning plans with weekly milestones, resources, and practical tasks designed to build the exact skills needed for your target roles.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#service5" aria-expanded="false" aria-controls="service5">
                            <img src="Uploads/certifictionicon.jpeg" alt="Certifications" class="service-icon">
                            <div>
                                <strong>Industry Certifications</strong>
                                <small class="d-block opacity-75">Boost your credentials</small>
                            </div>
                        </button>
                    </h2>
                    <div id="service5" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                        <div class="accordion-body">
                            <i class="fas fa-certificate text-success me-2"></i>
                            Access curated certification recommendations including AWS Solutions Architect, Google Cloud Professional, and industry-specific credentials to enhance your marketability.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#service6" aria-expanded="false" aria-controls="service6">
                            <img src="Uploads/resume builder.png" alt="Resume Builder" class="service-icon">
                            <div>
                                <strong>Professional Resume Builder</strong>
                                <small class="d-block opacity-75">ATS-optimized templates</small>
                            </div>
                        </button>
                    </h2>
                    <div id="service6" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                        <div class="accordion-body">
                            <i class="fas fa-file-alt text-info me-2"></i>
                            Create stunning, ATS-friendly resumes using our collection of professional templates. Real-time guidance ensures your resume meets industry standards and passes automated screening.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#service7" aria-expanded="false" aria-controls="service7">
                            <img src="Uploads/salary.png" alt="Salary Analysis" class="service-icon">
                            <div>
                                <strong>Salary Intelligence</strong>
                                <small class="d-block opacity-75">Market-based compensation insights</small>
                            </div>
                        </button>
                    </h2>
                    <div id="service7" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                        <div class="accordion-body">
                            <i class="fas fa-dollar-sign text-success me-2"></i>
                            Get comprehensive salary data for your target roles including average compensation ranges, location-based variations, and industry benchmarks to negotiate effectively.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section id="team" class="container-fluid py-5">
        <div class="glass-section animate-on-scroll">
            <h2 class="section-title">Meet Our Expert Team</h2>
            <div class="row team-grid justify-content-center">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="team-card">
                        <img src="Uploads/pavanlogo.jpg" alt="Pavan Mali" class="img-fluid">
                        <h4>Pavan Mali</h4>
                        <p><i class="fas fa-university text-info me-2"></i><strong>MMCOE Pune</strong></p>
                        <p><i class="fas fa-code text-warning me-2"></i><strong>Backend Developer</strong></p>
                        <p><i class="fas fa-envelope text-primary me-2"></i><a href="mailto:pavanmali0281@gmail.com">pavanmali0281@gmail.com</a></p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-white me-3"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-github"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="team-card">
                        <img src="Uploads/omkarmahajan.jpg" alt="Omkar Mahajan" class="img-fluid">
                        <h4>Omkar Mahajan</h4>
                        <p><i class="fas fa-university text-info me-2"></i><strong>MMCOE Pune</strong></p>
                        <p><i class="fas fa-robot text-success me-2"></i><strong>AI/ML Engineer</strong></p>
                        <p><i class="fas fa-envelope text-primary me-2"></i><a href="mailto:omkarmahajn339@gmail.com">omkarmahajn339@gmail.com</a></p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-white me-3"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-github"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="team-card">
                        <img src="Uploads/shriyashgore.jpg" alt="Shriyash Gore" class="img-fluid">
                        <h4>Shriyash Gore</h4>
                        <p><i class="fas fa-university text-info me-2"></i><strong>RMD Sinhagad Pune</strong></p>
                        <p><i class="fas fa-palette text-danger me-2"></i><strong>Frontend Developer</strong></p>
                        <p><i class="fas fa-envelope text-primary me-2"></i><a href="mailto:shriyashgore@gmail.com">shriyashgore@gmail.com</a></p>
                        <div class="social-links mt-3">
                            <a href="#" class="text-white me-3"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-github"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="container-fluid py-5">
        <div class="glass-section animate-on-scroll">
            <h2 class="section-title">Get In Touch</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <div class="contact-content">
                        <p class="lead mb-4">Ready to transform your career? We're here to help you every step of the way!</p>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="contact-item">
                                    <i class="fas fa-envelope fa-2x mb-3 text-info"></i>
                                    <h5>Email Us</h5>
                                    <p><a href="mailto:support@smarthire.com">support@smarthire.com</a></p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="contact-item">
                                    <i class="fas fa-phone fa-2x mb-3 text-success"></i>
                                    <h5>Call Us</h5>
                                    <p>+1-800-555-1234</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="contact-item">
                                    <i class="fas fa-clock fa-2x mb-3 text-warning"></i>
                                    <h5>Available</h5>
                                    <p>24/7 Support</p>
                                </div>
                            </div>
                        </div>
                        <p>Have questions about our AI-powered career services? Need help with resume analysis or job recommendations? Our dedicated support team is available around the clock to assist you.</p>
                        <a href="mailto:support@smarthire.com" class="btn-contact">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-4">
        <div class="container">
            <div class="glass-section" style="padding: 20px;">
                <div class="row align-items-center">
                    <div class="col-md-6 text-md-start">
                        <p class="mb-0" style="color: white;">© 2025 SmartHire. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="social-links">
                            <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-github"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for nav links
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

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Particle system
        function createParticle() {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + 'vw';
            particle.style.animationDuration = (Math.random() * 3 + 5) + 's';
            particle.style.opacity = Math.random() * 0.5 + 0.3;
            document.getElementById('particles').appendChild(particle);

            setTimeout(() => {
                particle.remove();
            }, 8000);
        }

        // Create particles periodically
        setInterval(createParticle, 300);

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });

        // Add stagger animation to team cards
        const teamCards = document.querySelectorAll('.team-card');
        teamCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
        });

        // Add hover sound effect (optional)
        document.querySelectorAll('.btn, .nav-link, .team-card').forEach(element => {
            element.addEventListener('mouseenter', () => {
                // You can add sound effects here if needed
                element.style.transform = element.style.transform + ' scale(1.02)';
            });
            
            element.addEventListener('mouseleave', () => {
                element.style.transform = element.style.transform.replace(' scale(1.02)', '');
            });
        });

        // Dynamic gradient animation
        let gradientAngle = 0;
        setInterval(() => {
            gradientAngle += 1;
            document.body.style.background = `linear-gradient(${gradientAngle}deg, #667eea 0%, #764ba2 50%, #f093fb 100%)`;
        }, 100);

        // Loading animation
        window.addEventListener('load', () => {
            document.body.classList.add('loaded');
            
            // Animate hero text
            const heroTitle = document.querySelector('.hero h1');
            const heroText = document.querySelector('.hero p');
            const heroBtn = document.querySelector('.hero-cta');
            
            if (heroTitle) heroTitle.style.animation = 'slideUp 1s ease-out';
            if (heroText) heroText.style.animation = 'slideUp 1s ease-out 0.2s both';
            if (heroBtn) heroBtn.style.animation = 'slideUp 1s ease-out 0.4s both';
        });

        // Add typing effect to hero title
        function typeWriter(element, text, speed = 50) {
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

        // Initialize typing effect on load
        window.addEventListener('load', () => {
            const heroTitle = document.querySelector('.hero h1');
            if (heroTitle) {
                const originalText = heroTitle.textContent;
                setTimeout(() => {
                    typeWriter(heroTitle, originalText, 80);
                }, 500);
            }
        });
    </script>
</body>
</html>