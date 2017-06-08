<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;
use Closure;

class MemcacheDataStore implements DataStore
{
    private $store;

    private $keyPrefix;
    /** @var \Memcache $memcache */
    private $memcache;

    public function __construct(DataStore $store = null, Closure $closure, $keyPrefix)
    {
        $this->store = $store;
        $this->keyPrefix = $keyPrefix;
        $this->memcache = $closure();
    }

    public function get(Model $object)
    {
        $identifiers = $object->getIdentifiers();

        $ret = array();
        $count = 0;
        $depth = $this->getDepth($identifiers, $object);

        $rootIdentifier = $identifiers[0];

        $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier . ':' . $object->$rootIdentifier;

        $dataList = $this->memcache->get($key);
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
        $identifiers = $object->getIdentifiers();
        $rootIdentifier = $identifiers[0];

        $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier . ':' . $object->$rootIdentifier;

        $dataList = $this->memcache->get($key);
        if($dataList)
        {

        }
        else
        {
            return 0;
        }
    }

    /**
     * @param Model $object
     * @return Model[];
     */
    public function add(Model $object)
    {
    }

    /**
     * @param Model $object
     * @return int
     */
    public function remove(Model $object)
    {
    }

    public function flush()
    {
        throw new \Exception('not use this function');
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }

    /**
     * @param Model[] $objects
     * @return int
     */
    public function removeMulti($objects)
    {
        // TODO: Implement removeMulti() method.
    }

    public function setChangedAttributes(Model $object, $changedAttributes)
    {
        // TODO: Implement setChangedAttributes() method.
    }

    public function getLastAddedDataList()
    {
        // TODO: Implement getLastAddedDataList() method.
    }
}