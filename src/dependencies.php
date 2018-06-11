<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
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
    return new \Vahi\Controllers\UserController($container);
};

$container['AdminController'] = function ($container) {
    return new \Vahi\Controllers\AdminController($container);
};

$container['User'] = function ($container) {
    return new \Vahi\Objects\User($container);
};

$container['Admin'] = function ($container) {
    return new \Vahi\Objects\Admin($container);
};

$container['Picture'] = function ($container) {
    return new \Vahi\Objects\Picture($container);
};

$container['Eye'] = function ($container) {
    return new \Vahi\Objects\Eye($container);
};

$container['TokenKit'] = function ($container) {
    return new \Vahi\Tools\TokenKit($container);
};

