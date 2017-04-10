<?php

namespace test\DataStore;

use battlecook\DataStore\PdoDataStore;
use PHPUnit\Framework\TestCase;
use battlecook\DataStore\MemoryDataStore;
use test\fixture\MemoryDataStore\Item;
use test\fixture\MemoryDataStore\User;

require __DIR__  . '/../../vendor/autoload.php';

class CompoundDataStoreTest extends TestCase
{
    public function testGet()
    {
        //given
        $store = new MemoryDataStore(new PdoDataStore(null, function (){
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

    public function testGetSameUser()
    {
        //given
        $store = new MemoryDataStore(new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        }));

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 1;

        $ret = $store->get($object);

        //then
        $this->assertEquals(2, count($ret));
        $this->assertEquals('item1', $ret[0]->itemName);
        $this->assertEquals('item2', $ret[1]->itemName);
    }

    public function testAdd()
    {
        //given
        $store = new MemoryDataStore(new PdoDataStore(null, function (){
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

    public function testSet()
    {
        //given
        $store = new MemoryDataStore(new PdoDataStore(null, function (){
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

    public function testRemove()
    {
        //given
        $store = new MemoryDataStore(new PdoDataStore(null, function (){
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
}
