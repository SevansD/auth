<?php

namespace Duamel\Auth;

use DI\Container;
use Duamel\Auth\Models\User;
use Klein\Request;
use Klein\Response;
use Klein\ServiceProvider;

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

    public function register(Request $request, Response $response)
    {
        /** @var string $userName */
        $userName = $request->param('username');
        /** @var string $password */
        $password = $request->param('password');
        if (empty($userName)) {
            $this->setError('username', 'empty');
        }
        if (empty($password)) {
            $this->setError('password', 'empty');
        }
        $user = new User($this->container);
        $issetUser = $user->searchByUP($userName, $password);
        if ($issetUser) {
            $this->setError('username', 'duplicate');
        }
        $user->setUserName($userName)->setPassword($password);
        $user->save();
        $this->processLogin($user, $request, $response);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function login(Request $request, Response $response)
    {
        $params = $request->paramsPost();
        /** @var string $userName */
        $userName = $params['username'];
        /** @var string $password */
        $password = $params['password'];
        if (empty($userName)) {
            $this->setError('username', 'empty');
            return $this->throwError($response);
        }
        if (empty($password)) {
            $this->setError('password', 'empty');
            return $this->throwError($response);
        }
        /** @var User $user */
        $user = (new User($this->container))->searchByUP($userName, $password);

        if ($user === FALSE) {
            $this->setError('user', 'Not found');
            return $this->throwError($response);
        }
        return $this->processLogin($user, $request, $response);
    }

    /**
     * @param User $user
     * @param Request $request
     * @param Response $response
     *
     * @return string
     */
    private function processLogin($user, $request, $response)
    {
        $SID = $user->generateSID(APP_SECRET_KEY);
        if (empty($this->errors)) {
            $response->code(202);
            return
                json_encode([
                'status' => 'ok',
                'user_id' => $user->getId(),
                'userName=' . urlencode($user->getUserName()),
                'sid' => $SID]);
        }
        return $this->throwError($response);
    }

    /**
     * @param Response $response
     *
     * @return string
     */
    private function throwError($response)
    {
        $response->code(403);
        return json_encode(['status' => 'error', 'errors' => $this->errors]);
    }

    public function logout(Request $request)
    {
        setcookie('sid', '');
        $user = $request->param('user');
    }


    private function setError($field, $error)
    {
        $this->errors[$field] = $error;
    }
}
