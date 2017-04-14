<?php

namespace test\Fixture\PdoDataStore\GetOtherStore;

require __DIR__ . '/../../../../vendor/autoload.php';

class BeforeData
{
    public static function getData()
    {
        return array(
            'Item' => array(
                array('itemId' => 1, 'userId' => 1, 'itemDesignId' => 2, 'itemName' => 'item2'),
                array('itemId' => 2, 'userId' => 1, 'itemDesignId' => 3, 'itemName' => 'item3'),
            ),
        );
    }
}

?>