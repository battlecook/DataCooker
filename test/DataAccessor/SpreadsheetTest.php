<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataAccessor\Spreadsheet;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataAccessor\Quest;
use test\Fixture\DataStorage\Item;

require __DIR__ . '/../../vendor/autoload.php';

class SpreadsheetTest extends TestCase
{
    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage this sheet is empty
     */
    public function testEmptySheet()
    {
        //given
        $storage = new Spreadsheet(null, new \battlecook\Config\Spreadsheet("../Fixture/DataAccessor/SampleEmptySheet.xlsx"));

        $object = new Item();

        //when
        $storage->add($object);

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage this path is invalid path.
     */
    public function testNotExistPath()
    {
        //given

        //when
        new Spreadsheet(null, new \battlecook\Config\Spreadsheet("../Fixture/DataAccessor/Sample"));

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage not exist sheet
     */
    public function testNotExistSheet()
    {
        //given
        $storage = new Spreadsheet(null, new \battlecook\Config\Spreadsheet("../Fixture/DataAccessor/Sample.xlsx"));

        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $storage->add($object);

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage difference fields and columns
     */
    public function testDifferenceFieldsAndColumns()
    {
        //given
        $storage = new Spreadsheet(null, new \battlecook\Config\Spreadsheet("../Fixture/DataAccessor/Sample.xlsx"));

        $object = new Quest();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $storage->add($object);

        //then
    }

    public function testGet()
    {
        //given
        $storage = new Spreadsheet(null, new \battlecook\Config\Spreadsheet("../Fixture/DataAccessor/Sample.xlsx"));

        $object = new Quest();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $object->attr1 = 1;
        $object->attr2 = 1;
        $object->attr3 = 1;

        //when
        $ret = $storage->get($object);

        //then
        $this->assertEquals($object, $ret);
    }

    public function testAdd()
    {
        //given
        $storage = new Spreadsheet(null, new \battlecook\Config\Spreadsheet("../Fixture/DataAccessor/Sample.xlsx"));

        $object = new Quest();
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
        $storage = new Spreadsheet(null, new \battlecook\Config\Spreadsheet(""));

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
        $storage = new Spreadsheet(null, new \battlecook\Config\Spreadsheet(""));

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