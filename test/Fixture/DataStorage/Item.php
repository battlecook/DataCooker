<?php
declare(strict_types=1);

namespace test\Fixture\DataStorage;

use battlecook\Data\Model;

require __DIR__  . '/../../../vendor/autoload.php';

final class Item extends Model
{
    public $id1;
    public $id2;
    public $id3;
    public $auto1;
    public $attr2;
    public $attr3;

    public function getIdentifiers(): array
    {
        return array('id1', 'id2', 'id3');
    }

    public function getAutoIncrement(): string
    {
        return 'auto1';
    }

    public function getAttributes(): array
    {
        return array('auto1', 'attr1', 'attr2', 'attr3');
    }
}