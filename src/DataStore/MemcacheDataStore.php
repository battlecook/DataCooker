<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class MemcacheDataStore extends BufferDataStore implements DataStore
{
    private $buffer;
    private $store;

    private $keyPrefix;
    private $memcache;

    public function __construct(DataStore $store = null, \Memcache $memcache, $keyPrefix)
    {
        $this->buffer = array('index' => array(), 'data' => array());
        $this->store = $store;
        $this->memcache = $memcache;

        $this->keyPrefix = $keyPrefix;
    }

    public function get(Model $object)
    {
        if(empty($this->buffer))
        {
            $identifiers = $object->getIdentifiers();
            $rootIdentifier = $identifiers[0];

            $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier;

            $cachedData = $this->memcache->get($key);
            foreach($cachedData as $data)
            {
                parent::addClear($data);
            }
        }

        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                parent::addClear($data);
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
        $rowCount = 0;
        $bufferedData = $this->get($object);
        if(!empty($bufferedData))
        {
            $data = $bufferedData[0];
            $attributes = $object->getAttributes();
            $isDirty = false;
            foreach($attributes as $attribute)
            {
                if($data->$attribute !== $object->$attribute)
                {
                    $isDirty = true;
                    break;
                }
            }
            if($isDirty)
            {
                foreach($this->buffer as $key => $value)
                {
                    if($value[self::DATA] === $data)
                    {
                        $this->buffer[$key][self::DATA] = $object;
                        if($value[self::STATE] !== DataState::DIRTY_ADD)
                        {
                            $this->buffer[$key][self::STATE] = DataState::DIRTY_SET;
                        }
                        $rowCount++;
                        break;
                    }
                }
            }

            if($this->store)
            {
                $this->store->set($object);
            }
        }

        return $rowCount;
    }

    /**
     * @param Model $object
     * @return Model[];
     */
    public function add(Model $object)
    {
        parent::add($object);
        if($this->store)
        {
            $this->store->add($object);
        }
    }

    /**
     * @param Model $object
     * @return int
     */
    public function remove(Model $object)
    {

        $rowCount = parent::remove($object);
        if($rowCount > 0 && $this->store)
        {
            $this->store->remove($object);
        }

        return $rowCount;
    }

    public function flush()
    {
        foreach($this->buffer as $data)
        {

        }
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }
}