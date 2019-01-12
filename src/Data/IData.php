<?php
declare(strict_types=1);

namespace battlecook\Data;

interface IData
{
    public function getIdentifiers(): array;
    public function getAutoIncrement(): ?string;
    public function getAttributes(): array;
}