<?php
declare(strict_types=1);

namespace battlecook\DataStructure;

final class Sheet
{
    private $name;
    private $columns;

    public function __construct(string $name, array $columns, int $rowCount)
    {
        $this->name = $name;
        $this->columns = $columns;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
}