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
    /**
     * @param Model $object
     */
    public function add(Model $object);
    /**
     * @param Model $object
     * @return int
     */
    public function remove(Model $object);

    public function flush($data);
    public function rollback();
}