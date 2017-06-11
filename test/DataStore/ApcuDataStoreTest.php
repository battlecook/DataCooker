<?php

namespace test\DataStore;

use battlecook\DataStore\ApcuDataStore;
use PHPUnit\Framework\TestCase;
use test\Fixture\ExcelDataStore\Monster;

require __DIR__  . '/../../vendor/autoload.php';

class ApcuDataStoreTest extends TestCase
{
    public function setUp()
    {
        if(ini_get('apc.enable_cli') !== '1')
        {
            if(ini_set ('apc.enable_cli', '1' ) === false)
            {
                print_r("you have to set apc.enable_cli is 1 in php.ini file");
                return 1;
            }
        }

        $keyPrefix = 'ProjectName';

        $object = new Monster();
        $object->id = 2;
        $rootIdentifier = $object->getIdentifiers()[0];

        $key = $keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier;

        $value = array();

        $object = new Monster();
        $object->id = 1;
        $object->x = 3;
        $object->y = 4;

        $value[] = $object;

        $object = new Monster();
        $object->id = 2;
        $object->x = 6;
        $object->y = 5;

        $value[] = $object;

        $ret = apcu_store($key, $value);
        if($ret === false)
        {
            print_r("ApcuDataStore Setup Failed");
        }
    }

    public function testGet()
    {
        //given
        $keyPrefix = 'ProjectName';
        $store = new ApcuDataStore(null, $keyPrefix);

        //when
        $object = new Monster();
        $object->id = 2;

        $ret = $store->get($object);

        //then
        $this->assertEquals(1, count($ret));
    }
}
