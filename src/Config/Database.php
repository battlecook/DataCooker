<?php
declare(strict_types=1);

namespace battlecook\Config;

use battlecook\DataStore\IDataStore;

final class Database
{
    private $store;
    private $ip;
    private $port;
    private $dbName;
    private $user;
    private $password;

    public function __construct(IDataStore $store = null, \battlecook\Config\Server\Database $server = null)
    {
        $this->store = $store;

        if($server === null) {
            $server = new \battlecook\Config\Server\Database();
        }

        $this->ip = $server->getIP();
        $this->port = $server->getPort();
        $this->dbName = $server->getDbName();
        $this->user = $server->getAuth()->getUser();
        $this->password = $server->getAuth()->getPassword();
    }

    public function getStore(): ?IDataStore
    {
        return $this->store;
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