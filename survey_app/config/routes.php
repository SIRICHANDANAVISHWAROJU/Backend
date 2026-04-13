<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\SurveyController;
use App\Middleware\AuthMiddleware;

return function (App $app) {

    // ---- Public Routes ----

    // Home page
    $app->get('/', function ($request, $response) {
        $twig = $this->get(\Slim\Views\Twig::class);
        return $twig->render($response, 'home.twig');
    });

    // Auth routes
    $app->get('/login', [AuthController::class, 'showLogin']);
    $app->post('/login', [AuthController::class, 'login']);
    $app->get('/logout', [AuthController::class, 'logout']);

    // Survey public routes (for users/participants)
    $app->get('/survey/{slug}', [SurveyController::class, 'showSurvey']);
    $app->post('/survey/{slug}/submit', [SurveyController::class, 'submitSurvey']);
    $app->get('/survey/{slug}/thank-you', [SurveyController::class, 'thankYou']);

    // ---- Admin Routes (protected) ----
    $app->group('/admin', function (RouteCollectorProxy $group) {

        // Dashboard
        $group->get('', [AdminController::class, 'dashboard']);
        $group->get('/dashboard', [AdminController::class, 'dashboard']);

        // Upload CSV
        $group->get('/upload', [AdminController::class, 'showUpload']);
        $group->post('/upload', [AdminController::class, 'processUpload']);

        // Toggle survey active/inactive
        $group->post('/survey/{id}/toggle', [AdminController::class, 'toggleSurvey']);

        // Delete survey
        $group->post('/survey/{id}/delete', [AdminController::class, 'deleteSurvey']);

        // View results
        $group->get('/survey/{id}/results', [AdminController::class, 'viewResults']);

        // Download results CSV
        $group->get('/survey/{id}/download', [AdminController::class, 'downloadResults']);

    })->add(new AuthMiddleware());
};
