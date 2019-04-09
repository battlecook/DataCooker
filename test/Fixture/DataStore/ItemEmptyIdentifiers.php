<?php
declare(strict_types=1);

namespace test\Fixture\DataStore;

require __DIR__ . '/../../../vendor/autoload.php';

final class ItemEmptyIdentifiers
{
    public $version;
    public $id1;
    public $id2;
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