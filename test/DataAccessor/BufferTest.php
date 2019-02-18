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
    public function setUp()
    {
        $buffer = new Buffer();
        $buffer->initialize();
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage auto increment have to has only one
     */
    public function testNoCachedFieldMultiAutoIncrement()
    {
        //given
        $storage = new Buffer();

        $object = new ItemMultiAutoIncrement();

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
        $storage = new Buffer();

        $object = new Item();
        $object->attr1 = '1';

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
        $storage = new Buffer();

        $object = new ItemEmptyIdentifiers();

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
        $storage = new Buffer();

        $object = new ItemAutoIncrementAlone();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $storage->add($object);

        //then
    }

    public function testGetAll()
    {
        //given
        $storage = new Buffer();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;
        $storage->add($object1);

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;
        $storage->add($object2);

        //when
        $object = new Item();
        $ret = $storage->get($object);

        //then
        $this->assertEquals(2, count($ret));
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testAdd()
    {
        //given
        $storage = new Buffer();

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $ret = $storage->add($object);

        //then
        $this->assertEquals($object, $ret);
    }

    public function testSet()
    {
        //given
        $storage = new Buffer();

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;
        $storage->add($object);

        //when
        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 1;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;
        $storage->set($object2);

        //then
        $ret = $storage->get(new Item());
        $this->assertEquals($object2, $ret[0]);
    }

    public function testRemove()
    {
        //given
        $storage = new Buffer();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;
        $storage->add($object1);

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;
        $storage->add($object2);

        //when
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $storage->remove($object);

        //then
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $ret = $storage->get($object);
        $this->assertEquals($object2, $ret[0]);
    }
}