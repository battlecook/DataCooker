<?php

namespace test\Fixture\PdoDataStore\Flush;

require __DIR__ . '/../../../../vendor/autoload.php';

class BeforeData
{
    public static function getData()
    {
        return array(
            'Item' => array(
                array('itemId' => 1, 'userId' => 2, 'itemDesignId' => 2, 'itemName' => 'item2-2'),
                array('itemId' => 2, 'userId' => 3, 'itemDesignId' => 3, 'itemName' => 'item3'),
            ),

            'User' => array(
            ),

            'Shard' => array(
            ),

            'Channel' => array(
            ),
        );
    }
}

?>