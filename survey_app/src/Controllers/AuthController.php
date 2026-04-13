<?php

namespace App\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AuthController
{
    private Twig $twig;
    private PDO $pdo;

    public function __construct(Twig $twig, PDO $pdo)
    {
        $this->twig = $twig;
        $this->pdo = $pdo;
    }

    public function showLogin(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Already logged in? Redirect to dashboard
        if (isset($_SESSION['admin_id'])) {
            return $response
                ->withHeader('Location', '/admin/dashboard')
                ->withStatus(302);
        }

        return $this->twig->render($response, 'auth/login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $data = $request->getParsedBody();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        // Validate input
        if (empty($username) || empty($password)) {
            return $this->twig->render($response, 'auth/login.twig', [
                'error' => 'Please enter both username and password.',
                'username' => $username,
            ]);
        }

        // Look up admin
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password'])) {
            return $this->twig->render($response, 'auth/login.twig', [
                'error' => 'Invalid username or password.',
                'username' => $username,
            ]);
        }

        // Set session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        return $response
            ->withHeader('Location', '/admin/dashboard')
            ->withStatus(302);
    }

    public function logout(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_destroy();

        return $response
            ->withHeader('Location', '/login')
            ->withStatus(302);
    }
}
