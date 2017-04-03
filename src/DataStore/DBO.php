<?php

namespace main\php;

class DBO
{
    public static $pdo = null;

    function __construct(Config $config)
    {
        $this->pdoPool = array();

        if(self::$pdo === null)
        {
            $config = $config->getDBConfig();

            $dsn = $config['dsn'];
            $dbUser = $config['user'];
            $dbPassword = $config['password'];
            $options = $config['options'];

            self::$pdo = new \PDO($dsn, $dbUser, $dbPassword, $options);
            self::$pdo->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8mb4");
            self::$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->exec("SET CHARACTER SET utf8mb4");
        }
    }

    public static function reset()
    {
        self::$pdo = null;
    }

    public function getPdo()
    {
        return self::$pdo;
    }
} 