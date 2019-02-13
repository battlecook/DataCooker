<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

class Node
{
    private $key;

    public function __construct(array $key)
    {
        $this->key = $key;
    }

    public function getKey(): array
    {
        return $this->key;
    }
}