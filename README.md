# SmartHire: AI-Powered Resume & Career Analyzer Platform

SmartHire is a full-stack platform that empowers users to upload, analyze, and improve their resumes using advanced AI. The platform provides ATS (Applicant Tracking System) compatibility scoring, actionable suggestions, personalized job recommendations, and learning plans to help users achieve their career goals.




## Features

- **Resume Upload & Analysis:** Upload your resume (PDF) and receive a detailed analysis of your skills and gaps.
- **ATS Score & Suggestions:** Get an AI-generated ATS compatibility score and actionable tips to improve your resume for automated screening systems.
- **Job Recommendations:** Receive personalized job role suggestions based on your skills and current market demand.
- **Learning Plans:** Get an 8-week learning plan with weekly tasks to build the skills needed for your target job roles.
- **Resume Builder with Multiple Templates:** Easily create a new resume from scratch using a guided builder and choose from multiple modern templates. Download your resume as PDF.
- **History & Progress:** View your past uploads, recommendations, and progress in a beautiful dashboard.
- **User Authentication:** Secure registration, login, and OTP email verification.

## Tech Stack

- **Frontend:** PHP, Bootstrap 5, HTML5, CSS3, JavaScript
- **Backend:** Python (Flask), PyMuPDF, Azure/OpenAI API
- **Database:** MySQL
- **Other:** PHPMailer (for OTP/email), Chart.js (for data visualization)

## Folder Structure

```
SmartHire/
├── ai_engine/           # Python backend (Flask API, resume analysis, job recommender)
├── uploads/             # Uploaded resumes and generated reports
├── vendor/              # PHP dependencies (e.g., PHPMailer)
├── index.php            # Landing page
├── login.php            # User login
├── register.php         # User registration
├── upload.php           # Resume upload & result display
├── dashboard.php        # User dashboard
├── history.php          # Resume/report history
├── profile.php          # User profile
├── db_config.php        # Database connection
├── mail_config.php      # Email/OTP configuration
└── ...
```

## Setup Instructions

1. **Clone the repository** and place it in your XAMPP/htdocs directory.
2. **Install Python dependencies:**
   ```bash
   cd ai_engine
   pip install -r requirements.txt
   ```
3. **Configure Azure/OpenAI API:**
   - Set your API key and deployment in `ai_engine/azure_client.py`.
4. **Set up MySQL database:**
   - Import the provided SQL schema and create a database named `smarthire`.
5. **Start the Flask backend:**
   ```bash
   cd ai_engine
   python app.py
   ```
6. **Start Apache/MySQL via XAMPP** and access the app at `http://localhost/SmartHire/`.


## Usage

- Register and verify your email via OTP.
- Upload your resume and receive instant AI-powered analysis, ATS score, and job recommendations.
- Use the Resume Builder to create a new resume with your details and select from multiple templates.
- Download your AI-generated report or resume as PDF, or view your history.

## Contributing
Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

## License
This project is for educational and demonstration purposes only.

---
Built with ❤️ by the SmartHire Team.
