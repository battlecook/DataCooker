<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\Config\Auth;
use battlecook\Config\Database;
use battlecook\DataAccessor\RelationDatabase;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;

require __DIR__ . '/../../vendor/autoload.php';

class RelationDatabaseTest extends TestCase
{
    public function testAdd()
    {
        //given
        $storage = new RelationDatabase(null, new Database('localhost', 3306, "DataCooker", new Auth("user", "password")));

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
        $storage = new RelationDatabase(null, new Database('localhost', 3306, "DataCooker", new Auth("user", "password")));

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
        $storage = new RelationDatabase(null, new Database('localhost', 3306, "DataCooker", new Auth("user", "password")));

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