<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\Config\Auth;
use battlecook\Config\Database;
use battlecook\DataStore\RelationDatabase;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;

require __DIR__ . '/../../vendor/autoload.php';

class RelationDatabaseTest extends TestCase
{
    private $ip = "localhost";
    private $port = 3306;
    private $dbName = "DataCooker";
    private $user = "user";
    private $password = "password";

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

    private function getConfig()
    {
        return new Database($this->ip, $this->port, $this->dbName, new Auth($this->user, $this->password));
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testAddWithAutoIncrement()
    {
        //given
        $storage = new RelationDatabase(null, $this->getConfig());

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

    /**
     * @expectedException \battlecook\DataCookerException
     * @throws \battlecook\DataCookerException
     */
    public function testAddDuplicated()
    {
        //given
        $storage = new RelationDatabase(null, $this->getConfig());

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
    public function testAddWithoutAutoIncrement()
    {
        //given
        $storage = new RelationDatabase(null, $this->getConfig());

        $object = new Item();
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $ret = $storage->add($object);

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
    public function testGet()
    {
        //given
        $storage = new RelationDatabase(null, $this->getConfig());

        $object = new Item();

        //when
        $ret = $storage->get($object);

        //then
        $this->assertEquals(array(), $ret);
    }

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testSet()
    {
        //given
        $storage = new RelationDatabase(null, $this->getConfig());

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

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testRemove()
    {
        //given
        $storage = new RelationDatabase(null, $this->getConfig());

        $object1 = new Item();
        $object1->id1 = 1;
        $object1->id2 = 1;
        $object1->id3 = 1;
        $object1->attr1 = 1;
        $object1->attr2 = 1;
        $object1->attr3 = 1;
        $storage->add($object1);

        $object2 = new Item();
        $object2->id1 = 2;
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
        $object->id2 = 1;
        $ret = $storage->get($object);
        $this->assertEquals($object2, $ret[0]);
    }
}