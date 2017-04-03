<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class ExcelDataStore implements DataStore
{
    private $buffer;
    private $store;

    public function __construct(DataStore $store = null)
    {
        $this->buffer = array();
        $this->store = $store;
    }

    public function get(Model $object)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param Model $object
     * @return int $rowCount;
     */
    public function set(Model $object)
    {
        // TODO: Implement set() method.
    }

    /**
     * @param Model $object
     * @return Model[];
     */
    public function add(Model $object)
    {
        // TODO: Implement add() method.
    }

    /**
     * @param Model $object
     * @return int
     */
    public function remove(Model $object)
    {
        // TODO: Implement remove() method.
    }

    public function flush()
    {
        // TODO: Implement flush() method.
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }
}