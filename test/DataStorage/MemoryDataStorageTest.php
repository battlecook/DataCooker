<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataStorage\Memory;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;

require __DIR__  . '/../../vendor/autoload.php';

class MemoryDataStorageTest extends TestCase
{
    /**
     * @throws \battlecook\DataCookerException
     */
    public function testAddNotFillAllField()
    {
        //given
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $storage = new Memory();

        //when
        $storage->add($object);

        //then
        $this->assertEquals(1,1);
    }
}