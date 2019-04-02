<?php

namespace test\Helper;

use battlecook\Config\Auth;
use battlecook\Config\Database;
use battlecook\Config\Memcache;
use battlecook\Config\Redis;

final class Config
{
    private static $ip = "localhost";
    private static $port = 3306;
    private static $dbName = "DataCooker";
    private static $user = "root";
    private static $password = "password";

    public static function getDatabaseConfig()
    {
        return new Database(self::$ip, self::$port, self::$dbName, new Auth(self::$user, self::$password));
    }

    public static $memcachedIP = 'localhost';

    public static function getMemcachedConfig()
    {
        return new Memcache(self::$memcachedIP);
    }

    public static $redisIP = 'localhost';

    public static function getRedisConfig()
    {
        return new Redis("localhost");
    }
}