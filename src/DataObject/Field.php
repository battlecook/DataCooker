<?php
declare(strict_types=1);

namespace battlecook\DataObject;

final class Field
{
    private $identifiers;
    private $autoincrement;
    private $attributes;

    public function __construct(array $identifiers, $autoincrement, array $attributes)
    {
        $this->identifiers = $identifiers;
        $this->autoincrement = $autoincrement;
        $this->attributes = $attributes;
    }


}