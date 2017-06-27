<?php

namespace Duamel\Auth;

use DI\Container;
use Duamel\Auth\Models\User;
use MiladRahimi\PHPRouter\Request;
use MiladRahimi\PHPRouter\Response;

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

    public function login(Request $request, Response $response)
    {
        setcookie('sid', '');
        /** @var string $userName */
        $userName = $request->post('username');
        /** @var string $password */
        $password = $request->post('password');
        $rememberMe = $request->post('remember');
        if (empty($userName)) {
            $this->setError('username', 'empty');
        }
        if (empty($password)) {
            $this->setError('password', 'empty');
        }
        /** @var User $user */
        $user = (new User($this->container))->searchByUP($userName, $password);
        if ($user === FALSE) {
            $this->setError('user', 'Not found');
        }
        $SID = $user->generateSID(APP_SECRET_KEY);
        $source = parse_url($request->getReferer());
        if (in_array($source->host, SAVE_HOSTS)) {
            header('Location: http://' . $source->host . $source->path . '?' .
                'user_id=' . $user->getId() .
                '&userName=' . urlencode($user->getUserName()) .
                '&sid=' . $SID .
                '&rememberMe=' . $rememberMe);
        } else {
            $response->redirect($source);
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
