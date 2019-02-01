<?php
declare(strict_types=1);

namespace battlecook\DataStructure;

class Node
{
    private $pointer;

    public function __construct($pointer)
    {
        $this->pointer = $pointer;
    }
}