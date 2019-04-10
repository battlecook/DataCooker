<?php
declare(strict_types=1);

namespace battlecook\Config\Server;

final class Memcache extends Server
{
    public function __construct(string $ip = 'localhost', int $port = 11211)
    {
        parent::__construct($ip, $port);
    }
}