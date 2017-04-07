<?php

namespace test\fixture\PdoDataStore;

require __DIR__ . '/../../../vendor/autoload.php';

class AfterData
{
    public static function getData()
    {
        return array(
            'Item' => array(
                array('seq' => 1, 'userId' => 2, 'itemId' => 2, 'itemName' => 'item2-2'),
                array('seq' => 2, 'userId' => 3, 'itemId' => 3, 'itemName' => 'item3'),
            ),

        );
    }
}

?>