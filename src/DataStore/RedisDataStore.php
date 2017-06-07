<?php

namespace battlecook\DataStore;

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

    public function get(Model $object)
    {
        $identifiers = $object->getIdentifiers();

        $ret = array();
        $count = 0;
        $depth = $this->getDepth($identifiers, $object);

        $rootIdentifier = $identifiers[0];

        $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier . ':' . $object->$rootIdentifier;

        $dataList = $this->redis->get($key);
        if($dataList)
        {
            foreach($dataList as $data)
            {
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
}