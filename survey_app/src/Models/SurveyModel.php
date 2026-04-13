<?php

namespace App\Models;

use PDO;

class SurveyModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ========== SURVEY CRUD ==========

    public function createSurvey(string $topic, string $slug): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO surveys (topic, slug, is_active) VALUES (?, ?, 1)"
        );
        $stmt->execute([$topic, $slug]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getAllSurveys(): array
    {
        $stmt = $this->pdo->query("
            SELECT s.*, 
                   COUNT(DISTINCT q.id) as question_count,
                   COUNT(DISTINCT r.id) as response_count
            FROM surveys s
            LEFT JOIN questions q ON q.survey_id = s.id
            LEFT JOIN responses r ON r.survey_id = s.id
            GROUP BY s.id
            ORDER BY s.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getSurveyById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM surveys WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getSurveyBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM surveys WHERE slug = ?");
        $stmt->execute([$slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function toggleSurvey(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE surveys SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function deleteSurvey(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM surveys WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function slugExists(string $slug): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM surveys WHERE slug = ?");
        $stmt->execute([$slug]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ========== QUESTIONS ==========

    public function addQuestion(int $surveyId, string $questionText, string $correctAnswer, int $sortOrder): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO questions (survey_id, question_text, correct_answer, sort_order) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$surveyId, $questionText, $correctAnswer, $sortOrder]);
        return (int) $this->pdo->lastInsertId();
    }

    public function addOption(int $questionId, string $optionText, bool $isCorrect): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)"
        );
        $stmt->execute([$questionId, $optionText, $isCorrect ? 1 : 0]);
        return (int) $this->pdo->lastInsertId();
    }

    public function getQuestionsBySurveyId(int $surveyId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM questions WHERE survey_id = ? ORDER BY sort_order ASC"
        );
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll();
    }

    public function getOptionsByQuestionId(int $questionId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM options WHERE question_id = ? ORDER BY RAND()"
        );
        $stmt->execute([$questionId]);
        return $stmt->fetchAll();
    }

    public function getSurveyWithQuestions(int $surveyId): array
    {
        $survey = $this->getSurveyById($surveyId);
        if (!$survey) {
            return [];
        }

        $questions = $this->getQuestionsBySurveyId($surveyId);
        foreach ($questions as &$question) {
            $question['options'] = $this->getOptionsByQuestionId($question['id']);
        }
        $survey['questions'] = $questions;
        return $survey;
    }

    public function getSurveyWithQuestionsBySlug(string $slug): array
    {
        $survey = $this->getSurveyBySlug($slug);
        if (!$survey) {
            return [];
        }

        $questions = $this->getQuestionsBySurveyId($survey['id']);
        foreach ($questions as &$question) {
            $question['options'] = $this->getOptionsByQuestionId($question['id']);
        }
        $survey['questions'] = $questions;
        return $survey;
    }

    // ========== RESPONSES ==========

    public function saveResponse(int $surveyId, string $sessionId, array $answers): int
    {
        $this->pdo->beginTransaction();

        try {
            $score = 0;
            $totalQuestions = count($answers);

            // Calculate score first
            foreach ($answers as $questionId => $optionId) {
                $stmt = $this->pdo->prepare("SELECT is_correct FROM options WHERE id = ?");
                $stmt->execute([$optionId]);
                $option = $stmt->fetch();
                if ($option && $option['is_correct']) {
                    $score++;
                }
            }

            // Insert response record
            $stmt = $this->pdo->prepare(
                "INSERT INTO responses (survey_id, session_id, score, total_questions) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$surveyId, $sessionId, $score, $totalQuestions]);
            $responseId = (int) $this->pdo->lastInsertId();

            // Insert individual answers
            foreach ($answers as $questionId => $optionId) {
                $stmt = $this->pdo->prepare("SELECT is_correct FROM options WHERE id = ?");
                $stmt->execute([$optionId]);
                $option = $stmt->fetch();

                $stmt = $this->pdo->prepare(
                    "INSERT INTO response_answers (response_id, question_id, selected_option_id, is_correct) VALUES (?, ?, ?, ?)"
                );
                $stmt->execute([
                    $responseId,
                    $questionId,
                    $optionId,
                    $option && $option['is_correct'] ? 1 : 0,
                ]);
            }

            $this->pdo->commit();
            return $responseId;

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getResponsesBySurveyId(int $surveyId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM responses WHERE survey_id = ? ORDER BY submitted_at DESC"
        );
        $stmt->execute([$surveyId]);
        return $stmt->fetchAll();
    }

    public function getResponseDetails(int $responseId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT ra.*, q.question_text, o.option_text,
                   (SELECT option_text FROM options WHERE question_id = q.id AND is_correct = 1 LIMIT 1) as correct_answer_text
            FROM response_answers ra
            JOIN questions q ON q.id = ra.question_id
            LEFT JOIN options o ON o.id = ra.selected_option_id
            WHERE ra.response_id = ?
            ORDER BY q.sort_order ASC
        ");
        $stmt->execute([$responseId]);
        return $stmt->fetchAll();
    }

    public function getResultsForExport(int $surveyId): array
    {
        $responses = $this->getResponsesBySurveyId($surveyId);
        $results = [];

        foreach ($responses as $response) {
            $details = $this->getResponseDetails($response['id']);
            $row = [
                'session_id' => $response['session_id'],
                'submitted_at' => $response['submitted_at'],
                'score' => $response['score'],
                'total' => $response['total_questions'],
                'percentage' => $response['total_questions'] > 0
                    ? round(($response['score'] / $response['total_questions']) * 100, 1)
                    : 0,
            ];

            foreach ($details as $i => $detail) {
                $qNum = $i + 1;
                $row["Q{$qNum}"] = $detail['question_text'];
                $row["Q{$qNum}_answer"] = $detail['option_text'] ?? 'No answer';
                $row["Q{$qNum}_correct"] = $detail['is_correct'] ? 'Yes' : 'No';
                $row["Q{$qNum}_correct_answer"] = $detail['correct_answer_text'];
            }

            $results[] = $row;
        }

        return $results;
    }

    // ========== CSV PARSING ==========

    /**
     * Parse a CSV file and create a survey with questions.
     * CSV format: Question,CorrectAnswer,WrongOption1,WrongOption2,...
     */
    public function createSurveyFromCSV(string $topic, string $csvPath): int
    {
        // Generate slug from topic name
        $slug = $this->generateSlug($topic);

        // Create the survey
        $surveyId = $this->createSurvey($topic, $slug);

        // Parse CSV
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open CSV file: $csvPath");
        }

        $rowIndex = 0;
        $isFirstRow = true;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip header row if it looks like one
            if ($isFirstRow) {
                $isFirstRow = false;
                $firstCell = strtolower(trim($row[0] ?? ''));
                if ($firstCell === 'question' || $firstCell === 'questions') {
                    continue;
                }
            }

            // Must have at least 3 columns: Question, CorrectAnswer, WrongOption1
            if (count($row) < 3) {
                continue;
            }

            $questionText = trim($row[0]);
            $correctAnswer = trim($row[1]);

            if (empty($questionText) || empty($correctAnswer)) {
                continue;
            }

            // Create question
            $questionId = $this->addQuestion($surveyId, $questionText, $correctAnswer, $rowIndex);

            // Add correct answer as option
            $this->addOption($questionId, $correctAnswer, true);

            // Add wrong options (columns 2+)
            for ($i = 2; $i < count($row); $i++) {
                $wrongOption = trim($row[$i]);
                if (!empty($wrongOption)) {
                    $this->addOption($questionId, $wrongOption, false);
                }
            }

            $rowIndex++;
        }

        fclose($handle);

        if ($rowIndex === 0) {
            // No questions were parsed, clean up
            $this->deleteSurvey($surveyId);
            throw new \RuntimeException("No valid questions found in CSV file.");
        }

        return $surveyId;
    }

    private function generateSlug(string $topic): string
    {
        $slug = strtolower(trim($topic));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
