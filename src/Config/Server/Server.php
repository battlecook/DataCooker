<?php
declare(strict_types=1);

namespace battlecook\Config\Server;

abstract class Server
{
    protected $ip;
    protected $port;

    protected function __construct(string $ip, int $port)
    {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function getIP(): string
    {
        return $this->ip;
    }

    public function getPort(): int
    {
        return $this->port;
    }
}