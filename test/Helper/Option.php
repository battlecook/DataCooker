<?php

namespace test\Helper;

final class Option
{
    public static $dbIP = 'localhost';

    public static function getDatabaseOption()
    {
        return ['store' => null,
                'hosts' => [['ip' => self::$dbIP, 'port' => 3306, 'dbname' => 'DataCooker', 'user' => 'root', 'password' => 'password']]];
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