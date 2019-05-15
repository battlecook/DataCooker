<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\DataCookerException;
use battlecook\DataStore\Buffered;
use battlecook\DataStore\IDataStore;

final class Memcached extends AbstractKeyValue
{
    private $store;
    private $memcached;
    private $timeExpired;

    /**
     * Memcached constructor.
     * @param array $option
     * @throws DataCookerException
     */
    public function __construct(array $option = array())
    {
        $hosts = array(array('ip' => 'localhost', 'port' => 11211));
        $this->timeExpired = self::DEFAULT_EXPIRE_TIME;
        if(empty($option) === false) {
            if(isset($option['store']) === true) {
                if(($option['store'] instanceof IDataStore) === false) {
                    throw new DataCookerException("store option have to be IDataStore instance.");
                }

                if($option['store'] instanceof Buffered) {
                    throw new DataCookerException("BufferedDataStore can't be exist for other DataStore.");
                }
                $this->store = $option['store'];
            }

            if(isset($option['hosts']) === true) {
                $hosts = $option['hosts'];
            }

            $this->store = $option['store'];
        }

        $this->memcached = new \Memcached();
        foreach ($hosts as $host) {
            if ($this->memcached->addServer($host['ip'], $host['port']) === false) {
                throw new DataCookerException("memcached addServer error");
            }
        }
    }

    private function isEmpty($tree): bool
    {
        return $tree === null;
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
        $tree = $this->memcached->get($key);
        if ($this->isEmpty($tree) === true) {
            $tree = array();
        }

        $this->insertRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
        if ($this->memcached->set($key, $tree, $this->timeExpired) === false) {
            throw new DataCookerException(
                "memcached set failed result code : " . $this->memcached->getResultCode()
                . " message : " . $this->memcached->getResultMessage());
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
    public function get($object)
    {
        $cacheKey = get_class($object);
        $this->setMeta($object);
        $this->checkHaveAllIdentifiersData($cacheKey, $object);
        $ret = $this->search($object);

        return $ret[0];
    }

    /**
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    public function search($object): array
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        if ($this->isGetAll($cacheKey, $object) === true && $this->store === null) {
            throw new DataCookerException("Key Value store (Memcached) doesn't provide GetAll");
        }

        $key = $this->getKey($cacheKey, $object);

        $tree = $this->memcached->get($key);
        if (empty($tree) === true) {
            return array();
        }

        return $this->searchRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
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
        $tree = $this->memcached->get($key);

        $this->updateRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
        $ret = $this->memcached->set($key, $tree, $this->timeExpired);
        if ($ret === false) {
            throw new DataCookerException(
                "memcached set failed result code : " . $this->memcached->getResultCode()
                . " message : " . $this->memcached->getResultMessage());
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
        $tree = $this->memcached->get($key);
        $this->removeRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);

        if (empty($tree) === true) {
            $ret = $this->memcached->delete($key);
            if ($ret === false) {
                throw new DataCookerException(
                    "memcached delete failed result code : " . $this->memcached->getResultCode()
                    . " message : " . $this->memcached->getResultMessage());
            }
        } else {
            $ret = $this->memcached->set($key, $tree, $this->timeExpired);
            if ($ret === false) {
                throw new DataCookerException(
                    "memcached set failed result code : " . $this->memcached->getResultCode()
                    . " message : " . $this->memcached->getResultMessage());
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
    public function commitAll($data = null)
    {
        if ($data !== null) {
            $items = array();
            foreach ($data as $key => $tree) {
                $newTreeGroup = $tree;
                $this->travel($newTreeGroup);

                foreach ($newTreeGroup as $rootIdValue => $newTree) {
                    $items[$key . '\\' . $rootIdValue] = array($rootIdValue => $newTree);
                }
            }

            if (empty($items) === false) {
                $ret = $this->memcached->setMulti($items, $this->timeExpired);
                if ($ret === false) {
                    throw new DataCookerException(
                        "memcached set failed result code : " . $this->memcached->getResultCode()
                        . " message : " . $this->memcached->getResultMessage());
                }
            }

            if ($this->store !== null) {
                $this->store->commit($data);
            }
        }
    }
}