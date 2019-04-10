<?php
declare(strict_types=1);

namespace battlecook\Config;

use battlecook\DataStore\IDataStore;

final class Apcu
{
    private $store;

    public function __construct(?IDataStore $store = null)
    {
        $this->store = $store;
    }

    public function getStore(): ?IDataStore
    {
        return $this->store;
    }
}