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

    public function testRemove()
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

    public function testGet()
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
}
