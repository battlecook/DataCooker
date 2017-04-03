<?php

namespace test\fixture\MemoryDataStore;

use battlecook\DataObject\Model;

require __DIR__  . '/../../../vendor/autoload.php';

class Item extends Model
{
    /**
     * @dataStoreIdentifier
     */
    public $userId;
    /**
     * @dataStoreIdentifier
     */
    public $itemId;
    /**
     * @dataStoreAttribute
     * @dataStoreAutoIncrement
     */
    public $seq;
    /**
     * @dataStoreAttribute
     */
    public $itemName;

    public function getShardKey()
    {
        return $this->userId;
    }
}