<?php

namespace test\DataStore;

use battlecook\DataStore\ExcelDataStore;
use PHPUnit\Framework\TestCase;
use test\Fixture\ExcelDataStore\Monster;

require __DIR__  . '/../../vendor/autoload.php';

class ExcelDataStoreTest extends TestCase
{
    public function testGet()
    {
        //given
        $path = __DIR__ . '/../Fixture/ExcelDataStore/monster.xlsx';
        $store = new ExcelDataStore(null, $path);

        //when
        $object = new Monster();
        $object->id = 1;
        $object->x = 3;

        $ret = $store->get($object);

        //then
        $this->assertEquals(1, count($ret));
    }
}
