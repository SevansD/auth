<?php

namespace Duamel\Auth;

class Crypto
{
    public static function passwordHash($password, $salt = null, $iterations = 10)
    {
        $salt || $salt = uniqid();
        $hash = md5(md5($password . md5(sha1($salt))));

        for ($i = 0; $i < $iterations; ++$i) {
            $hash = md5(md5(sha1($hash)));
        }

        return array('hash' => $hash, 'salt' => $salt);
    }

    public static function comparePassword($password, $passwordHash, $salt = null, $iterations = 10)
    {
        return self::passwordHash($password, $salt, $iterations) == $passwordHash;
    }
}