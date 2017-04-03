<?php

namespace test;

use battlecook\DataStore\PdoDataStore;
use main\php\Config;
use PHPUnit\DbUnit\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use test\fixture\MemoryDataStore\BeforeData;
use test\fixture\MemoryDataStore\Item;
use test\fixture\MemoryDataStore\Shard;
use test\fixture\MemoryDataStore\User;

require __DIR__  . '/../vendor/autoload.php';

class PdoDataStoreTest extends TestCase
{
    use TestCaseTrait;

    public function setUp()
    {
        $this->getOperations()->CLEAN_INSERT()->execute($this->getConnection(), $this->getDataSet());
    }

    public function testGet()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new Item();
        $object->userId = 1;
        $object->itemId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        $store->flush();

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;

        $ret = $store->get($object);

        //then
        $this->assertEquals(1, count($ret));
        $this->assertEquals('item2', $ret[0]->itemName);
    }

    public function testGetEmptyCondition()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new Item();
        $object->userId = 1;
        $object->itemId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        $store->flush();

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemId = 1;

        $ret = $store->get($object);

        //then
        $this->assertEquals(0, count($ret));
    }

    public function testGetSameUser()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new Item();
        $object->userId = 1;
        $object->itemId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 1;
        $object->itemId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $store->flush();

        //when
        $object = new Item();
        $object->userId = 1;

        $ret = $store->get($object);

        //then
        $this->assertEquals(2, count($ret));
        $this->assertEquals('item1', $ret[0]->itemName);
        $this->assertEquals('item2', $ret[1]->itemName);
    }

    public function testGetShard()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new Shard();
        $object->localId = 1;
        $object->channelId = '1';
        $object->shardId = 1;
        $object->insertTime = '0000-00-00 00:00:00';

        $store->add($object);

        $object = new Shard();
        $object->localId = 2;
        $object->channelId = '2';
        $object->shardId = 2;
        $object->insertTime = '0000-00-00 00:00:00';

        $store->add($object);

        $object = new Shard();
        $object->localId = 3;
        $object->channelId = '3';
        $object->shardId = 3;
        $object->insertTime = '0000-00-00 00:00:00';

        $store->add($object);

        $store->flush();

        //when
        $object = new Shard();
        $object->localId = 2;

        $ret = $store->get($object);

        //then
        $this->assertEquals(1, count($ret));
        $this->assertEquals('2', $ret[0]->channelId);
    }

    public function testGetChannel()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new Channel();
        $object->channelId = '1';
        $object->localId = 1;

        $store->add($object);

        $object = new Channel();
        $object->channelId = '2';
        $object->localId = 2;

        $store->add($object);

        $object = new Channel();
        $object->channelId = '3';
        $object->localId = 3;

        $store->add($object);

        $store->flush();

        //when
        $object = new Channel();
        $object->channelId = '2';

        $ret = $store->get($object);

        //then
        $this->assertEquals(1, count($ret));
        $this->assertEquals('2', $ret[0]->channelId);
    }

    public function testAdd()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new User();
        $object->userId = 1;
        $object->userName = 'user';

        //when
        $store->add($object);

        //then
        $actual = $store->get($object)[0];
        $this->assertEquals($object, $actual);
    }

    public function testSet()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new Item();
        $object->userId = 1;
        $object->itemId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $object->itemName = 'item2-2';

        $ret = $store->set($object);

        //then
        $this->assertEquals(1, $ret);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $ret = $store->get($object);
        $this->assertEquals('item2-2', $ret[0]->itemName);
    }

    public function testRemove()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new Item();
        $object->userId = 1;
        $object->itemId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;

        $ret = $store->remove($object);

        //then
        $this->assertEquals(1, $ret);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $ret = $store->get($object);
        $this->assertEquals(array(), $ret);
    }

    public function testFlush()
    {
        //given
        $store = new PdoDataStore(null, new Config());

        $object = new Item();
        $object->userId = 1;
        $object->itemId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $object->itemName = 'item2-2';

        $ret = $store->set($object);
        $store->flush();

        //then
        $this->assertEquals(1, $ret);

        $object = new Item();
        $object->userId = 2;
        $object->itemId = 2;
        $ret = $store->get($object);
        $this->assertEquals('item2-2', $ret[0]->itemName);

        $fixtureData = \test\php\DataStore\AfterData::getData();
        $AfterDataSet = $this->createArrayDataSet($fixtureData);
        $expectedTableNames = $AfterDataSet->getTableNames();

        $dbo = new \main\php\DBO(new Config());
        $dbConnection = $this->createDefaultDBConnection($dbo->getPdo());

        foreach($expectedTableNames as $expectedTableName)
        {
            $AfterDataSet = $this->createArrayDataSet($fixtureData);
            $expectedTable = $AfterDataSet->getTable($expectedTableName);
            $metaData = $AfterDataSet->getTable($expectedTableName)->getTableMetaData();
            $columns = $metaData->getColumns();

            $sql = "select ";
            $delimiter = '';
            foreach ($columns as $column)
            {
                $sql .= $delimiter . $column;
                $delimiter = ', ';
            }
            $sql .= ' from ' . $expectedTableName;
            $actualTable = $dbConnection->createQueryTable($expectedTableName, $sql);

            $this->assertTablesEqual($expectedTable, $actualTable);
        }
    }

    /**
     * Returns the test database connection.
     *
     * @return \PHPUnit\DbUnit\Database\Connection
     */
    public function getConnection()
    {
        $dbo = new \main\php\DBO(new Config());

        return $this->createDefaultDBConnection($dbo->getPdo(), ':memory:');
    }

    /**
     * Returns the test dataset.
     *
     * @return \PHPUnit\DbUnit\DataSet\IDataSet
     */
    protected function getDataSet()
    {
        $data = BeforeData::getData();
        $dataSet = new DbUnitArrayDataSet($this->getConnection(), $data);

        return $dataSet;
    }
}
