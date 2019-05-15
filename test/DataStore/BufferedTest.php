<?php
declare(strict_types=1);

namespace test\DataStore;

use battlecook\DataStore\AbstractStore;
use battlecook\DataStore\Buffered;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStore\Item;
use test\Fixture\DataStore\ItemAutoIncrementAlone;
use test\Fixture\DataStore\ItemEmptyIdentifiers;
use test\Fixture\DataStore\ItemMultiAutoIncrement;

require __DIR__ . '/../../vendor/autoload.php';

class BufferedTest extends TestCase
{
    public function setUp()
    {
        AbstractStore::initializeCachedMeta();

        $buffered = new Buffered();
        $buffered->convertAll();
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage auto increment have to has only one
     */
    public function testNoCachedFieldMultiAutoIncrement()
    {
        //given
        $store = new Buffered();

        $object = new ItemMultiAutoIncrement();

        //when
        $store->add($object);

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage auto increment have to has integer type
     */
    public function testAutoIncrementNotInteger()
    {
        //given
        $store = new Buffered();

        $object = new Item();
        $object->id1 = '1';

        //when
        $store->add($object);

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage identifiers is empty
     */
    public function testEmptyIdentifiers()
    {
        //given
        $store = new Buffered();

        $object = new ItemEmptyIdentifiers();

        //when
        $store->add($object);

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage autoincrement must be included in identifiers or attribute
     */
    public function testAutoIncrementAlone()
    {
        //given
        $store = new Buffered();

        $object = new ItemAutoIncrementAlone();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $store->add($object);

        //then
    }

    public function testSearchAll()
    {
        //given
        $store = new Buffered();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;
        $store->add($object1);

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;
        $store->add($object2);

        //when
        $object = new Item();
        $ret = $store->search($object);

        //then
        $this->assertEquals(2, count($ret));
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testAdd()
    {
        //given
        $store = new Buffered();

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $ret = $store->add($object);

        //then
        $this->assertEquals($object, $ret);
    }

    public function testSet()
    {
        //given
        $store = new Buffered();

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;
        $store->add($object);

        //when
        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 1;
        $object2->attr1 = 2;
        $object2->attr2 = 2;
        $object2->attr3 = 2;
        $store->set($object2);

        //then
        $ret = $store->search(new Item());
        $this->assertEquals($object2, $ret[0]);
    }

    public function testRemove()
    {
        //given
        $store = new Buffered();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;
        $store->add($object1);

        $object2 = new Item();
        $object2->id1 = 1;
        $object2->id2 = 1;
        $object2->id3 = 2;
        $object2->attr1 = 1;
        $object2->attr2 = 1;
        $object2->attr3 = 1;
        $store->add($object2);

        //when
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $store->remove($object);

        //then
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $ret = $store->search($object);
        $this->assertEquals($object2, $ret[0]);
    }
}