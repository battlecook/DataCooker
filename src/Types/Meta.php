<?php
declare(strict_types=1);

namespace battlecook\Types;

final class Meta
{
    private $field;
    private $dataName;
    private $depth;

    public function __construct(Field $field, string $dataName)
    {
        $this->field = $field;
        $this->dataName = $dataName;
        $this->depth = count($field->getIdentifiers());
    }

    public function getField(): Field
    {
        return $this->field;
    }

    public function hasAutoIncrement(): bool
    {
        return $this->field->hasAutoIncrement();
    }

    public function getDataName(): string
    {
        return $this->dataName;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }
}