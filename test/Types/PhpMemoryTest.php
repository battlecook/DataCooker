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

        $store = new PhpMemory();

        $keys = array(1, 1, 1);
        $data1 = new Item();
        $data1->id1 = 1;
        $data1->id2 = 1;
        $data1->id3 = 1;
        $data1->attr1 = 1;
        $data1->attr2 = 1;
        $data1->attr3 = 1;
        $store->insert($dataName, $keys, $data1, Status::INSERTED);

        $keys = array(1, 1, 2);
        $data2 = new Item();
        $data2->id1 = 1;
        $data2->id2 = 1;
        $data2->id3 = 1;
        $data2->attr1 = 1;
        $data2->attr2 = 1;
        $data2->attr3 = 1;
        $store->insert($dataName, $keys, $data2, Status::INSERTED);

        //when
        $keys = array(1, 1);
        $ret = $store->search($dataName, $keys);

        //then
        $this->assertEquals(2, count($ret));
        $this->assertEquals($data1, $ret[0]->getData());
        $this->assertEquals($data2, $ret[1]->getData());
    }

    public function testInsert()
    {
        //given
        $dataName = get_class(new Item());

        $store = new PhpMemory();

        //when
        $keys = array(1, '2', 3);

        $data = new Item();
        $data->id1 = 1;
        $data->id2 = '2';
        $data->id3 = 3;
        $data->attr1 = '1';
        $data->attr2 = '2';
        $data->attr3 = 3;
        $store->insert($dataName, $keys, $data, Status::INSERTED);

        //then
        $ret = $store->search($dataName, $keys);
        $this->assertEquals($data, $ret[0]->getData());
    }

    public function testInsertWithoutAutoIncrement()
    {
        //given
        $dataName = get_class(new Item());

        $store = new PhpMemory();

        $keys = array(1, 2, 3);

        $data = new Item();
        $data->id1 = 1;
        $data->id2 = 2;
        $data->id3 = 3;
        $data->attr1 = 1;
        $data->attr2 = 2;
        $data->attr3 = 3;
        $store->insert($dataName, $keys, $data, Status::INSERTED);

        $data->attr1 = 1;
        $data->attr2 = 2;
        $data->attr3 = 4;
        $store->update($dataName, $keys, $data, true);

        //when
        $keys = array(1, 2, 3);
        $data->attr1 = 1;
        $data->attr2 = 2;
        $data->attr3 = 3;
        $store->insert($dataName, $keys, $data, Status::UPDATED);

        //then
        $ret = $store->search($dataName, $keys);
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

        $store = new PhpMemory();

        $data = new Item();
        $data->id1 = 1;
        $data->id2 = '2';
        $data->id3 = 3;
        $data->attr1 = '1';
        $data->attr2 = '2';
        $data->attr3 = '3';

        //when
        $store->update($dataName, array(1, '2', 3), $data, true);

        //then
    }

    public function testUpdate()
    {
        //given
        $dataName = get_class(new Item());

        $store = new PhpMemory();

        $keys = array(1, '2', 3);

        $data = new Item();
        $data->id1 = 1;
        $data->id2 = '2';
        $data->id3 = 3;
        $data->attr1 = 1;
        $data->attr2 = 2;
        $data->attr3 = 3;

        $store->insert($dataName, $keys, $data, Status::INSERTED);

        $data = new Item();
        $data->id1 = 1;
        $data->id2 = '2';
        $data->id3 = 3;
        $data->attr1 = '1';
        $data->attr2 = '2';
        $data->attr3 = '3';

        //when
        $store->update($dataName, array(1, '2', 3), $data, true);

        //then
        $ret = $store->search($dataName, $keys);
        $this->assertEquals($data, $ret[0]->getData());
    }

    public function testDeleteUnsetData()
    {
        //given
        $dataName = get_class(new Item());

        $store = new PhpMemory();

        $keys = array(1, '2', 3);

        $data = new Item();
        $data->id1 = 1;
        $data->id2 = '2';
        $data->id3 = 3;
        $data->attr1 = 1;
        $data->attr2 = 2;
        $data->attr3 = 3;

        $store->insert($dataName, $keys, $data, Status::INSERTED);

        //when
        $store->delete($dataName, array(1, '2', 3), false);

        //then
        $ret = $store->search($dataName, $keys);
        $this->assertEquals(array(), $ret);
    }

    public function testDeleteNotUnsetData()
    {
        //given
        $dataName = get_class(new Item());

        $store = new PhpMemory();

        $keys = array(1, '2', 3);

        $data = new Item();
        $data->id1 = 1;
        $data->id2 = '2';
        $data->id3 = 3;
        $data->attr1 = 1;
        $data->attr2 = 2;
        $data->attr3 = 3;

        $store->insert($dataName, $keys, $data, Status::INSERTED);

        //when
        $store->delete($dataName, array(1, '2', 3), true);

        //then
        $ret = $store->search($dataName, $keys);
        $this->assertEquals(Status::DELETED, $ret[0]->getStatus());
    }

    public function testDelete()
    {
        //given
        $dataName = get_class(new Item());

        $storage = new PhpMemory();

        $keys1 = array(1, 1, 1);
        $data = array(1, 2, 3);

        $data = new Item();
        $data->id1 = 1;
        $data->id2 = 1;
        $data->id3 = 1;
        $data->attr1 = 1;
        $data->attr2 = 2;
        $data->attr3 = 3;

        $storage->insert($dataName, $keys1, $data, Status::INSERTED);

        $keys2 = array(1, 1, 2);
        $data = array(1, 2, 3);


        $data = new Item();
        $data->id1 = 1;
        $data->id2 = 1;
        $data->id3 = 2;
        $data->attr1 = 1;
        $data->attr2 = 2;
        $data->attr3 = 3;

        $storage->insert($dataName, $keys2, $data, Status::INSERTED);

        //when
        $storage->delete($dataName, $keys1, false);

        //then
        $ret = $storage->search($dataName, $keys1);
        $this->assertEquals(array(), $ret);

        $ret = $storage->search($dataName, array(1, 1));
        $this->assertEquals($data, $ret[0]->getData());
    }

    public function testGetData()
    {
        //given
        $store = new PhpMemory();

        $dataName1 = get_class(new Item());

        $keys1 = array(1, 1, 1);

        $data1 = new Item();
        $data1->id1 = 1;
        $data1->id2 = 1;
        $data1->id3 = 1;
        $data1->attr1 = 1;
        $data1->attr2 = 2;
        $data1->attr3 = 3;
        $store->insert($dataName1, $keys1, $data1, Status::INSERTED);

        $keys2 = array(1, 1, 2);

        $data2 = new Item();
        $data2->id1 = 1;
        $data2->id2 = 1;
        $data2->id3 = 2;
        $data2->attr1 = 1;
        $data2->attr2 = 2;
        $data2->attr3 = 3;
        $store->insert($dataName1, $keys2, $data2, Status::INSERTED);

        $dataName2 = get_class(new Quest());

        $keys1 = array(1, 1, 1);

        $data3 = new Item();
        $data3->id1 = 1;
        $data3->id2 = 1;
        $data3->id3 = 1;
        $data3->attr1 = 1;
        $data3->attr2 = 2;
        $data3->attr3 = 3;
        $store->insert($dataName2, $keys1, $data3, Status::INSERTED);

        $keys2 = array(1, 1, 2);

        $data4 = new Item();
        $data4->id1 = 1;
        $data4->id2 = 1;
        $data4->id3 = 2;
        $data4->attr1 = 1;
        $data4->attr2 = 2;
        $data4->attr3 = 3;
        $store->insert($dataName2, $keys2, $data4, Status::INSERTED);

        //when
        $ret = $store->getTrees();

        //then
        $expected = array(
            $dataName1 => array(
                1 => array(
                    1 => array(
                        1 => new LeafNode($keys1, $data1),
                        2 => new LeafNode($keys2, $data2),
                    )
                )
            ),
            $dataName2 => array(
                1 => array(
                    1 => array(
                        1 => new LeafNode($keys1, $data3),
                        2 => new LeafNode($keys2, $data4),
                    )
                )
            ),
        );
        $this->assertEquals($expected, $ret);
    }

}