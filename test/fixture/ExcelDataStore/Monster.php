<?php

namespace test\Fixture\ExcelDataStore;

use battlecook\DataObject\Model;

require __DIR__  . '/../../../vendor/autoload.php';

class Monster extends Model
{
    /**
     * @dataStoreIdentifier
     */
    public $id;
    /**
     * @dataStoreAttribute
     */
    public $x;
    /**
     * @dataStoreAttribute
     */
    public $y;
}