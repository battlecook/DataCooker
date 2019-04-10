<?php
declare(strict_types=1);

namespace battlecook\Config\Server;

use battlecook\Config\Auth;

final class Database extends Server
{
    private $dbName;
    private $auth;

    public function __construct(string $ip = 'localhost', int $port = 3306, string $dbName = "", Auth $auth = null)
    {
        parent::__construct($ip, $port);

        $this->dbName = $dbName;
        $this->auth = $auth;
    }

    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }
}