<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

final class Field
{
    private $identifiers;
    private $autoincrement;
    private $attributes;

    public function __construct(array $identifiers, string $autoincrement, array $attributes)
    {
        //todo added constraint params ex( empty($identifiers) )

        // for later need depth constraint
        $this->identifiers = $identifiers;
        $this->autoincrement = $autoincrement;
        $this->attributes = $attributes;
    }

    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    public function getAutoIncrement(): string
    {
        return $this->autoincrement;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getFields(): array
    {
        $fields = array_merge($this->identifiers, $this->attributes);
        $fields = array_diff($fields, array($this->autoincrement));

        return $fields;
    }

    public function hasAutoIncrement(): bool
    {
        if ($this->autoincrement === "") {
            return false;
        }
        return true;
    }
}