<?php
declare(strict_types=1);

namespace battlecook\Config;

final class Memcache
{
    public $ip;
    public $port;

    public function __construct(string $ip = "localhost", int $port = 11211)
    {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function getPort()
    {
        return $this->port;
    }
}