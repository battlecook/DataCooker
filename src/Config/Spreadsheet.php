<?php
declare(strict_types=1);

namespace battlecook\Config;

use battlecook\DataStore\IDataStore;

final class Spreadsheet
{
    private $store;
    private $path;

    public function __construct(?IDataStore $store = null, string $path = "")
    {
        $this->store = $store;
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getStore(): ?IDataStore
    {
        return $this->store;
    }
}