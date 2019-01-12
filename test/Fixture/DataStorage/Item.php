<?php
declare(strict_types=1);

namespace test\Fixture\DataStorage;

require __DIR__  . '/../../../vendor/autoload.php';

final class Item
{
    /**
     * @DataCookerVersion
     */
    public $version;
    /**
     * @DataCookerIdentifier
     */
    public $id1;
    /**
     * @DataCookerIdentifier
     */
    public $id2;
    /**
     * @DataCookerIdentifier
     */
    public $id3;
    /**
     * @DataCookerAutoIncrement
     */
    public $auto1;
    /**
     * @DataCookerAttribute
     */
    public $attr2;
    /**
     * @DataCookerAttribute
     */
    public $attr3;
}