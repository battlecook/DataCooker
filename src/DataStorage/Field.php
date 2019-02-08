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
}