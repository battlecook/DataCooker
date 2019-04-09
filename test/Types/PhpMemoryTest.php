<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\Types\Status;
use battlecook\Types\Field;
use battlecook\Types\LeafNode;
use battlecook\Types\Meta;
use battlecook\Types\PhpMemory;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStore\Item;
use test\Fixture\DataStore\Quest;

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
        $storage = new PhpMemory();

        //when
        $keys = array(1, '2', 3);
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
        //$storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName));

        $keys = array(1, 1, 1);
        $data1 = array(1, 1, 1);
        $storage->insert($dataName, $keys, $data1);

        $keys = array(1, 1, 2);
        $data2 = array(1, 1, 1);
        $storage->insert($dataName, $keys, $data2);

        //when
        $keys = array(1, 1);
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
        $storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName));

        //when
        $keys = array(1, '2', 3);
        $data = array('1', '2', 3);
        $storage->insert($dataName, $keys, $data);

        //then
        $ret = $storage->search($dataName, $keys);
        $this->assertEquals($data, $ret[0]->getData());
    }

    public function testInsertWithoutAutoIncrement()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName));

        $keys = array(1, 2, 3);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys, $data);

        $data = array(1, 2, 4);
        $storage->update($dataName, $keys, $data);

        //when
        $keys = array(1, 2, 3);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys, $data);

        //then
        $ret = $storage->search($dataName, $keys);
        $this->assertEquals($data, $ret[0]->getData());
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
        $storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName));

        //when
        $storage->update($dataName, array(1, '2', 3), array('1', '2', '3'));

        //then
    }

    public function testUpdate()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName));

        $keys = array(1, '2', 3);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys, $data);

        //when
        $storage->update($dataName, array(1, '2', 3), array('1', '2', '3'));

        //then
        $ret = $storage->search($dataName, $keys);
        $this->assertEquals($data, $ret[0]->getData());
    }

    public function testDeleteUnsetData()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName));

        $keys = array(1, '2', 3);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys, $data);

        //when
        $storage->delete($dataName, array(1, '2', 3));

        //then
        $ret = $storage->search($dataName, $keys);
        $this->assertEquals(array(), $ret);
    }

    public function testDeleteNotUnsetData()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $autoIncrement = 'id1';
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers, $autoIncrement, $attributes), $dataName));

        $keys = array(1, '2', 3);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys, $data);

        //when
        $storage->delete($dataName, array(1, '2', 3));

        //then
        $ret = $storage->search($dataName, $keys);
        $this->assertEquals(Status::DELETED, $ret[0]->getStatus());
    }

    public function testDelete()
    {
        //given
        $dataName = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage = new PhpMemory();
        $storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName));

        $keys1 = array(1, 1, 1);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys1, $data);

        $keys2 = array(1, 1, 2);
        $data = array(1, 2, 3);
        $storage->insert($dataName, $keys2, $data);

        //when
        $storage->delete($dataName, $keys1);

        //then
        $ret = $storage->search($dataName, $keys1);
        $this->assertEquals(array(), $ret);

        $ret = $storage->search($dataName, array(1, 1));
        $this->assertEquals($data, $ret[0]->getData());
    }

    public function testGetData()
    {
        //given
        $storage = new PhpMemory();

        $dataName1 = get_class(new Item());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName1));

        $keys1 = array(1, 1, 1);
        $data = array(1, 2, 3);
        $storage->insert($dataName1, $keys1, $data);

        $keys2 = array(1, 1, 2);
        $data = array(1, 2, 3);
        $storage->insert($dataName1, $keys2, $data);

        $dataName2 = get_class(new Quest());
        $identifiers = array('id1', 'id2', 'id3');
        $attributes = array("attr1", "attr2", "attr3");

        $storage->addMetaData(new Meta(new Field($identifiers, "", $attributes), $dataName2));

        $keys1 = array(1, 1, 1);
        $data = array(1, 2, 3);
        $storage->insert($dataName2, $keys1, $data);

        $keys2 = array(1, 1, 2);
        $data = array(1, 2, 3);
        $storage->insert($dataName2, $keys2, $data);

        //when
        $ret = $storage->getTrees();

        //then
        $expected = array(
            $dataName1 => array(
                1 => array(
                    1 => array(
                        1 => new LeafNode($keys1, $data),
                        2 => new LeafNode($keys2, $data),
                    )
                )
            ),
            $dataName2 => array(
                1 => array(
                    1 => array(
                        1 => new LeafNode($keys1, $data),
                        2 => new LeafNode($keys2, $data),
                    )
                )
            ),
        );
        $this->assertEquals($expected, $ret);
    }

}