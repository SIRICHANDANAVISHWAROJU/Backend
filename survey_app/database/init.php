<?php
/**
 * Database Initialization Script (MySQL)
 * 
 * Prerequisites:
 *   1. Make sure XAMPP is running (Apache + MySQL)
 *   2. Create a database called 'survey_app' in phpMyAdmin
 *   3. Then run: php database/init.php
 */

$host = '127.0.0.1';
$dbname = 'survey_app';
$username = 'root';
$password = ''; // default XAMPP MySQL has no password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL successfully.\n\n";

    // ---- Drop existing tables (fresh start) ----
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS response_answers");
    $pdo->exec("DROP TABLE IF EXISTS responses");
    $pdo->exec("DROP TABLE IF EXISTS options");
    $pdo->exec("DROP TABLE IF EXISTS questions");
    $pdo->exec("DROP TABLE IF EXISTS surveys");
    $pdo->exec("DROP TABLE IF EXISTS admins");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Dropped old tables (if any).\n";

    // ---- Admins table ----
    $pdo->exec("
        CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "Created table: admins\n";

    // Insert default admin (username: admin, password: admin123)
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $hash]);
    echo "Inserted default admin user.\n";

    // ---- Surveys table ----
    $pdo->exec("
        CREATE TABLE surveys (
            id INT AUTO_INCREMENT PRIMARY KEY,
            topic VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "Created table: surveys\n";

    // ---- Questions table ----
    $pdo->exec("
        CREATE TABLE questions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            survey_id INT NOT NULL,
            question_text TEXT NOT NULL,
            correct_answer TEXT NOT NULL,
            sort_order INT DEFAULT 0,
            FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "Created table: questions\n";

    // ---- Options table ----
    $pdo->exec("
        CREATE TABLE options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            question_id INT NOT NULL,
            option_text TEXT NOT NULL,
            is_correct TINYINT(1) DEFAULT 0,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "Created table: options\n";

    // ---- Responses table ----
    $pdo->exec("
        CREATE TABLE responses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            survey_id INT NOT NULL,
            session_id VARCHAR(50) NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            score INT DEFAULT 0,
            total_questions INT DEFAULT 0,
            FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "Created table: responses\n";

    // ---- Response Answers table ----
    $pdo->exec("
        CREATE TABLE response_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            response_id INT NOT NULL,
            question_id INT NOT NULL,
            selected_option_id INT DEFAULT NULL,
            is_correct TINYINT(1) DEFAULT 0,
            FOREIGN KEY (response_id) REFERENCES responses(id) ON DELETE CASCADE,
            FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
            FOREIGN KEY (selected_option_id) REFERENCES options(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "Created table: response_answers\n";

    echo "\n========================================\n";
    echo "Database initialized successfully!\n";
    echo "========================================\n";
    echo "Default admin credentials:\n";
    echo "  Username: admin\n";
    echo "  Password: admin123\n";
    echo "========================================\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nMake sure:\n";
    echo "  1. XAMPP MySQL is running\n";
    echo "  2. Database 'survey_app' exists in phpMyAdmin\n";
    echo "  3. MySQL username is 'root' with no password\n";
    exit(1);
}
