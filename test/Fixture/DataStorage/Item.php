<?php
declare(strict_types=1);

namespace test\Fixture\DataStorage;

require __DIR__  . '/../../../vendor/autoload.php';

final class Item
{
    /**
     * @dataCookerVersion
     */
    public $version;
    /**
     * @dataCookerIdentifier
     */
    public $id1;
    /**
     * @dataCookerIdentifier
     */
    public $id2;
    /**
     * @dataCookerIdentifier
     */
    public $id3;
    /**
     * @dataCookerAutoIncrement
     */
    public $auto1;
    /**
     * @dataCookerAttribute
     */
    public $attr2;
    /**
     * @dataCookerAttribute
     */
    public $attr3;
}