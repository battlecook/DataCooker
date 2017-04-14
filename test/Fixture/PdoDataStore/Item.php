<?php

namespace test\Fixture\PdoDataStore;

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
    public $itemDesignId;
    /**
     * @dataStoreAttribute
     * @dataStoreAutoIncrement
     */
    public $itemId;
    /**
     * @dataStoreAttribute
     */
    public $itemName;

    public function getShardKey()
    {
        return $this->userId;
    }
}