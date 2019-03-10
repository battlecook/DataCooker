<?php
declare(strict_types=1);

namespace test\Fixture\DataStore;

require __DIR__  . '/../../../vendor/autoload.php';

final class Quest
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