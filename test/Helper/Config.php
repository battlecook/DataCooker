<?php

namespace test\Helper;

use battlecook\Config\Auth;
use battlecook\Config\Database;
use battlecook\Config\Memcache;
use battlecook\Config\Redis;

final class Config
{
    public static $ip = "localhost";
    public static $port = 3306;
    public static $dbName = "DataCooker";
    public static $user = "root";
    public static $password = "password";

    public static function getDatabaseConfig()
    {
        return new Database(self::$ip, self::$port, self::$dbName, new Auth(self::$user, self::$password));
    }

    public static function getPdo()
    {
        $ip = Config::$ip;
        $port = Config::$port;
        $dbName = Config::$dbName;
        $user = Config::$user;
        $password = Config::$password;

        $dsn = "mysql:host={$ip};port={$port};dbname={$dbName}";

        return new \PDO($dsn, $user, $password, array());
    }

    public static $memcachedIP = 'localhost';

    public static function getMemcachedConfig()
    {
        return new Memcache(self::$memcachedIP);
    }

    public static $redisIP = 'localhost';

    public static function getRedisConfig()
    {
        return new Redis(self::$redisIP);
    }
}