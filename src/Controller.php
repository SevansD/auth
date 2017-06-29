<?php

namespace Duamel\Auth;

use DI\Container;
use Duamel\Auth\Entity\User;
use Duamel\Auth\Entity\UserManager;
use Klein\Request;
use Klein\Response;

/**
 * Class Controller
 * @package Duamel\Auth
 * @author Duamel Sevans <mail@duamel.ru>
 */
class Controller
{

    private $errors;

    /**
     * @var UserManager
     */
    private $manager;

    public function __construct(Container $container)
    {
        $this->manager = new UserManager($container);
    }

    public function register(Request $request, Response $response)
    {
        /** @var string $userName */
        $userName = $request->param('username');
        /** @var string $password */
        $password = $request->param('password');
        if (empty($userName)) {
            $this->setError('username', 'empty');
            return $this->throwError($response);
        }
        if (empty($password)) {
            $this->setError('password', 'empty');
            return $this->throwError($response);
        }
        $issetUser = $this->manager->searchIdByUserName($userName);
        if ($issetUser) {
            $this->setError('username', 'duplicate');
            return $this->throwError($response);
        }
        $user = (new User())
            ->setUserName($userName)
            ->setPassword(Crypto::passwordHash($password, APP_SECRET_KEY)['hash']);
        $this->manager->save($user);
        return $this->processLogin($user, $request, $response);
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
        $userId = $this->manager->searchIdByUserName($userName);

        if ($userId === FALSE) {
            $this->setError('user', 'Not found');
            return $this->throwError($response);
        }
        $user = $this->manager->loadById($userId);
        if (!Crypto::comparePassword($password, $user->getPassword(), APP_SECRET_KEY)) {
            $this->setError('password', 'Incorrect');
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
    private function processLogin(User $user, $request, $response)
    {
        $SID = $this->manager->generateSID($user, APP_SECRET_KEY);
        if (empty($this->errors)) {
            $response->code(202);
            return json_encode([
                'status' => 'ok',
                'user_id' => $user->getId(),
                'userName=' . $user->getUserName(),
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
        $this->errors[] = $field;
        $this->errors[] = $error;
    }
}
