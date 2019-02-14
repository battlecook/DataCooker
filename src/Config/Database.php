<?php
declare(strict_types=1);

namespace battlecook\Config;

final class Database
{
    private $ip;
    private $port;
    private $dbName;
    private $user;
    private $password;

    public function __construct(string $ip, int $port, string $dbName = "", ?Auth $auth = null)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->dbName = $dbName;
        if($auth !== null)
        {
            $this->user = $auth->getUser();
            $this->password = $auth->getPassword();
        }
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getDatabaseName()
    {
        return $this->dbName;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPassword()
    {
        return $this->password;
    }
}