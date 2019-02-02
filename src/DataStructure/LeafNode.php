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
    private $currentStatus;

    public function __construct($pointer, array $data, array $keys)
    {
        parent::__construct($pointer);

        $this->data = $data;
        $this->keys = $keys;
    }

    public function getStatus(): int
    {
        return $this->currentStatus;
    }

    public function update(array $data): bool
    {
        if($this->currentStatus === self::DELETED)
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

        if($isChanged === true && $this->currentStatus !== self::UPDATED)
        {
            $this->currentStatus = self::UPDATED;
        }

        return true;
    }
}