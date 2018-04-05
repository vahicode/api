<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Middleware settings
        'jwt' =>[
            "secure" => false, // Don't allow access over an unencrypted connection
            'path' => '/',
            'passthrough' => [
                '/login',
                '/admin/login',
                '/admin/init'
            ],
            'attribute' => 'jwt',
            'secret' => getenv("JWT_SECRET"),
            'lifetime' => "1 month",
            "error" => function ($request, $response, $arguments) {
                echo file_get_contents(dirname(__DIR__).'/templates/index.html');
            }
        ],
        
        // Renderer settings
        'renderer' => [
            'template_path' => dirname(__DIR__) . '/templates/',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => getenv('LOG_FILE'),
            'level' => \Monolog\Logger::DEBUG,
        ],
        
        // Database
        'db' => [
            'type' => 'mariadb',
            'host' => getenv('DB_HOST'),
            'database' => getenv('DB_DB'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
        ],

        // Storage settings
        'storage' => [
            'static_path' => dirname(__DIR__) . '/public/static',
            'temp_path' => '/tmp',
        ],

        // App settings
        'app' => [
            'site' => getenv('SITE'),
            'origin' => getenv('ORIGIN'),
            'static_path' => '/static',
        ],
    ],
];
