<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

interface DataStore
{
    /**
     * @param Model $object
     * @return Model[];
     */
    public function get(Model $object);
    public function set(Model $object): int;
    public function setChangedAttributes(Model $object, $changedAttributes);
    public function add(Model $object);
    public function remove(Model $object): int;
    /**
     * @param Model[] $objects
     * @return int
     */
    public function removeMulti($objects): int;

    public function flush();
    public function rollback();

    public function getLastAddedDataList();

    public function reset();
}