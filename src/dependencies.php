<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// database
$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['database'], $db['user'], $db['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

    return $pdo;
};


$container['UserController'] = function ($container) {
    return new \EyeFu\Controllers\UserController($container);
};

$container['AdminController'] = function ($container) {
    return new \EyeFu\Controllers\AdminController($container);
};

$container['User'] = function ($container) {
    return new \EyeFu\Objects\User($container);
};

$container['Admin'] = function ($container) {
    return new \EyeFu\Objects\Admin($container);
};

$container['Picture'] = function ($container) {
    return new \EyeFu\Objects\Picture($container);
};

$container['Eye'] = function ($container) {
    return new \EyeFu\Objects\Eye($container);
};

$container['TokenKit'] = function ($container) {
    return new \EyeFu\Tools\TokenKit($container);
};

