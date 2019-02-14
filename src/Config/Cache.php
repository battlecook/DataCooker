<?php
declare(strict_types=1);

namespace battlecook\Config;

final class Cache
{
    protected $ip;
    protected $port;

    public function __construct(string $ip, int $port)
    {
        $this->ip = $ip;
        $this->port = $port;
    }
}