<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

use battlecook\Data\Status;

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

    public function update(int $status, array $data)
    {
        $this->status = $status;
        $this->data = $data;
    }

    public function delete(int $status)
    {
        $this->status = $status;
    }
}