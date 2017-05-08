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
    /**
     * @param Model $object
     * @return int $rowCount;
     */
    public function set(Model $object);
    public function setChangedAttributes(Model $object, $changedAttributes);
    /**
     * @param Model $object
     */
    public function add(Model $object);
    /**
     * @param Model $object
     * @return int
     */
    public function remove(Model $object);
    /**
     * @param Model[] $objects
     * @return int
     */
    public function removeMulti($objects);

    public function flush();
    public function rollback();
}