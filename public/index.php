<?php
require "../vendor/autoload.php";
define('APP_SECRET_KEY', '! golf PARK LAPTOP tokyo 3 FRUIT SKYPE PARK drip # 2 + BESTBUY % 2');
define('SAVE_HOSTS', ['todo.backend', 'user.backend']);
use Duamel\Auth\Config;
use Duamel\Auth\Controller;
use Klein\Klein;
$builder = new \DI\ContainerBuilder();
$container = $builder->build();
$container->set('redis', new Predis\Client(Config::get('redis', 'production')));
$controller = new Controller($container);

$router = new Klein;
$router->respond('*', function ($request, $response, $service) {
    $response->header('Access-Control-Allow-Origin', '*'); });
$router->post('/login', [$controller, 'login']);
$router->post('/register', [$controller, 'register']);

$router->dispatch();