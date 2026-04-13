<?php
/**
 * Application Settings
 */

return [
    'settings' => [
        'displayErrorDetails' => true,
        'logErrors' => true,
        'logErrorDetails' => true,

        'db' => [
            'host' => '127.0.0.1',
            'dbname' => 'survey_app',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],

        'twig' => [
            'path' => __DIR__ . '/../templates',
            'cache' => false,
        ],

        'upload' => [
            'path' => __DIR__ . '/../uploads',
        ],

        'base_url' => 'http://localhost:8080',
    ],
];
