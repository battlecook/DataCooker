<?php
declare(strict_types=1);

namespace test\DataStore;

use battlecook\DataStore\RelationDatabase;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStore\Item;
use test\Helper\Config;
use test\Helper\Option;

require __DIR__ . '/../../vendor/autoload.php';

class RelationDatabaseTest extends TestCase
{
    private function createPdo()
    {
        $ip = Option::$dbIP;
        $port = Option::$dbPort;
        $dbName = Option::$dbName;
        $user = Option::$user;
        $password = Option::$password;

        $dsn = "mysql:host={$ip};port={$port};dbname={$dbName}";

        return new \PDO($dsn, $user, $password, array());
    }

    public function setUp()
    {
        $pdo = $this->createPdo();
        $dropSql = "drop table Item;";
        $st = $pdo->prepare($dropSql);
        $st->execute();

        $createSql = "create table Item
(
	id1 int auto_increment,
	id2 int not null,
	id3 int not null,
	attr1 int not null,
	attr2 int not null,
	attr3 int not null,
	constraint Item_pk
		primary key (id1)
);

";
        $st = $pdo->prepare($createSql);
        $st->execute();
    }

    /**
     * @return RelationDatabase
     * @throws \battlecook\DataCookerException
     */
    private function createStore()
    {
        return new RelationDatabase(Option::getDatabaseOption());
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testAddWithAutoIncrement()
    {
        //given
        $store = $this->createStore();

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

    /**
     * @expectedException \battlecook\DataCookerException
     * @throws \battlecook\DataCookerException
     */
    public function testAddDuplicated()
    {
        //given
        $store = $this->createStore();

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $store->add($object);
        $store->add($object);

        //then
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testAddWithoutAutoIncrement()
    {
        //given
        $store = $this->createStore();

        $object = new Item();
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $ret = $store->add($object);

        //then
        $expected = new Item();
        $expected->id1 = 1;
        $expected->id2 = 1;
        $expected->id3 = 1;
        $expected->attr1 = 1;
        $expected->attr2 = 1;
        $expected->attr3 = 1;

        $this->assertEquals($expected, $ret);
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testSearchEmptyData()
    {
        //given
        $store = $this->createStore();

        $object = new Item();

        //when
        $ret = $store->search($object);

        //then
        $this->assertEquals(array(), $ret);
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testSet()
    {
        //given
        $store = $this->createStore();

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

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testRemove()
    {
        //given
        $store = $this->createStore();

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;
        $store->add($object1);

        $object2 = new Item();
        $object2->id1 = 2;
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
        $object->id2 = 1;
        $ret = $store->search($object);
        $this->assertEquals($object2, $ret[0]);
    }
}