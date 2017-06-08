<?php

namespace battlecook\DataStore;

use battlecook\DataCookerException;
use battlecook\DataObject\Model;
use Closure;
use Redis;

class RedisDataStore implements DataStore
{
    private $store;

    private $keyPrefix;
    /** @var redis $redis */
    private $redis;

    public function __construct(DataStore $store = null, Closure $closure, $keyPrefix)
    {
        $this->store = $store;
        $this->keyPrefix = $keyPrefix;
        $this->redis = $closure();
    }

    private function getKey(Model $object)
    {
        $identifiers = $object->getIdentifiers();
        $rootIdentifier = $identifiers[0];
        $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier . ':' . $object->$rootIdentifier;

        return $key;
    }

    public function get(Model $object)
    {
        $identifiers = $object->getIdentifiers();

        $ret = array();
        $depth = $this->getDepth($identifiers, $object);

        $key = $this->getKey($object);
        $dataList = $this->redis->sMembers($key);
        if($dataList)
        {
            foreach($dataList as $data)
            {
                $count = 0;
                $data = unserialize($data);
                foreach($identifiers as $identifier)
                {
                    if($data->$identifier === $object->$identifier)
                    {
                        $count++;
                    }
                    else
                    {
                        break;
                    }
                }

                if($count >= $depth)
                {
                    $ret[] = $data;
                }
            }
        }

        return $ret;
    }

    private function getDepth($identifiers, $object)
    {
        $depth = 0;
        foreach($identifiers as $identifier)
        {
            if(isset($object->$identifier))
            {
                $depth++;
            }
            else
            {
                break;
            }
        }

        return $depth;
    }

    /**
     * @param Model $object
     * @return int $rowCount;
     */
    public function set(Model $object)
    {
        if($this->store)
        {
            $this->store->set($object);
        }

        $key = $this->getKey($object);
        $rowCount = $this->redis->sAdd($key, $object);

        return $rowCount;
    }

    public function add(Model $object)
    {
        if($this->store)
        {
            $this->store->add($object);
        }

        $key = $this->getKey($object);
        $this->redis->sAdd($key, serialize($object));
    }

    /**
     * @param Model $object
     * @return int
     */
    public function remove(Model $object)
    {
        if($this->store)
        {
            $this->store->remove($object);
        }

        $key = $this->getKey($object);
        $this->redis->sRem($key, serialize($object));
    }

    public function flush()
    {
        // TODO: Implement flush() method.
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }

    public function setChangedAttributes(Model $object, $changedAttributes)
    {
        // TODO: Implement setChangedAttributes() method.
    }

    /**
     * @param Model[] $objects
     * @return int
     */
    public function removeMulti($objects)
    {
        // TODO: Implement removeMulti() method.
    }

    public function getLastAddedDataList()
    {
        if($this->store)
        {
            return $this->store->getLastAddedDataList();
        }

        throw new DataCookerException("RedisDataStore is not supported getLastAddedDataList.");
    }
}