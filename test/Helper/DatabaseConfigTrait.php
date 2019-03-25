<?php

namespace test\Helper;

use battlecook\Config\Auth;
use battlecook\Config\Database;

Trait DatabaseConfigTrait
{
    private $ip = "mariadb";
    private $port = 3306;
    private $dbName = "DataCooker";
    private $user = "root";
    private $password = "password";

    private function getConfig()
    {
        return new Database($this->ip, $this->port, $this->dbName, new Auth($this->user, $this->password));
    }
}