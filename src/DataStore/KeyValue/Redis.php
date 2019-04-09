<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\DataCookerException;
use battlecook\DataStore\Buffered;
use battlecook\DataStore\IDataStore;

final class Redis extends AbstractKeyValue
{
    private $store;
    private $redis;

    /**
     * Redis constructor.
     * @param IDataStore|null $store
     * @param \battlecook\Config\Redis $config
     * @throws DataCookerException
     */
    public function __construct(?IDataStore $store, \battlecook\Config\Redis $config)
    {
        if($store instanceof Buffered) {
            throw new DataCookerException("BufferedDataStore can't be exist for other DataStore.");
        }
        $this->store = $store;

        $this->redis = new \Redis();
        if ($this->redis->pconnect($config->getIp(), $config->getPort()) === false) {
            throw new DataCookerException("redis connection failed");
        }

        if ($config->useAuth === true) {
            if ($this->redis->auth($config->password) === false) {
                throw new DataCookerException("redis auth failed");
            }
        }
        $ret = $this->redis->ping();
        if ($ret !== '+PONG') {
            throw new DataCookerException("redis ping failed");
        }
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
        $tree = $this->redis->get($key);
        if ($this->isEmpty($tree) === true) {
            $tree = array();
        } else {
            $tree = unserialize($tree);
        }

        $this->insertRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
        if ($this->redis->set($key, serialize($tree)) === false) {
            throw new DataCookerException(
                "redis set failed get last error : " . $this->redis->getLastError());
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
        $cacheKey = get_class($object);
        if ($this->isGetAll($cacheKey, $object) === true && $this->store === null) {
            throw new DataCookerException("Key Value store (Redis) doesn't provide GetAll");
        }

        $key = $this->getKey($cacheKey, $object);

        $tree = $this->redis->get($key);
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
        $tree = $this->redis->get($key);
        if ($this->isEmpty($tree) === true) {
            $tree = array();
        } else {
            $tree = unserialize($tree);
        }

        $this->updateRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
        $ret = $this->redis->set($key, serialize($tree));
        if ($ret === false) {
            throw new DataCookerException("redis set failed get last error : " . $this->redis->getLastError());
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
        $tree = $this->redis->get($key);
        if ($this->isEmpty($tree) === true) {
            $tree = array();
        } else {
            $tree = unserialize($tree);
        }
        $this->removeRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);

        if (empty($tree) === true) {
            $ret = $this->redis->delete($key);
            if ($ret === false) {
                throw new DataCookerException("redis set failed get last error : " . $this->redis->getLastError());
            }
        } else {
            $ret = $this->redis->set($key, serialize($tree));
            if ($ret === false) {
                throw new DataCookerException("redis set failed get last error : " . $this->redis->getLastError());
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
            $items = array();
            foreach ($data as $key => $tree) {
                $newTreeGroup = $tree;
                $this->travel($newTreeGroup);

                foreach ($newTreeGroup as $rootIdValue => $newTree) {
                    $items[$key . '\\' . $rootIdValue] = serialize(array($rootIdValue => $newTree));
                }
            }

            if (empty($items) === false) {
                $ret = $this->redis->mset($items);
                if ($ret === false) {
                    throw new DataCookerException(
                        "redis multi set failed : " . $this->redis->getLastError());
                }
            }

            if ($this->store !== null) {
                $this->store->commit($data);
            }
        }
    }
}