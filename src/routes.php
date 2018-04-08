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
$app->post('/admin/admin', 'AdminController:addAdmin');
$app->post('/admin/users', 'AdminController:addUsers');
$app->post('/admin/pictures', 'AdminController:addPictures');
$app->post('/admin/eyes/bundle', 'AdminController:eyeFromPictureBundle');
$app->post('/admin/eyes', 'AdminController:eyesFromOrphanPictures');

// Admin list
$app->get('/admin/admins', 'AdminController:getAdminList');
$app->get('/admin/users', 'AdminController:getUserList');
$app->get('/admin/admin/{id}', 'AdminController:loadAdmin');
$app->get('/admin/user/{id}', 'AdminController:loadUser');
$app->get('/admin/pictures/orphans', 'AdminController:getOrphanPicturesList');
$app->get('/admin/eyes', 'AdminController:getEyeList');
$app->get('/admin/eye/{id}', 'AdminController:loadEye');

// Admin edit
$app->post('/admin/admin/{id}', 'AdminController:updateAdmin');
$app->post('/admin/user/{id}', 'AdminController:updateUser');

// Admin delete
$app->delete('/admin/admin/{id}', 'AdminController:removeAdmin');
$app->delete('/admin/user/{id}', 'AdminController:removeUser');

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
