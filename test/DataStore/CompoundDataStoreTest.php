<?php

namespace test\DataStore;

use battlecook\DataStore\ApcuDataStore;
use battlecook\DataStore\BufferDataStore;
use battlecook\DataStore\ExcelDataStore;
use battlecook\DataStore\PdoDataStore;
use PHPUnit\Framework\TestCase;
use test\Fixture\ExcelDataStore\Monster;
use test\Fixture\MemoryDataStore\Item;
use test\Fixture\MemoryDataStore\User;

require __DIR__  . '/../../vendor/autoload.php';

class CompoundDataStoreTest extends TestCase
{
    public function testGetStorageDataCombination()
    {
        //given
        $store = new BufferDataStore(new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        }));

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'user1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'user2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemDesignId = 3;
        $object->itemName = 'user3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;

        $ret = $store->get($object);

        //then
        $this->assertEquals(1, count($ret));
        $this->assertEquals('user2', $ret[0]->itemName);
    }

    public function testAddStorageDataCombination()
    {
        //given
        $store = new BufferDataStore(new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        }));

        $object = new User();
        $object->userId = 1;
        $object->userName = 'user';

        //when
        $store->add($object);

        //then
        $actual = $store->get($object)[0];
        $this->assertEquals($object, $actual);
    }

    public function testSetStorageDataCombination()
    {
        //given
        $store = new BufferDataStore(new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        }));

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'user1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'user2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemDesignId = 3;
        $object->itemName = 'user3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'user2-2';

        $ret = $store->set($object);

        //then
        $this->assertEquals(1, $ret);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $ret = $store->get($object);
        $this->assertEquals('user2-2', $ret[0]->itemName);
    }

    public function testRemoveStorageDataCombination()
    {
        //given
        $store = new BufferDataStore(new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        }));

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'user1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'user2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemDesignId = 3;
        $object->itemName = 'user3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;

        $ret = $store->remove($object);

        //then
        $this->assertEquals(1, $ret);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $ret = $store->get($object);
        $this->assertEquals(array(), $ret);
    }

    public function testGetDesignDataCombination()
    {
        //given
        $keyPrefix = 'ProjectName';
        $path = __DIR__ . '/../Fixture/ExcelDataStore/monster.xlsx';
        $store = new BufferDataStore(new ApcuDataStore(new ExcelDataStore(null, $path), $keyPrefix));

        //when
        $object = new Monster();
        $object->id = 2;

        $ret = $store->get($object);

        //then
        $this->assertEquals(1, count($ret));
    }
}
