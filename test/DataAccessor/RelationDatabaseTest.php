<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\Config\Auth;
use battlecook\Config\Database;
use battlecook\DataAccessor\RelationDatabase;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCase;
use test\Fixture\DataStorage\Item;

require __DIR__ . '/../../vendor/autoload.php';

class RelationDatabaseTest extends TestCase
{
    private $ip = "localhost";
    private $port = 3306;
    private $dbName = "DataCooker";
    private $user = "user";
    private $password = "password";

    private function getConfig()
    {
        return new Database($this->ip, $this->port, $this->dbName, new Auth($this->user, $this->password));
    }

    public function testAdd()
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

    /**
     * Returns the test database connection.
     *
     * @return Connection
     */
    protected function getConnection()
    {
        $dsn = "mysql:host={$this->ip};port={$this->port};dbname={$this->dbName}";
        $schema = "Item";

        return $this->createDefaultDBConnection(new \PDO($dsn, $this->user, $this->password, array()), $schema);
    }

    /**
     * Returns the test dataset.
     *
     * @return IDataSet
     */
    protected function getDataSet()
    {
        // TODO: Implement getDataSet() method.
    }
}