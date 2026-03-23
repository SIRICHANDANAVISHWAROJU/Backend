<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

// ─── Database Connection ───────────────────────────────────────────────────
$pdo = new PDO(
    'mysql:host=localhost;dbname=slim_crud_db;charset=utf8',
    'root',   // ← your MySQL username (default in XAMPP is root)
    '',       // ← your MySQL password (default in XAMPP is empty)
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// ─── App Setup ────────────────────────────────────────────────────────────
$app = AppFactory::create();
$app->setBasePath('/siri_crud_assign');
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();  // Allows reading JSON body from POST/PUT


// ══════════════════════════════════════════════════════════════════════════
//  CRUD ROUTES FOR USERS
// ══════════════════════════════════════════════════════════════════════════


// ─── 1. GET /users — Get ALL users ────────────────────────────────────────
$app->get('/users', function (Request $request, Response $response) use ($pdo) {

    $stmt  = $pdo->query('SELECT id, username, email, created_at, updated_at FROM users ORDER BY id DESC');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode([
        'success' => true,
        'count'   => count($users),
        'data'    => $users
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});


// ─── 2. GET /users/{id} — Get ONE user ────────────────────────────────────
$app->get('/users/{id}', function (Request $request, Response $response, array $args) use ($pdo) {

    $stmt = $pdo->prepare('SELECT id, username, email, created_at, updated_at FROM users WHERE id = ?');
    $stmt->execute([$args['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'User not found'
        ]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    $response->getBody()->write(json_encode([
        'success' => true,
        'data'    => $user
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});


// ─── 3. POST /users — Create a NEW user ───────────────────────────────────
$app->post('/users', function (Request $request, Response $response) use ($pdo) {

    $body = $request->getParsedBody();

    // Validate required fields
    if (empty($body['username']) || empty($body['email']) || empty($body['password'])) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'username, email, and password are all required'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    // Hash the password — never store plain text
    $hashedPassword = password_hash($body['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
        $stmt->execute([$body['username'], $body['email'], $hashedPassword]);

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User created successfully',
            'id'      => $pdo->lastInsertId()
        ]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        // Catches duplicate username or email (UNIQUE constraint)
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Username or email already exists'
        ]));
        return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
    }
});


// ─── 4. PUT /users/{id} — Update an EXISTING user ─────────────────────────
$app->put('/users/{id}', function (Request $request, Response $response, array $args) use ($pdo) {

    $body = $request->getParsedBody();

    // Check if user exists first
    $check = $pdo->prepare('SELECT id FROM users WHERE id = ?');
    $check->execute([$args['id']]);
    if (!$check->fetch()) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'User not found'
        ]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    // Validate fields
    if (empty($body['username']) || empty($body['email'])) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'username and email are required'
        ]));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        // If password is provided, update it too; otherwise keep the old one
        if (!empty($body['password'])) {
            $hashedPassword = password_hash($body['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, password = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$body['username'], $body['email'], $hashedPassword, $args['id']]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$body['username'], $body['email'], $args['id']]);
        }

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]));
        return $response->withHeader('Content-Type', 'application/json');

    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'Username or email already taken by another user'
        ]));
        return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
    }
});


// ─── 5. DELETE /users/{id} — Delete a user ────────────────────────────────
$app->delete('/users/{id}', function (Request $request, Response $response, array $args) use ($pdo) {

    // Check if user exists first
    $check = $pdo->prepare('SELECT id FROM users WHERE id = ?');
    $check->execute([$args['id']]);
    if (!$check->fetch()) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => 'User not found'
        ]));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$args['id']]);

    $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});


// ─── Run the app ──────────────────────────────────────────────────────────
$app->run();
