<?php
declare(strict_types=1);

namespace battlecook\Config;

final class Auth
{
    private $user;
    private $password;

    public function __construct(string $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
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