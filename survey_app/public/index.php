<?php
/**
 * Anonymous Survey System
 * Built with Slim Framework 4
 * 
 * Entry point: public/index.php
 */

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Build DI Container
$containerBuilder = new ContainerBuilder();
$containerSetup = require __DIR__ . '/../config/container.php';
$containerSetup($containerBuilder);
$container = $containerBuilder->build();

// Create Slim App with DI
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Twig Middleware
$twig = $container->get(\Slim\Views\Twig::class);
$app->add(\Slim\Views\TwigMiddleware::create($app, $twig));

// Add Error Middleware
$app->addErrorMiddleware(true, true, true);

// Add Body Parsing Middleware (for POST data)
$app->addBodyParsingMiddleware();

// Register Routes
$routes = require __DIR__ . '/../config/routes.php';
$routes($app);

// Run App
$app->run();
