<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

// Admin routes

// Admin login
$app->get('/admin/init', 'AdminController:init');
$app->post('/admin/login', 'AdminController:login');
$app->get('/admin/profile', 'AdminController:getProfile');

// Admin create
$app->post('/admin/add/admin', 'AdminController:addAdmin');

// Admin list
$app->get('/admin/list/admins', 'AdminController:getAdminList');

// Preflight requests 
$app->options('/[{path:.*}]', function($request, $response, $path = null) {
    $settings = require __DIR__ . '/../src/settings.php';
    return $response
        ->withHeader('Access-Control-Allow-Origin', $settings['settings']['app']['origin'])
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// Catch-all route
$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});
