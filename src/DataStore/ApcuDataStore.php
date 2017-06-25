<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class ApcuDataStore implements DataStore
{
    private $store;

    private $keyPrefix;

    public function __construct(DataStore $store = null, $keyPrefix)
    {
        $this->store = $store;

        $this->keyPrefix = $keyPrefix;
    }

    public function get(Model $object)
    {
        $identifiers = $object->getIdentifiers();

        $ret = array();
        $count = 0;
        $depth = $this->getDepth($identifiers, $object);

        $rootIdentifier = $identifiers[0];
        $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier . ':' . $object->$rootIdentifier;

        $success = false;
        $dataList = apcu_fetch($key, $success);
        if($success)
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

    public function set(Model $object): int
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

    public function remove(Model $object): int
    {
        // TODO: Implement remove() method.
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
    public function removeMulti($objects): int
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

    public function reset()
    {

    }
}