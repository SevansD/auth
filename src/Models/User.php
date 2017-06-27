<?php

namespace Duamel\Auth\Models;

use DI\Container;
use Predis\Client;

class User
{
    private $id;
    private $userName;
    private $password;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     *
     * @return User
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $id = $this->id;
        if (empty($this->id)) {
            $id = $this->redis->get('userId');
            if (empty($id)) {
                $id = 0;
                $this->redis->set('userId', $id);
            } else {
                $id++;
                $this->redis->set('userId', $id);
            }
            $this->id = $id;
        }
        $this->redis->hmset("user:$id", ['username' => $this->userName, 'password' => $this->password]);
        $this->redis->sadd('up:' . $this->userName . $this->password, $id);
        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     * @throws \Exception
     */
    public function loadById($id)
    {
        $data = $this->redis->hgetall("user:$id");
        if (empty($data)) {
            throw new \Exception('User with this id not isset');
        }
        $this->setId($id)
            ->setUserName($data['username'])
            ->setPassword($data['password']);
        return $this;
    }

    /**
     * @param string $userName
     * @param string $password
     *
     * @return bool
     */
    public function searchByUP($userName, $password)
    {
        $hashes = $this->passwordHash($password);
        $users = $this->redis->sGetMembers('up:' . $userName . $hashes['hash']);
        return empty($users) ? false : $users[0];
    }



    public static function isAuthorized()
    {
        if (!empty($_SESSION["user_id"])) {
            return (bool) $_SESSION["user_id"];
        }
        return FALSE;
    }

    public function saveSession($remember = false, $http_only = true, $days = 7)
    {
        $_SESSION["user_id"] = $this->id;

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

    public function passwordHash($password, $salt = null, $iterations = 10)
    {
        $salt || $salt = uniqid();
        $hash = md5(md5($password . md5(sha1($salt))));

        for ($i = 0; $i < $iterations; ++$i) {
            $hash = md5(md5(sha1($hash)));
        }

        return array('hash' => $hash, 'salt' => $salt);
    }

    public function generateSID($key)
    {
        return hash(
            'sha256',
            $this->id . $this->userName,
            $key
        );
    }
}
