<?php

namespace test\Fixture\PdoDataStore\GetOtherStore;

require __DIR__ . '/../../../../vendor/autoload.php';

class BeforeData
{
    public static function getData()
    {
        return array(
            'Quest' => array(
                array('id' => 1, 'key1' => 1, 'key2' => 2, 'key3' => 1, 'attr' => 'attr'),
                array('id' => 2, 'key1' => 1, 'key2' => 3, 'key3' => 1, 'attr' => 'attr'),
                array('id' => 3, 'key1' => 1, 'key2' => 3, 'key3' => 2, 'attr' => 'attr'),
                array('id' => 4, 'key1' => 1, 'key2' => 1, 'key3' => 1, 'attr' => 'attr'),
            ),
        );
    }
}

?>