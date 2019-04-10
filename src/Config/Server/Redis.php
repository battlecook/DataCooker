<?php
declare(strict_types=1);

namespace battlecook\Config\Server;

final class Redis extends Server
{
    private $password;

    public function __construct(string $ip = 'localhost', int $port = 6379, string $password = "")
    {
        parent::__construct($ip, $port);
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}