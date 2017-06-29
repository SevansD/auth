<?php

namespace Duamel\Auth\Test\Model;

use DI\ContainerBuilder;
use Duamel\Auth\Config;
use Duamel\Auth\Entity\UserManager;
use Duamel\Auth\Entity\User;
use Predis\Client;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * @var UserManager
     */
    private $manager;

    public function setUp()
    {
        $this->container = ContainerBuilder::buildDevContainer();
        $this->container->set('redis', new Client(Config::get('redis', 'test')));
        $this->manager = new UserManager($this->container);
    }

    /**
     * @param $id
     * @param $username
     * @param $password
     *
     * @dataProvider saveProvider
     */
    public function testSave($id, $username, $password)
    {
        $user = (new User($this->container))
            ->setId($id)
            ->setUserName($username)
            ->setPassword($password);
        $this->manager->save($user);
        $this->assertNotNull($user->getId());
    }

    public function saveProvider()
    {
        return [
            [NULL, 'test', 'test'],
            [1, 'test2', 'test2']
        ];
    }

    /**
     * @param int $id
     * @param string $pass
     * @param string $name
     *
     * @dataProvider loadByIdProvider
     */
    public function testLoadById($id, $pass, $name)
    {
        $user = (new User())
            ->setId($id)
            ->setPassword($pass)
            ->setUserName($name);
        $this->manager->save($user);
        $testUser = $this->manager->loadById($user->getId());
        $this->assertEquals($user, $testUser);
    }
}
