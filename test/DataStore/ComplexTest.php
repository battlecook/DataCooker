<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataStore\Buffer;
use battlecook\DataStore\RelationDatabase;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;
use test\Helper\DatabaseConfigTrait;

require __DIR__ . '/../../vendor/autoload.php';

class ComplexTest extends TestCase
{
    use DatabaseConfigTrait;

    private function getPdo()
    {
        $dsn = "mysql:host={$this->ip};port={$this->port};dbname={$this->dbName}";

        return new \PDO($dsn, $this->user, $this->password, array());
    }

    public function setUp()
    {
        $pdo = $this->getPdo();
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
     * @throws \battlecook\DataCookerException
     */
    public function testBufferRelationDatabase()
    {
        //given
        $store = new Buffer(new RelationDatabase(null, $this->getConfig()));

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
        $storage = new Buffer(new RelationDatabase(null, $this->getConfig()));

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $storage->add($object);
        $storage->add($object);

        //then
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testSetBufferRDB()
    {
        //given
        Buffer::initialize();
        $store = new Buffer(new RelationDatabase(null, $this->getConfig()));

        $object = new Item();
        //$object->id1 = 1;
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

        $store->commit();

        //then
        $ret = $store->get(new Item());
        $this->assertEquals($object2, $ret[0]);

        $rdbStore = new RelationDatabase(null, $this->getConfig());
        $ret = $rdbStore->get(new Item());
        $this->assertEquals($object2, $ret[0]);
    }

    public function testSetRDBBuffer()
    {
        //given
        Buffer::initialize();
        $store = new RelationDatabase(new Buffer(), $this->getConfig());

        $object = new Item();
        //$object->id1 = 1;
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

        $store->commit();

        //then
        $ret = $store->get(new Item());
        $this->assertEquals($object2, $ret[0]);

        $rdbStore = new RelationDatabase(null, $this->getConfig());
        $ret = $rdbStore->get(new Item());
        $this->assertEquals($object2, $ret[0]);
    }
}