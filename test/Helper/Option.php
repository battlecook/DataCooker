<?php

namespace test\Helper;

final class Option
{
    public static $dbIP = 'localhost';
    public static $dbPort = 3306;
    public static $dbName = "DataCooker";
    public static $user = "root";
    public static $password = "password";

    public static function getDatabaseOption()
    {
        return ['store' => null,
                'hosts' => [['ip' => self::$dbIP, 'port' => self::$dbPort, 'dbname' => self::$dbName, 'user' => self::$user, 'password' => self::$password]]];
    }

    public static $memcachedIP = 'localhost';

    public static function getMemcachedOption()
    {
        return ['store' => null,
                'hosts' => [['ip' => self::$memcachedIP, 'port' => 11211]]];
    }

    public static $redisIP = 'localhost';

    public static function getRedisConfig()
    {
        return ['store' => null,
                'hosts' => [['ip' => self::$redisIP, 'port' => 6379]]];
    }
}