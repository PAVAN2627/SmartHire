from flask import Flask, request, jsonify
from resume_analyzer import extract_resume_data, extract_resume_text, analyze_ats_score
from job_recommender import recommend_jobs

import os
import traceback

app = Flask(__name__)

# Load Azure OpenAI configuration from environment variables
AZURE_OPENAI_ENDPOINT = os.environ.get('AZURE_OPENAI_ENDPOINT')
AZURE_OPENAI_KEY = os.environ.get('AZURE_OPENAI_KEY')
AZURE_OPENAI_DEPLOYMENT = os.environ.get('AZURE_OPENAI_DEPLOYMENT')
AZURE_OPENAI_API_VERSION = os.environ.get('AZURE_OPENAI_API_VERSION', '2024-02-15-preview')  # Fallback to stable version

@app.route('/analyze', methods=['POST'])
def analyze_resume():
    try:
        uploaded_file = request.files['resume']
        user_id = request.form.get('user_id')

        if not user_id:
            return jsonify({"error": "user_id is required"}), 400

        save_path = os.path.join(os.path.dirname(__file__), f'Uploads\\resumes\\user_{user_id}\\')
        os.makedirs(save_path, exist_ok=True)

        resume_path = os.path.join(save_path, uploaded_file.filename)
        uploaded_file.save(resume_path)

        resume_data = extract_resume_data(resume_path)
        recommendations = recommend_jobs(resume_data)

 

        relative_web_path = f'/Uploads/resumes/user_{user_id}/ai_report.pdf'

        return jsonify({
            "resume_data": resume_data,
            "recommendations": recommendations,
            "pdf_path": relative_web_path
        })
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

@app.route('/ats-analyze', methods=['POST'])
def ats_analyze_resume():
    try:
        if 'resume' not in request.files:
            return jsonify({"error": "No resume file provided"}), 400
        uploaded_file = request.files['resume']
        user_id = request.form.get('user_id')

        if not user_id:
            return jsonify({"error": "user_id is required"}), 400
        if not all([AZURE_OPENAI_ENDPOINT, AZURE_OPENAI_KEY, AZURE_OPENAI_DEPLOYMENT, AZURE_OPENAI_API_VERSION]):
            return jsonify({"error": "Azure OpenAI configuration is missing"}), 500

        save_path = os.path.join(os.path.dirname(__file__), f'Uploads\\resumes\\user_{user_id}\\')
        os.makedirs(save_path, exist_ok=True)

        resume_path = os.path.join(save_path, uploaded_file.filename)
        txt_path = os.path.join(save_path, 'resume_text.txt')
        uploaded_file.save(resume_path)

        # Extract text and save to .txt
        text = extract_resume_text(resume_path, txt_path)

        # Analyze text via Azure OpenAI API
        ats_data = analyze_ats_score(
            text,
            AZURE_OPENAI_KEY,
            AZURE_OPENAI_ENDPOINT,
            AZURE_OPENAI_DEPLOYMENT,
            AZURE_OPENAI_API_VERSION
        )

        # Clean up uploaded PDF file (keep .txt for user reference)
        os.remove(resume_path)

        return jsonify({
            "score": ats_data["score"],
            "key_points": ats_data["key_points"],
            "suggestions": ats_data["suggestions"],
            "txt_path": f'/Uploads/resumes/user_{user_id}/resume_text.txt'
        })
    except Exception as e:
        traceback.print_exc()
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True)