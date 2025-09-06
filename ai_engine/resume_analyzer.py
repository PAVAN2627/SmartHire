import re
import fitz  # PyMuPDF
import os
import requests
import json
import logging
# Set up logging
logging.basicConfig(level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')

KNOWN_SKILLS = [
    "Python", "Java", "C", "C++", "C#", "JavaScript", "TypeScript", "Go", "Rust", "Ruby", "PHP", "Swift", "Kotlin",
    "HTML", "CSS", "Bootstrap", "Tailwind", "React", "Vue", "Angular", "Next.js", "Node.js", "Express.js", "jQuery",
    "Android", "Android Studio", "Kotlin", "Swift", "iOS", "React Native", "Flutter", "Dart", "Firebase", "REST APIs", "UI/UX Design",
    "Django", "Flask", "Spring", "Laravel", "FastAPI", "GraphQL", "MVC", "ASP.NET",
    "MySQL", "PostgreSQL", "SQLite", "MongoDB", "Oracle", "Redis", "Firebase Realtime DB", "Elasticsearch",
    "Docker", "Kubernetes", "AWS", "Azure", "Google Cloud", "CI/CD", "GitHub Actions", "Jenkins", "Terraform", "Ansible",
    "NumPy", "Pandas", "Matplotlib", "Seaborn", "Scikit-learn", "TensorFlow", "PyTorch", "OpenCV", "NLTK", "Spacy",
    "Jupyter", "Keras", "XGBoost", "Hugging Face", "Power BI", "Tableau",
    "Git", "GitHub", "GitLab", "Bitbucket", "VS Code", "IntelliJ", "Eclipse", "Postman", "Notion", "Slack",
    "Agile", "Scrum", "TDD", "OOP", "Design Patterns", "UML", "Unit Testing", "System Design",
    "Figma", "Adobe XD", "Sketch", "Canva", "UI Design", "UX Design", "Wireframing", "Prototyping",
    "Communication", "Teamwork", "Problem Solving", "Leadership", "Project Management"
]

def extract_resume_data(filepath):
    """Extract skills from a PDF resume for job recommendations."""
    try:
        doc = fitz.open(filepath)
        text = ''
        for page in doc:
            text += page.get_text()
        doc.close()

        text_lower = text.lower()
        found_skills = []

        for skill in KNOWN_SKILLS:
            pattern = r'\b' + re.escape(skill.lower()) + r'\b'
            if re.search(pattern, text_lower):
                found_skills.append(skill)

        logging.info(f"Extracted skills: {found_skills}")
        return {
            "skills": sorted(list(set(found_skills)))
        }
    except Exception as e:
        logging.error(f"Failed to extract skills from PDF: {str(e)}")
        raise Exception(f"Failed to extract skills from PDF: {str(e)}")

def extract_resume_text(filepath, output_txt_path):
    """Extract all text from a PDF and save it to a .txt file."""
    try:
        doc = fitz.open(filepath)
        text = ''
        for page in doc:
            text += page.get_text()
        doc.close()

        # Save text to .txt file
        with open(output_txt_path, 'w', encoding='utf-8') as txt_file:
            txt_file.write(text)
        
        logging.info(f"Text extracted and saved to {output_txt_path}")
        return text
    except Exception as e:
        logging.error(f"Failed to extract text from PDF: {str(e)}")
        raise Exception(f"Failed to extract text from PDF: {str(e)}")

def analyze_ats_score(text, api_key, endpoint, deployment, api_version):
    """Send resume text to Azure OpenAI API for ATS analysis."""
    api_endpoint = f"{endpoint.rstrip('/')}/openai/deployments/{deployment}/chat/completions?api-version={api_version}"
    headers = {
        "api-key": api_key,
        "Content-Type": "application/json"
    }
    prompt = (
        "You are an expert in Applicant Tracking Systems (ATS). Analyze the following resume text for ATS compatibility. "
        "Provide a response in strict JSON format with the following fields: "
        "- `score`: An integer (0-100) representing ATS compatibility. "
        "- `key_points`: An array of strings listing strengths (e.g., detected skills, sections). "
        "- `suggestions`: An array of strings with specific suggestions to improve ATS compatibility with one line in details. "
        "Ensure the response is a valid JSON object and nothing else. Do not include plain text or additional formatting.\n\n"
        f"Resume text:\n{text[:4000]}"  # Limit to avoid token limits
    )
    payload = {
        "messages": [{"role": "user", "content": prompt}],
        "max_tokens": 500,
        "temperature": 0.3  # Lower temperature for consistent JSON output
    }

    try:
        logging.info(f"Sending request to: {api_endpoint}")
        response = requests.post(api_endpoint, headers=headers, json=payload)
        logging.info(f"Response status code: {response.status_code}")
        logging.debug(f"Response headers: {response.headers}")
        logging.debug(f"Raw response text: {response.text}")

        response.raise_for_status()  # Raises for 4xx/5xx errors
        result = response.json()
        
        # Extract content from API response
        content = result.get('choices', [{}])[0].get('message', {}).get('content', '')
        if not content:
            logging.error("Empty content received from API")
            raise Exception("Empty content received from API")

        logging.info(f"API response content: {content}")

        try:
            # Attempt to parse content as JSON
            data = json.loads(content)
            # Validate required fields
            if not isinstance(data, dict) or not all(key in data for key in ['score', 'key_points', 'suggestions']):
                logging.error(f"Invalid JSON structure: {content}")
                raise Exception("Invalid JSON structure: missing required fields")
            return {
                "score": data.get("score", 0),
                "key_points": data.get("key_points", []),
                "suggestions": data.get("suggestions", [])
            }
        except json.JSONDecodeError as json_err:
            logging.error(f"Failed to parse API content as JSON: {content}")
            # Fallback: return default response
            return {
                "score": 0,
                "key_points": [],
                "suggestions": ["Unable to analyze resume due to API response error. Please check the resume content and try again."]
            }
    except requests.exceptions.RequestException as req_err:
        logging.error(f"API request failed with status {response.status_code}: {response.text}")
        raise Exception(f"API request failed: {str(req_err)}")
