<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

use battlecook\Data\Status;

final class LeafNode
{
    private $data;
    private $status;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->status = Status::INSERTED;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(): int
    {
        return $this->status;
    }

    public function getData()
    {
        return $this->data;
    }

    public function update(array $data, int $status)
    {
        $this->status = $status;
        $this->data = $data;
    }

    public function delete(int $status)
    {
        $this->status = $status;
    }
}