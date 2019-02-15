<?php
declare(strict_types=1);

namespace battlecook\Config;

final class Spreadsheet
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}