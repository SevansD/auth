<?php

require "../vendor/autoload.php";
define('APP_SECRET_KEY', '');
define('SAVE_HOSTS', ['todo.backend']);
use MiladRahimi\PHPRouter\Router;


$router = new Router;
$router->get('/', 'Controller@login');

$router->dispatch();