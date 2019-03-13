<?php
declare(strict_types=1);

namespace test\Fixture\DataStorage;

require __DIR__  . '/../../../vendor/autoload.php';

final class User
{
    /**
     * @dataCookerVersion
     */
    public $version;
    /**
     * @dataCookerAutoIncrement
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
     * @dataCookerAttribute
     */
    public $attr1;
    /**
     * @dataCookerAttribute
     */
    public $attr2;
    /**
     * @dataCookerAttribute
     */
    public $attr3;
}