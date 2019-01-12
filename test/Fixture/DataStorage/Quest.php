<?php
declare(strict_types=1);

namespace test\Fixture\DataStorage;

use battlecook\Data\Model;

require __DIR__  . '/../../../vendor/autoload.php';

final class Quest extends Model
{
    public $userId;
    public $itemDesignId;
    public $itemId;
    public $itemName;

    public function getIdentifiers(): array
    {
        // TODO: Implement getIdentifiers() method.
    }

    public function getAttributes(): array
    {
        // TODO: Implement getAttributes() method.
    }
}