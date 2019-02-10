<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataStorage\Field;
use battlecook\DataStorage\Meta;
use battlecook\DataStorage\PhpMemory;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;

require __DIR__ . '/../../vendor/autoload.php';

class PhpMemoryTest extends TestCase
{
    /**
     * @throws \battlecook\DataCookerException
     */
    public function testSearchEmptyData()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers,"", $attributes), $dataName));

        //when
        $keys = array(1,'2',3);
        $ret = $storage->search($dataName, $keys);

        //then
        $this->assertEquals(array(), $ret);
    }

    public function testSearchInternalNode()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers,"", $attributes), $dataName));

        $keys = array(1,1,1);
        $data1 = array(1,1,1);
        $storage->insert($dataName, $keys, $data1);

        $keys = array(1,1,2);
        $data2 = array(1,1,1);
        $storage->insert($dataName, $keys, $data2);

        //when
        $keys = array(1,1);
        $ret = $storage->search($dataName, $keys);

        //then
        $this->assertEquals(2, count($ret));
        $this->assertEquals($data1, $ret[0]->getData());
        $this->assertEquals($data2, $ret[1]->getData());
    }

    public function testInsert()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers,"", $attributes), $dataName));

        //when
        $keys = array(1,'2',3);
        $data = array('1','2', 3);
        $storage->insert($dataName, $keys, $data);

        //then
        $ret = $storage->search($dataName, $keys);
        $this->assertEquals($data, $ret[0]->getData());
    }

    public function testInsertWithoutAutoIncrement()
    {
        $this->assertEquals(1, 1);
    }

    public function testInsertWithAutoIncrement()
    {
        $this->assertEquals(1, 1);
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @throws \battlecook\DataCookerException
     */
    public function testUpdateEmptyData()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers,"", $attributes), $dataName));

        $keys = array(1,'2',3);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys, $data);

        //when
        $storage->update($dataName, array(1,'2',3), array('1','2', '3'));

        //then
    }

    public function testUpdate()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers,"", $attributes), $dataName));

        $keys = array(1,'2',3);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys, $data);

        //when
        $storage->update($dataName, array(1,'2',3), array('1','2'));

        //then
    }

    public function testDelete()
    {
        //given
        $dataName = get_class(new Item());
        $storage = new PhpMemory();

        //when
        $storage->delete($dataName, array(1,'2',3));

        //then
        $this->assertEquals(1,1);
    }
}