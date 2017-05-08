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

        $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier;

        $cachedData = $this->memcache->get($key);
        if($cachedData)
        {

            foreach($cachedData as $data)
            {
                //parent::addClear($data);
            }

        }
        else
        {

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
}