<?php

namespace Duamel\Auth;

/**
 * Class Config
 * @package Duamel\Auth
 */
class Config
{

    /**
     * @var
     */
    private static $data;

    /**
     * @param $service
     * @param $level
     *
     * @return mixed
     * @throws \Exception
     */
    public static function get($service, $level)
    {
        if (empty(self::readFile($level)[$service])) {
            throw new \Exception('Empty config for service '. $service);
        }
        return self::readFile($level)[$service];
    }

    /**
     * @param $level
     *
     * @return mixed
     */
    private static function readFile($level)
    {
        if (empty(self::$data[$level])) {
            self::$data[$level] = parse_ini_file(
                __DIR__ . '/../config/' . $level . '.ini',
                TRUE
            );
        }
        return self::$data[$level];
    }
}
