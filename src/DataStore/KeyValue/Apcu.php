<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\DataCookerException;
use battlecook\DataStore\Buffer;
use battlecook\DataStore\IDataStore;

final class Apcu extends AbstractKeyValue
{
    private $store;

    /**
     * Apcu constructor.
     * @param IDataStore|null $store
     * @throws DataCookerException
     */
    public function __construct(?IDataStore $store)
    {
        if($store instanceof Buffer) {
            throw new DataCookerException("Buffer DataStore can't be exist for other DataStore.");
        }
        $this->store = $store;
    }

    private function isEmpty($tree): bool
    {
        return $tree === false;
    }

    /**
     * @param $object
     * @return mixed
     * @throws DataCookerException
     */
    public function add($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $this->checkHaveAllFieldData($cacheKey, $object);

        $haveToAddOtherStore = true;
        $autoIncrement = $this->getAutoIncrementKey($cacheKey);
        if ($autoIncrement !== "" && empty($object->$autoIncrement) === true) {
            if ($this->store === null) {
                throw new DataCookerException("autoIncrement value is null");
            } else {
                $object = $this->store->add($object);
                if (empty($object->$autoIncrement) === true) {
                    throw new DataCookerException("autoIncrement value is null");
                }
                $haveToAddOtherStore = false;
            }
        }

        $key = $this->getKey($cacheKey, $object);

        $tree = apcu_fetch($key);
        if ($this->isEmpty($tree) === true) {
            $tree = array();
        } else {
            $tree = unserialize($tree);
        }

        $this->insertRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
        if (apcu_store($key, serialize($tree)) === false) {
            throw new DataCookerException("apcu store failed ");
        }

        if ($this->store !== null && $haveToAddOtherStore === true) {
            $object = $this->store->add($object);
        }

        return clone $object;
    }

    /**
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    public function get($object): array
    {
        $cacheKey = get_class($object);
        if ($this->isGetAll($cacheKey, $object) === true && $this->store === null) {
            throw new DataCookerException("Key Value store (Apcu) doesn't provide GetAll");
        }

        $key = $this->getKey($cacheKey, $object);

        $tree = apcu_fetch($key);
        if ($this->isEmpty($tree) === true) {
            return array();
        } else {
            $tree = unserialize($tree);
            return $this->searchRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
        }
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    public function set($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $this->checkHaveAllFieldData($cacheKey, $object);

        $key = $this->getKey($cacheKey, $object);
        $tree = apcu_fetch($key);
        if ($this->isEmpty($tree) === true) {
            $tree = array();
        } else {
            $tree = unserialize($tree);
        }

        $this->updateRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
        if (apcu_store($key, serialize($tree)) === false) {
            throw new DataCookerException("apcu_store failed ");
        }

        if ($this->store !== null) {
            $this->store->set($object);
        }
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    public function remove($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $key = $this->getKey($cacheKey, $object);
        $tree = apcu_fetch($key);
        if ($this->isEmpty($tree) === true) {
            $tree = array();
        } else {
            $tree = unserialize($tree);
        }
        $this->removeRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);

        if (empty($tree) === true) {
            $ret = apcu_delete($key);
            if ($ret === false) {
                throw new DataCookerException("redis set failed");
            }
        } else {
            $ret = apcu_store($key, serialize($tree));
            if ($ret === false) {
                throw new DataCookerException("apcu_store failed");
            }
        }

        if ($this->store !== null) {
            $this->store->remove($object);
        }
    }

    /**
     * @param null $data
     * @throws DataCookerException
     */
    public function commit($data = null)
    {
        if ($data !== null) {
            foreach ($data as $key => $tree) {
                $newTreeGroup = $tree;
                $this->travel($newTreeGroup);

                foreach ($newTreeGroup as $rootIdValue => $newTree) {
                    $ret = apcu_store($key . '\\' . $rootIdValue, serialize(array($rootIdValue => $newTree)));
                    if($ret === false) {
                        throw new DataCookerException("apcu_store failed");
                    }
                }
            }

            if ($this->store !== null) {
                $this->store->commit($data);
            }
        }
    }
}