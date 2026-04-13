<?php

namespace App\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Models\SurveyModel;

class AdminController
{
    private Twig $twig;
    private PDO $pdo;
    private SurveyModel $surveyModel;

    public function __construct(Twig $twig, PDO $pdo)
    {
        $this->twig = $twig;
        $this->pdo = $pdo;
        $this->surveyModel = new SurveyModel($pdo);
    }

    /**
     * Admin Dashboard - lists all surveys
     */
    public function dashboard(Request $request, Response $response): Response
    {
        $surveys = $this->surveyModel->getAllSurveys();

        $queryParams = $request->getQueryParams();

        return $this->twig->render($response, 'admin/dashboard.twig', [
            'surveys' => $surveys,
            'admin' => $_SESSION['admin_username'] ?? 'Admin',
            'success' => $queryParams['success'] ?? null,
            'errorMsg' => $queryParams['error'] ?? null,
        ]);
    }

    /**
     * Show CSV upload form
     */
    public function showUpload(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'admin/upload.twig', [
            'admin' => $_SESSION['admin_username'] ?? 'Admin',
        ]);
    }

    /**
     * Process CSV upload and create survey
     */
    public function processUpload(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $topic = trim($data['topic'] ?? '');
        $uploadedFiles = $request->getUploadedFiles();

        // Validate topic
        if (empty($topic)) {
            return $this->twig->render($response, 'admin/upload.twig', [
                'error' => 'Please enter a topic name.',
                'admin' => $_SESSION['admin_username'] ?? 'Admin',
            ]);
        }

        // Validate file
        if (!isset($uploadedFiles['csv_file']) || $uploadedFiles['csv_file']->getError() !== UPLOAD_ERR_OK) {
            return $this->twig->render($response, 'admin/upload.twig', [
                'error' => 'Please upload a valid CSV file.',
                'topic' => $topic,
                'admin' => $_SESSION['admin_username'] ?? 'Admin',
            ]);
        }

        $csvFile = $uploadedFiles['csv_file'];

        // Check file extension
        $filename = $csvFile->getClientFilename();
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            return $this->twig->render($response, 'admin/upload.twig', [
                'error' => 'Only CSV files are accepted.',
                'topic' => $topic,
                'admin' => $_SESSION['admin_username'] ?? 'Admin',
            ]);
        }

        // Save uploaded file temporarily
        $uploadDir = __DIR__ . '/../../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $tmpPath = $uploadDir . uniqid('csv_') . '.csv';
        $csvFile->moveTo($tmpPath);

        try {
            // Parse CSV and create survey
            $surveyId = $this->surveyModel->createSurveyFromCSV($topic, $tmpPath);

            // Clean up temp file
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }

            // Redirect to dashboard with success
            return $response
                ->withHeader('Location', '/admin/dashboard?success=Survey+created+successfully')
                ->withStatus(302);

        } catch (\Exception $e) {
            // Clean up temp file on error
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }

            return $this->twig->render($response, 'admin/upload.twig', [
                'error' => 'Error processing CSV: ' . $e->getMessage(),
                'topic' => $topic,
                'admin' => $_SESSION['admin_username'] ?? 'Admin',
            ]);
        }
    }

    /**
     * Toggle survey active/inactive
     */
    public function toggleSurvey(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $this->surveyModel->toggleSurvey($id);

        return $response
            ->withHeader('Location', '/admin/dashboard')
            ->withStatus(302);
    }

    /**
     * Delete a survey
     */
    public function deleteSurvey(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $this->surveyModel->deleteSurvey($id);

        return $response
            ->withHeader('Location', '/admin/dashboard?success=Survey+deleted')
            ->withStatus(302);
    }

    /**
     * View survey results
     */
    public function viewResults(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $survey = $this->surveyModel->getSurveyWithQuestions($id);

        if (empty($survey)) {
            return $response
                ->withHeader('Location', '/admin/dashboard?error=Survey+not+found')
                ->withStatus(302);
        }

        $responses = $this->surveyModel->getResponsesBySurveyId($id);

        // Get detailed answers for each response
        foreach ($responses as &$resp) {
            $resp['details'] = $this->surveyModel->getResponseDetails($resp['id']);
        }

        // Calculate statistics
        $stats = [
            'total_responses' => count($responses),
            'avg_score' => 0,
            'highest_score' => 0,
            'lowest_score' => 0,
        ];

        if (count($responses) > 0) {
            $scores = array_column($responses, 'score');
            $totals = array_column($responses, 'total_questions');
            $percentages = [];
            foreach ($responses as $r) {
                if ($r['total_questions'] > 0) {
                    $percentages[] = round(($r['score'] / $r['total_questions']) * 100, 1);
                }
            }
            $stats['avg_score'] = count($percentages) > 0 ? round(array_sum($percentages) / count($percentages), 1) : 0;
            $stats['highest_score'] = count($percentages) > 0 ? max($percentages) : 0;
            $stats['lowest_score'] = count($percentages) > 0 ? min($percentages) : 0;
        }

        return $this->twig->render($response, 'admin/results.twig', [
            'survey' => $survey,
            'responses' => $responses,
            'stats' => $stats,
            'admin' => $_SESSION['admin_username'] ?? 'Admin',
        ]);
    }

    /**
     * Download results as CSV
     */
    public function downloadResults(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $survey = $this->surveyModel->getSurveyById($id);

        if (!$survey) {
            return $response
                ->withHeader('Location', '/admin/dashboard?error=Survey+not+found')
                ->withStatus(302);
        }

        $results = $this->surveyModel->getResultsForExport($id);

        // Build CSV content
        $output = fopen('php://temp', 'r+');

        if (!empty($results)) {
            // Header row
            fputcsv($output, array_keys($results[0]));
            // Data rows
            foreach ($results as $row) {
                fputcsv($output, $row);
            }
        } else {
            fputcsv($output, ['No responses yet']);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        $filename = 'results_' . $survey['slug'] . '_' . date('Y-m-d') . '.csv';

        $response->getBody()->write($csvContent);
        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
