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
     *
     * @throws \Exception
     */
    public function save()
    {
        $id = $this->id;
        if (empty($this->id)) {
            $issetUserName = $this->redis->sGetMembers('up:' . $this->userName);
            if (!empty($issetUserName)) {
                throw new \Exception('UserName isn\'t empty');
            }
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
        $this->redis->sadd('up:' . $this->userName, $id);
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
     * @param string $up UserName+Password
     *
     * @return bool
     */
    public function searchByUP($up)
    {
        $users = $this->redis->sGetMembers('up:' . $up);
        return empty($users) ? false : $users[0];
    }
}
