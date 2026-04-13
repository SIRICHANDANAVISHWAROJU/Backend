<?php

namespace App\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use App\Models\SurveyModel;

class SurveyController
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
     * Display the survey questionnaire for participants
     */
    public function showSurvey(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];
        $survey = $this->surveyModel->getSurveyWithQuestionsBySlug($slug);

        // Survey not found
        if (empty($survey)) {
            return $this->twig->render($response->withStatus(404), 'survey/not_found.twig');
        }

        // Survey is inactive
        if (!$survey['is_active']) {
            return $this->twig->render($response, 'survey/inactive.twig', [
                'survey' => $survey,
            ]);
        }

        return $this->twig->render($response, 'survey/questionnaire.twig', [
            'survey' => $survey,
        ]);
    }

    /**
     * Process survey submission
     */
    public function submitSurvey(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];
        $survey = $this->surveyModel->getSurveyBySlug($slug);

        if (!$survey || !$survey['is_active']) {
            return $response
                ->withHeader('Location', '/survey/' . $slug)
                ->withStatus(302);
        }

        $data = $request->getParsedBody();

        // Collect answers: answers[question_id] = option_id
        $answers = [];
        if (isset($data['answers']) && is_array($data['answers'])) {
            foreach ($data['answers'] as $questionId => $optionId) {
                $answers[(int) $questionId] = (int) $optionId;
            }
        }

        if (empty($answers)) {
            return $response
                ->withHeader('Location', '/survey/' . $slug)
                ->withStatus(302);
        }

        // Generate anonymous session ID
        $sessionId = substr(md5(uniqid(mt_rand(), true)), 0, 12);

        // Save the response
        try {
            $responseId = $this->surveyModel->saveResponse($survey['id'], $sessionId, $answers);

            // Get the response details for the thank you page
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['last_response_id'] = $responseId;
            $_SESSION['last_survey_slug'] = $slug;

            return $response
                ->withHeader('Location', '/survey/' . $slug . '/thank-you')
                ->withStatus(302);

        } catch (\Exception $e) {
            return $this->twig->render($response, 'survey/error.twig', [
                'error' => 'Something went wrong. Please try again.',
            ]);
        }
    }

    /**
     * Thank you page after submission
     */
    public function thankYou(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];
        $survey = $this->surveyModel->getSurveyBySlug($slug);

        if (!$survey) {
            return $this->twig->render($response->withStatus(404), 'survey/not_found.twig');
        }

        // Get submission details from session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $responseId = $_SESSION['last_response_id'] ?? null;
        $result = null;

        if ($responseId) {
            // Get the response record
            $stmt = $this->pdo->prepare("SELECT * FROM responses WHERE id = ?");
            $stmt->execute([$responseId]);
            $result = $stmt->fetch();

            // Clear session data
            unset($_SESSION['last_response_id']);
            unset($_SESSION['last_survey_slug']);
        }

        return $this->twig->render($response, 'survey/thank_you.twig', [
            'survey' => $survey,
            'result' => $result,
        ]);
    }
}
