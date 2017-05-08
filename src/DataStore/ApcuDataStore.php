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
        $rootIdentifier = $identifiers[0];
        $key = $this->keyPrefix . '/' . $object->getShortName() . '/' . 'v:' . $object->getVersion() . '/' . $rootIdentifier;

        $ret = array();
        $count = 0;
        $depth = $this->getDepth($identifiers, $object);

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
        else
        {
            //뒷단 store에서 채워야 하는가 ?
            //apcu_store($key, $this->buffer);
            return $ret;

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