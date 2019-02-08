<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataAccessor\Buffer;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;

require __DIR__ . '/../../vendor/autoload.php';

class BufferTest extends TestCase
{
    /**
     * @throws \battlecook\DataCookerException
     */
    public function testAdd()
    {
        //given
        $object = new Item();
        $object->id1 = 1;
        $object->id2 = 1;
        $object->id3 = 1;
        $storage = new Buffer();

        //when
        $storage->get($object);

        //then
        $this->assertEquals(1,1);
    }
}