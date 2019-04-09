<?php
declare(strict_types=1);

namespace battlecook\Types;

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