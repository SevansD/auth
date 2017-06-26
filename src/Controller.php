<?php

namespace Duamel\Auth;

use DI\Container;
use Duamel\Auth\Models\User;
use MiladRahimi\PHPRouter\Request;

/**
 * Class Controller
 * @package Duamel\Auth
 * @author Duamel Sevans <mail@duamel.ru>
 */
class Controller
{

    private $errors;

    /**
     * @var \DI\Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(Request $request)
    {

    }

    public function login(Request $request)
    {
        setcookie('sid', '');
        $userName = $request->post('username');
        $password = $request->post('password');
        $rememberMe = $request->post('remember');
        if (empty($userName)) {
            $this->setError('username', 'empty');
        }
        if (empty($password)) {
            $this->setError('password', 'empty');
        }
        $user = (new User($this->container))->searchByUP($userName);
        if ($user === false) {
            $this->setError('user', 'Not found');
        }
        
    }

    public function logout(Request $request)
    {
        setcookie('sid', '');
        $user = $request->post('user');
    }

    private function setError($field, $error)
    {
        $this->errors[$field] = $error;
    }
}
