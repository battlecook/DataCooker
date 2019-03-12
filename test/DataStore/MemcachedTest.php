<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataStore\Memcached;
use battlecook\DataStore\Spreadsheet;
use battlecook\DataStorage\LeafNode;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;

require __DIR__ . '/../../vendor/autoload.php';

class MemcachedTest extends TestCase
{
    public function setUp()
    {
        Spreadsheet::initialize();
    }

    public function testCommit()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache()));
        $data = array(get_class(new Item()) =>
            array(1 =>
                array(1 =>
                    array(1 => new LeafNode(array(1,1,1), array(1,1,1)),
                        2 => new LeafNode(array(1,1,2), array(1,1,1))
                    ),
                )
            )
        );

        //when
        $store->commit($data);

        //then
    }

    public function testGet()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache()));
        $data = array(get_class(new Item()) =>
            array(1 =>
                array(1 =>
                    array(1 => new LeafNode(array(1,1,1), array(1,1,1)),
                           2 => new LeafNode(array(1,1,2), array(1,1,1))
                    ),
                )
            )
        );

        $store->commit($data);

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;

        //when
        $ret = $store->get($object);

        //then
        $this->assertEquals($object, $ret[0]);
    }

    public function testAdd()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache()));

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        $store->commit();

        //when
        $ret = $store->add($object);

        //then
        $this->assertEquals($object, $ret);
    }

    public function testSet()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache()));

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
        $ret = $store->get(new Item());
        $this->assertEquals($object2, $ret[0]);
    }

    public function testRemove()
    {
        //given
        $store = new Memcached(null, array(new \battlecook\Config\Memcache()));

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
        $ret = $store->get($object);
        $this->assertEquals($object2, $ret[0]);
    }
}