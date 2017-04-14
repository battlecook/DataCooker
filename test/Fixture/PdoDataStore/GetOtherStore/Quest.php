<?php

namespace test\Fixture\PdoDataStore\GetOtherStore;

use battlecook\DataObject\Model;

require __DIR__  . '/../../../../vendor/autoload.php';

class Quest extends Model
{
    /**
     * @dataStoreIdentifier
     */
    public $key1;
    /**
     * @dataStoreIdentifier
     */
    public $key2;
    /**
     * @dataStoreIdentifier
     */
    public $key3;
    /**
     * @dataStoreAttribute
     * @dataStoreAutoIncrement
     */
    public $id;
    /**
     * @dataStoreAttribute
     */
    public $attr;

    public function getShardKey()
    {
        return $this->key1;
    }
}