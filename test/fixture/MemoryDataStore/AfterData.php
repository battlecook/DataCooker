<?php

namespace test\fixture\MemoryDataStore;

require __DIR__  . '/../../../vendor/autoload.php';

class AfterData
{
    public static function getData()
    {
        return array(
            'Item' => array(
                array('seq' => 1, 'userId' => 1, 'itemId' => 1, 'itemName' => 'item1'),
                array('seq' => 2, 'userId' => 2, 'itemId' => 2, 'itemName' => 'item2-2'),
                array('seq' => 3, 'userId' => 3, 'itemId' => 3, 'itemName' => 'item3'),
            ),

        );
    }
}

?>