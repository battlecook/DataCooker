<?php
declare(strict_types=1);

namespace battlecook\DataStructure;

final class Attribute
{
    private $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}