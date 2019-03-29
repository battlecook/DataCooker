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

    //todo consider transaction option
    public function __construct(string $ip = "localhost", int $port = 3306, string $dbName = "", ?Auth $auth = null)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->dbName = $dbName;
        if ($auth !== null) {
            $this->user = $auth->getUser();
            $this->password = $auth->getPassword();
        }
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getDatabaseName(): string
    {
        return $this->dbName;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}