<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true
]);

session_start();

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

require __DIR__ . '/../src/settings.php';
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/routes.php';

$app->run();
