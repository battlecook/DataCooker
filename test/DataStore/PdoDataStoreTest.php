<?php

namespace test\DataStore;

use battlecook\DataStore\BufferDataStore;
use battlecook\DataStore\PdoDataStore;
use PHPUnit\DbUnit\DefaultTester;
use PHPUnit\DbUnit\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;
use test\Fixture\PdoDataStore\AfterData;
use test\Fixture\PdoDataStore\BeforeData;
use test\Fixture\PdoDataStore\Item;
use test\Fixture\PdoDataStore\User;

require __DIR__  . '/../../vendor/autoload.php';

class PdoDataStoreTest extends TestCase
{
    use TestCaseTrait;

    public function setUp()
    {
        $this->getOperations()->CLEAN_INSERT()->execute($this->getConnection(), $this->getDataSet());
    }

    public function testGet()
    {
        $dbo = new DBO(new Config());
        //given
        $store = new PdoDataStore(null, function () use ($dbo)
        {
            return $dbo->getPdo();
        });

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemDesignId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;

        $ret = $store->get($object);

        //then
        $this->assertEquals(1, count($ret));
        $this->assertEquals('item2', $ret[0]->itemName);
    }

    public function testGetSameUser()
    {
        //given
        $store = new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        });

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 1;

        $ret = $store->get($object);

        //then
        $this->assertEquals(2, count($ret));
        $this->assertEquals('item1', $ret[0]->itemName);
        $this->assertEquals('item2', $ret[1]->itemName);
    }

    public function testAdd()
    {
        //given
        $store = new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        });

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
        $store = new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        });

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemDesignId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
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
        $store = new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        });

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemDesignId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;

        $ret = $store->remove($object);

        //then
        $this->assertEquals(1, $ret);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $ret = $store->get($object);
        $this->assertEquals(array(), $ret);
    }

    public function testFlush()
    {
        //given
        $pdoDataStore = new PdoDataStore(null, function (){
            $dbo = new DBO(new Config());
            return $dbo->getPdo();
        });

        $store = new BufferDataStore($pdoDataStore);

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $object->itemName = 'item1';

        $store->add($object);

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'item2';

        $store->add($object);

        $object = new Item();
        $object->userId = 3;
        $object->itemDesignId = 3;
        $object->itemName = 'item3';

        $store->add($object);

        //when
        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $object->itemName = 'item2-2';

        $ret = $store->set($object);

        $object = new Item();
        $object->userId = 1;
        $object->itemDesignId = 1;
        $store->remove($object);

        $store->flush();

        //then
        $this->assertEquals(1, $ret);
        $latAddedDataList = $store->getLastAddedDataList();
        $this->assertEquals(2, count($latAddedDataList));

        $object = new Item();
        $object->userId = 2;
        $object->itemDesignId = 2;
        $ret = $store->get($object);
        $this->assertEquals('item2-2', $ret[0]->itemName);

        $fixtureData = AfterData::getData();
        $AfterDataSet = $this->createArrayDataSet($fixtureData);
        $expectedTableNames = $AfterDataSet->getTableNames();

        $dbo = new DBO(new Config());
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
        $dbo = new DBO(new Config());

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
