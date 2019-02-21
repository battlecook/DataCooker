<?php

namespace test\Fixture\PdoDataStore;

require __DIR__ . '/../../../vendor/autoload.php';

class AfterData
{
    public static function getData()
    {
        return array(
            'Item' => array(
                array('itemId' => 1, 'userId' => 2, 'itemDesignId' => 2, 'itemName' => 'item2-2'),
                array('itemId' => 2, 'userId' => 3, 'itemDesignId' => 3, 'itemName' => 'item3'),
            ),

        );
    }
}