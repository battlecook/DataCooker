<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class RedisDataStore extends BufferDataStore implements DataStore
{
    private $buffer;
    private $store;

    private $keyPrefix;

    public function __construct(DataStore $store = null, $keyPrefix)
    {
        $this->buffer = array();
        $this->store = $store;

        $this->keyPrefix = $keyPrefix;
    }

    public function get(Model $object)
    {
        if(empty($this->buffer))
        {
            $identifiers = $object->getIdentifiers();
            $rootIdentifier = $identifiers[0];

            $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier;

            $cachedData = apcu_fetch($key);
            foreach($cachedData as $data)
            {
                $this->buffer[] = array(self::NODE => $data, self::STATE => DataState::NOT_CHANGED);
            }
        }

        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                $this->buffer[] = array(self::NODE => $data, self::STATE => DataState::NOT_CHANGED);
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