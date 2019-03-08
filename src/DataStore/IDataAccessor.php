<?php
declare(strict_types=1);

namespace battlecook\DataStore;

interface IDataAccessor
{
    public function add($object);

    public function get($object): array;

    public function set($object);

    public function remove($object);
}