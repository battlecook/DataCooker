<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataAccessor\Buffer;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;
use test\Fixture\DataStorage\ItemAutoIncrementAlone;
use test\Fixture\DataStorage\ItemEmptyIdentifiers;
use test\Fixture\DataStorage\ItemMultiAutoIncrement;

require __DIR__ . '/../../vendor/autoload.php';

class BufferTest extends TestCase
{
    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage auto increment have to has only one
     */
    public function testNoCachedFieldMultiAutoIncrement()
    {
        //given
        $object = new ItemMultiAutoIncrement();
        $storage = new Buffer();

        //when
        $storage->add($object);

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage auto increment have to has integer type
     */
    public function testAutoIncrementNotInteger()
    {
        //given
        $object = new Item();
        $object->attr1 = '1';
        $storage = new Buffer();

        //when
        $storage->add($object);

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage identifiers is empty
     */
    public function testEmptyIdentifiers()
    {
        //given
        $object = new ItemEmptyIdentifiers();
        $storage = new Buffer();

        //when
        $storage->add($object);

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage autoincrement must be included in identifiers or attribute
     */
    public function testAutoIncrementAlone()
    {
        //given
        $object = new ItemAutoIncrementAlone();
        $storage = new Buffer();

        //when
        $storage->add($object);

        //then
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testAdd()
    {
        //given
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;
        $storage = new Buffer();

        //when
        $ret = $storage->add($object);

        //then
        $this->assertEquals($object, $ret);
    }

    public function testSet()
    {
        //given
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;
        $storage = new Buffer();
        $storage->add($object);

        //when
        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 1;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;
        $ret = $storage->set($object2);

        //then
        $this->assertEquals($object2, $ret);
    }
}