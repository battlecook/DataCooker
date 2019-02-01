<?php
declare(strict_types=1);

namespace battlecook\DataStructure;

final class LeafNode extends Node
{
    const NONE = 0;
    const ADDED = 1;
    const UPDATED = 2;
    const DELETED = 4;

    private $data;
    private $keys;
    private $status;

    public function __construct($pointer, array $data, array $keys)
    {
        parent::__construct($pointer);

        $this->data = $data;
        $this->keys = $keys;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function update(array $data): bool
    {
        if($this->status === self::DELETED)
        {
            return false;
        }

        $isChanged = false;
        foreach($this->keys as $key)
        {
            if($this->data[$key] === $data[$key])
            {
                continue;
            }
            $this->data[$key] = $data[$key];
            if($isChanged === false)
            {
                $isChanged = true;
            }
        }

        if($isChanged === true && $this->status !== self::UPDATED)
        {
            $this->status = self::UPDATED;
        }
    }
}