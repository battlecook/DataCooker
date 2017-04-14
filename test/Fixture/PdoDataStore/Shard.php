<?php

namespace test\Fixture\PdoDataStore;

use battlecook\DataObject\Model;

require __DIR__  . '/../../../vendor/autoload.php';

class Shard extends Model
{
    /**
     * @dataStoreIdentifier
     * @dataStoreAutoIncrement
     */
    public $localId;
    /**
     * @dataStoreAttribute
     */
    public $channelId;
    /**
     * @dataStoreAttribute
     */
    public $shardId;
    /**
     * @dataStoreAttribute
     */
    public $insertTime;

    public function getShardKey()
    {
        return null;
    }
}