<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

final class Meta
{
    private $field;
    private $dataName;
    private $depth;
    private $hasAutoIncrement;

    public function __construct(Field $field, string $dataName)
    {
        $this->field = $field;
        $this->dataName = $dataName;
        $this->depth = count($field->getIdentifiers());
        $this->hasAutoIncrement = true;
        if($field->getAutoIncrement() === "")
        {
            $this->hasAutoIncrement = false;
        }
    }

    public function getField()
    {
        return $this->getField();
    }

    public function hasAutoIncrement(): bool
    {
        return $this->hasAutoIncrement;
    }

    public function getDataName()
    {
        return $this->dataName;
    }

    public function getDepth()
    {
        return $this->depth;
    }
}