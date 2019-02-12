<?php
declare(strict_types=1);

namespace battlecook\DataAccessor;

interface IDataAccessor
{
    public function add($object);
    public function get($object);
    public function set($object);
    public function remove($object);
}