<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

use battlecook\Data\Model;

interface IDataStorage
{
    public function add(Model $object);
    public function get(Model $object);
    public function set(Model $object): int;
    public function remove(Model $object): int;

    public function commit();
    public function rollback();
}