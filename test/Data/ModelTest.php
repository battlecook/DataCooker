<?php
declare(strict_types=1);

namespace test\Data;

use PHPUnit\Framework\TestCase;
use test\Fixture\Data\Item1;
use test\Fixture\Data\Item2;
use test\Fixture\Data\Item3;

require __DIR__  . '/../../vendor/autoload.php';

class ModelTest extends TestCase
{
    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage identifiers cant't be an empty array
     */
    public function testIdentifiersCantBeAnEmptyArray()
    {
        //given

        //when
        new Item1();

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage autoIncrement must not be an empty string ('')
     */
    public function testAutoIncrementCantBeAnEmptyString()
    {
        //given

        //when
        new Item2();

        //then
    }

    /**
     * @expectedException \battlecook\DataCookerException
     * @expectedExceptionMessage autoIncrement have to include identifiers or attributes
     */
    public function testAutoIncrementNotIncludeIdentifiersOrAttributes()
    {
        //given

        //when
        new Item3();

        //then
    }
}
