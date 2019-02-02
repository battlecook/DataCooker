<?php
declare(strict_types=1);

namespace battlecook\DataStructure;

class Node
{
    private $pointer;
    private $children;

    public function __construct($pointer)
    {
        $this->pointer = $pointer;
    }
}