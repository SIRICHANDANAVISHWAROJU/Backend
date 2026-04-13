<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Slim\Flash\Messages;

return function (ContainerBuilder $containerBuilder) {
    $settings = require __DIR__ . '/settings.php';

    $containerBuilder->addDefinitions([
        'settings' => $settings['settings'],

        // PDO Database connection (MySQL)
        PDO::class => function (ContainerInterface $c) {
            $db = $c->get('settings')['db'];
            $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
            $pdo = new PDO($dsn, $db['username'], $db['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        },

        // Twig View
        Twig::class => function (ContainerInterface $c) {
            $twigSettings = $c->get('settings')['twig'];
            $twig = Twig::create($twigSettings['path'], [
                'cache' => $twigSettings['cache'],
                'auto_reload' => true,
            ]);
            $twig->getEnvironment()->addGlobal('base_url', $c->get('settings')['base_url']);
            return $twig;
        },

        // Flash Messages
        Messages::class => function () {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            return new Messages();
        },
    ]);
};
