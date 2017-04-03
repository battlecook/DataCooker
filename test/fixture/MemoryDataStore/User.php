<?php

namespace test\fixture\MemoryDataStore;

use battlecook\DataObject\Model;

require __DIR__  . '/../../../vendor/autoload.php';

class User extends Model
{
    /**
     * @dataStoreIdentifier
     */
    public $userId;
    /**
     * @dataStoreAttribute
     */
    public $userName;

    public function getShardKey()
    {
        return $this->userId;
    }
}