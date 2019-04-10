<?php
declare(strict_types=1);

namespace battlecook\Config;

use battlecook\Config\Server\Server;
use battlecook\DataStore\IDataStore;

final class Redis
{
    const DEFAULT_EXPIRE_TIME = 60 * 60 * 7;

    private $store;
    private $ip;
    private $port;
    private $server;
    private $useAuth;
    private $password;

    public function __construct(IDataStore $store = null, \battlecook\Config\Server\Redis $server = null, $expireTime = self::DEFAULT_EXPIRE_TIME)
    {
        $this->store = $store;
        if ($server === null) {
            $this->server = new \battlecook\Config\Server\Redis('localhost', 6379, "");
        } else {
            $this->server = $server;
        }
        $this->expireTime = $expireTime;

        $this->ip = $server->getIP();
        $this->port = $server->getPort();
        if ($server->getPassword() !== "") {
            $this->password = $server->getPassword();
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

    public function getUseAuth(): bool
    {
        return $this->useAuth;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getStore(): ?IDataStore
    {
        return $this->store;
    }
}