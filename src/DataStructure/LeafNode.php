<?php
declare(strict_types=1);

namespace battlecook\DataStructure;

final class LeafNode extends Node
{
    private $data;
    private $status;

    public function __construct($pointer, $data)
    {
        parent::__construct($pointer);
        $this->data = $data;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function update($data)
    {

    }
}