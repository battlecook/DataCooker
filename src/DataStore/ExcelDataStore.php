<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class ExcelDataStore extends BufferDataStore implements DataStore
{
    private $buffer;
    private $store;

    public function __construct(DataStore $store = null, $config)
    {
        $this->buffer = array();
        $this->store = $store;
    }

    public function get(Model $object)
    {
        if(empty($this->buffer))
        {
           //excel 에서 가져오는 로직 추가
        }

        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                $this->buffer[] = array('data' => $data, 'state' => DataState::NOT_CHANGED);
            }
        }

        $ret = parent::get($object);

        return $ret;
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