<?php
declare(strict_types=1);

namespace battlecook\Config;

final class Redis
{
    public $ip;
    public $port;
    public $useAuth;
    public $password;

    public function __construct(string $ip = "localhost", int $port = 6379, bool $useAuth = false, string $password = "")
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->useAuth = $useAuth;
        $this->password = $password;
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
}