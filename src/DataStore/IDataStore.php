<?php
declare(strict_types=1);

namespace battlecook\DataStore;

interface IDataStore
{
    public function add($object);

    public function get($object);

    public function search($filter): array;

    public function set($object);

    public function remove($object);

    public function commit($data = null);
}