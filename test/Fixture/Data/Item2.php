<?php
declare(strict_types=1);

namespace test\Fixture\Data;

use battlecook\Data\Model;

require __DIR__  . '/../../../vendor/autoload.php';

final class Item2 extends Model
{
    public $id1;

    public function getIdentifiers(): array
    {
        return array('id1');
    }

    public function getAutoIncrement(): string
    {
        return '';
    }

    public function getAttributes(): array
    {
        return array('attr1');
    }
}