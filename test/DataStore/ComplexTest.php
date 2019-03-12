<?php
declare(strict_types=1);

namespace test\DataStorage;

use battlecook\DataStore\Buffer;
use battlecook\DataStore\RelationDatabase;
use PHPUnit\Framework\TestCase;
use test\Fixture\DataStorage\Item;
use test\Helper\DatabaseConfigTrait;

require __DIR__ . '/../../vendor/autoload.php';

class ComplexTest extends TestCase
{
    use DatabaseConfigTrait;

    /**
     * @throws \battlecook\DataCookerException
     */
    public function testBufferRelationDatabase()
    {
        //given
        $storage = new Buffer(new RelationDatabase(null, $this->getConfig()));

        $object = new Item();

        //when
        $storage->add($object);

        //then
    }
}