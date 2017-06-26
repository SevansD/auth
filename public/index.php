<?php

require "../vendor/autoload.php";

use MiladRahimi\PHPRouter\Router;


$router = new Router;
$router->get('/', 'Controller@method');

$router->dispatch();