<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class ApcuDataStore extends BufferDataStore implements DataStore
{
    private $store;

    private $keyPrefix;

    public function __construct(DataStore $store = null, $keyPrefix)
    {
        parent::__construct();

        $this->buffer = array();
        $this->store = $store;

        $this->keyPrefix = $keyPrefix;
    }

    public function get(Model $object)
    {
        $identifiers = $object->getIdentifiers();
        $rootIdentifier = $identifiers[0];
        $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier;

        if(empty($this->buffer))
        {
            $isSuccess = false;
            $cachedData = apcu_fetch($key, $isSuccess);
            if($isSuccess)
            {
                foreach($cachedData as $data)
                {
                    parent::addClear($data);
                }
            }
        }

        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                parent::addClear($data);
            }
            //have to filled at apc from buffer
            apcu_store($key, $this->buffer);
        }


        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                $this->addClear($data);
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

    public function flush($data)
    {
        // TODO: Implement flush() method.
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }
}