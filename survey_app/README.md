# SurveyHub — Anonymous Survey System

An anonymous survey/questionnaire system built with **Slim Framework 4** (PHP). Admins upload CSV files to generate web-based questionnaires, each with a unique URL. Participants can access and answer surveys anonymously. Admins can manage surveys, toggle availability, and download results.

---

## Features

### Admin
- Secure login-protected admin dashboard
- Upload CSV files with questions, correct answers, and wrong options
- Each CSV generates a survey with a unique, shareable URL
- Enable/disable any survey URL through the dashboard
- View detailed response analytics (scores, percentages, per-question breakdowns)
- Export results as a downloadable CSV file
- Delete surveys and all associated data

### Users (Participants)
- Access surveys via unique URLs — no login required
- Answer multiple-choice questions with a clean, responsive interface
- Submit responses anonymously
- See score immediately after submission

---

## Tech Stack

- **Framework**: Slim Framework 4 (PHP)
- **Template Engine**: Twig 3
- **Database**: MySQL (via XAMPP)
- **DI Container**: PHP-DI
- **CSV Parsing**: Native PHP `fgetcsv()`
- **Authentication**: Session-based with password hashing (`password_hash` / `password_verify`)

---

## Project Structure

```
survey_app/
├── config/
│   ├── container.php      # DI container setup (MySQL PDO)
│   ├── routes.php         # Route definitions
│   └── settings.php       # App configuration (DB credentials)
├── database/
│   └── init.php           # MySQL database initialization script
├── public/
│   ├── .htaccess          # Apache rewrite rules
│   └── index.php          # Application entry point
├── src/
│   ├── Controllers/
│   │   ├── AdminController.php   # Dashboard, upload, results, toggle
│   │   ├── AuthController.php    # Login / logout
│   │   └── SurveyController.php  # Public survey display & submission
│   ├── Middleware/
│   │   └── AuthMiddleware.php    # Protects admin routes
│   └── Models/
│       └── SurveyModel.php       # All database operations & CSV parsing
├── templates/
│   ├── layouts/
│   │   └── base.twig             # Base layout with styles
│   ├── admin/
│   │   ├── dashboard.twig        # Survey management dashboard
│   │   ├── upload.twig           # CSV upload form
│   │   └── results.twig          # Response analytics
│   ├── auth/
│   │   └── login.twig            # Admin login page
│   ├── survey/
│   │   ├── questionnaire.twig    # Public survey form
│   │   ├── thank_you.twig        # Post-submission page
│   │   ├── inactive.twig         # Disabled survey notice
│   │   ├── not_found.twig        # 404 for surveys
│   │   └── error.twig            # Error page
│   └── home.twig                 # Landing page
├── uploads/                      # Temporary CSV storage
├── sample_survey.csv             # Example CSV for testing
├── composer.json
├── .gitignore
└── README.md
```

---

## Installation Guide

### Prerequisites

- PHP 8.0 or higher
- Composer
- XAMPP (Apache + MySQL) or any MySQL server

### Step-by-Step Setup

1. **Clone the repository**

   ```bash
   git clone https://github.com/YOUR_USERNAME/survey_app.git
   cd survey_app
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Create the MySQL database**

   Open phpMyAdmin at `http://localhost/phpmyadmin`, click **New**, and create a database named `survey_app` with `utf8mb4_general_ci` collation.

4. **Configure database connection**

   Edit `config/settings.php` if your MySQL credentials differ from the defaults:
   ```php
   'db' => [
       'host' => '127.0.0.1',
       'dbname' => 'survey_app',
       'username' => 'root',
       'password' => '',
       'charset' => 'utf8mb4',
   ],
   ```

5. **Initialize the database tables**

   ```bash
   php database/init.php
   ```

   This creates all 6 tables and a default admin account:
   - **Username**: `admin`
   - **Password**: `admin123`

6. **Start the development server**

   ```bash
   php -S localhost:8080 -t public
   ```

7. **Open in browser**

   - Home page: [http://localhost:8080](http://localhost:8080)
   - Admin login: [http://localhost:8080/login](http://localhost:8080/login)

---

## Database Schema

The application uses 6 MySQL tables:

| Table | Purpose |
|-------|---------|
| `admins` | Admin user credentials |
| `surveys` | Survey metadata (topic, slug, active status) |
| `questions` | Questions linked to surveys |
| `options` | Answer options (correct and wrong) per question |
| `responses` | Anonymous submission records with scores |
| `response_answers` | Individual answers per response |

---

## CSV File Format

Each row of the CSV represents one question. The format is:

```
Question,CorrectAnswer,WrongOption1,WrongOption2,WrongOption3
```

- **Column 1**: Question text
- **Column 2**: Correct answer
- **Column 3+**: Wrong options (at least 1, can add more columns)

### Example

```csv
Question,CorrectAnswer,WrongOption1,WrongOption2,WrongOption3
"What is the capital of France?","Paris","London","Berlin","Madrid"
"Which planet is closest to the Sun?","Mercury","Venus","Mars","Jupiter"
"What is 2 + 2?","4","3","5","6"
```

The first row is treated as a header and skipped if it starts with "Question".

---

## Usage

### Creating a Survey

1. Log in at `/login` with admin credentials
2. Click **"+ New Survey"** or go to **Upload CSV**
3. Enter a unique topic name (e.g., "General Knowledge Quiz")
4. Upload your CSV file
5. The system parses the CSV and creates a survey with a URL like `/survey/general-knowledge-quiz`

### Managing Surveys

From the dashboard you can:
- **Enable/Disable** — Toggle whether participants can access the survey
- **View Results** — See all responses with scores and per-question breakdowns
- **Export CSV** — Download all response data as a CSV file
- **Delete** — Permanently remove a survey and all its data

### Taking a Survey

1. Open the survey URL (e.g., `http://localhost:8080/survey/general-knowledge-quiz`)
2. Answer all multiple-choice questions
3. Click **Submit Responses**
4. See your score on the thank-you page

---

## Default Admin Credentials

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

> **Important**: Change these credentials in production by updating the `database/init.php` script or directly updating the database.

---

## License

This project is created for educational purposes.
