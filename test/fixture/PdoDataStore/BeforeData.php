<?php

namespace test\fixture\PdoDataStore;

require __DIR__ . '/../../../vendor/autoload.php';

class BeforeData
{
    public static function getData()
    {
        return array(
            'Item' => array(
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