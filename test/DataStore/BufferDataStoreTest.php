<?php

namespace test\DataStore;

use PHPUnit\Framework\TestCase;
use battlecook\DataStore\BufferDataStore;
use test\Fixture\MemoryDataStore\User;

require __DIR__  . '/../../vendor/autoload.php';

class BufferDataStoreTest extends TestCase
{
    public function testAdd()
    {
        //given
        $object = new User();
        $object->userId = 1;
        $object->userName = 'user';

        $store = new BufferDataStore();

        //when
        $store->add($object);

        //then
        $actual = $store->get($object)[0];
        $this->assertEquals($object, $actual);
    }

    public function testSet()
    {
        //given
        $store = new BufferDataStore();

        $object = new User();
        $object->userId = 1;
        $object->userName = 'user';

        $store->add($object);

        $object->userName = 'user2';

        //when
        $store->set($object);

        //then
        $actual = $store->get($object)[0];
        $this->assertEquals($object, $actual);
    }

    public function testRemove()
    {
        //given
        $store = new BufferDataStore();

        $object = new User();
        $object->userId = 1;
        $object->userName = 'user1';

        $store->add($object);

        $object = new User();
        $object->userId = 2;
        $object->userName = 'user2';

        $store->add($object);

        //when
        $ret = $store->remove($object);

        //then
        $this->assertEquals(1, $ret);

        $object = new User();
        $object->userId = 2;
        $ret = $store->get($object);
        $this->assertEquals(0, count($ret));
    }

    public function testRemoveWithEmptyData()
    {
        //given
        $store = new BufferDataStore();

        $object = new User();
        $object->userId = 1;
        $object->userName = 'user';
        
        //when
        $ret = $store->remove($object);

        //then
        $this->assertEquals(0, $ret);
    }

    public function testGet()
    {
        //given
        $store = new BufferDataStore();

        $object = new User();
        $object->userId = 1;
        $object->userName = 'user1';

        $store->add($object);

        $object = new User();
        $object->userId = 2;
        $object->userName = 'user2';

        $store->add($object);

        //when
        $ret = $store->get($object);

        //then
        $actual = $ret[0];
        $this->assertEquals($object, $actual);
    }
}
