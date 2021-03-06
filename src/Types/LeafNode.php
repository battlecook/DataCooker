<?php
declare(strict_types=1);

namespace battlecook\Types;

final class LeafNode extends Node
{
    private $originalData;
    private $data;
    private $status;

    public function __construct(array $key, $data, $status = Status::INSERTED)
    {
        parent::__construct($key);

        $this->data = clone $data;
        $this->originalData = clone $data;
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus($changedStatus)
    {
        $this->status = $changedStatus;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getOriginalData()
    {
        return $this->originalData;
    }

    public function update(int $status, $data)
    {
        $this->status = $status;
        $this->data = $data;
    }
}