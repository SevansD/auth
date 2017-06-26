<?php

namespace Duamel\Auth\Test\Model;

use DI\ContainerBuilder;
use Duamel\Auth\Config;
use Duamel\Auth\Models\User;
use Predis\Client;

class UserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \DI\Container
     */
    private $container;

    public function setUp()
    {
        $this->container = ContainerBuilder::buildDevContainer();
        $this->container->set('redis', new Client(Config::get('redis', 'test')));
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
        $user->save();
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
        $user = (new User($this->container))
            ->setId($id)
            ->setPassword($pass)
            ->setUserName($name);
        $user->save();
        $testUser = new User($this->container);
        $testUser->loadById($user->getId());
        $this->assertEquals($user, $testUser);
    }

    public function loadByIdProvider()
    {
        return [
            [1, 'pass', 'name'],
        ];
    }

    public function testLoadByIdException()
    {
        $this->expectException(\Exception::class);
        (new User($this->container))->loadById(100000);
    }

}
