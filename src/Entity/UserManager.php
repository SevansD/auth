<?php

namespace Duamel\Auth\Entity;

use DI\Container;
use Predis\Client;

class UserManager
{

    /**
     * @var Client
     */
    private $redis;

    /**
     * User constructor.
     *
     * @param \DI\Container $container
     */
    public function __construct(Container $container)
    {
        $this->redis = $container->get('redis');
    }

    /**
     * @param User $user
     * @return User $user
     */
    public function save($user)
    {
        $id = $user->getId();
        if (empty($user->getId())) {
            $id = $this->redis->get('userId');
            if (empty($id)) {
                $id = 1;
                $this->redis->set('userId', $id);
            } else {
                $id++;
                $this->redis->set('userId', $id);
            }
            $user->setId($id);
        }
        $this->redis->hmset("user:$id", ['username' => (string)$user->getUserName(), 'password' => (string)$user->getPassword()]);
        $this->redis->hset('userName', $user->getUserName(), $id);
        return $user;
    }

    /**
     * @param int $id
     *
     * @return User
     * @throws \Exception
     */
    public function loadById($id)
    {
        $data = $this->redis->hgetall("user:$id");
        if (empty($data)) {
            throw new \Exception('User with this id not isset');
        }
        return (new User)
            ->setId($id)
            ->setUserName($data['username'])
            ->setPassword($data['password']);
    }

    /**
     * @param string $userName
     *
     * @return bool
     */
    public function searchIdByUserName($userName)
    {
        $users = $this->redis->hget('userName',  $userName);
        return $users ?? false;
    }

    public static function isAuthorized()
    {
        if (!empty($_SESSION["user_id"])) {
            return (bool) $_SESSION["user_id"];
        }
        return FALSE;
    }

    /**
     * @param User $user
     * @param bool $remember
     * @param bool $http_only
     * @param int $days
     */
    public function saveSession($user, $remember = false, $http_only = true, $days = 7)
    {
        $_SESSION["user_id"] = $user->getId();

        if ($remember) {
            $sid = session_id();
            $expire = time() + $days * 24 * 3600;
            $domain = "";
            $secure = false;
            $path = "/";

            setcookie(
                "sid",
                $sid,
                $expire,
                $path,
                $domain,
                $secure,
                $http_only
            );
        }
    }

    /**
     * @param User $user
     * @param string $key
     * @return string
     */
    public function generateSID($user, $key)
    {
        return hash(
            'sha256',
            $user->getId() . $user->getUserName() . $key
        );
    }
}
