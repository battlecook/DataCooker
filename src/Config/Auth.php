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

    public function getUser()
    {
        return $this->user;
    }

    public function getPassword()
    {
        return $this->password;
    }
}