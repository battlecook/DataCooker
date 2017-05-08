<?php

namespace test\DataStore;

use battlecook\DataStore\MemcacheDataStore;
use PHPUnit\Framework\TestCase;
use test\Fixture\ExcelDataStore\Monster;

require __DIR__  . '/../../vendor/autoload.php';

class MemcacheDataStoreTest extends TestCase
{
    public function setUp()
    {
        $keyPrefix = 'ProjectName';

        $object = new Monster();
        $object->id = 2;
        $rootIdentifier = $object->getIdentifiers()[0];

        $key = $keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier . ':' . $object->$rootIdentifier;

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

        apcu_store($key, $value);
    }

    public function testGet()
    {
        //given
        $keyPrefix = 'ProjectName';
        $store = new MemcacheDataStore(null, function (){
            $memcache = new \Memcache();
            $memcache->addServer('localhost', 11211);
            return $memcache;
        }, $keyPrefix);

        //when
        $object = new Monster();
        $object->id = 2;
        $object->x = 2;

        $ret = $store->get($object);

        //then
        $this->assertEquals(0, count($ret));
    }
}
