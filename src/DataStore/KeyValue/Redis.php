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
    private $timeExpired;

    /**
     * Redis constructor.
     * @param array $option
     * @throws DataCookerException
     */
    public function __construct(array $option = array())
    {
        $hosts = array(array('ip' => 'localhost', 'port' => 6379, 'password' => ''));
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
                foreach($hosts as $key => $host) {
                    if(isset($host['ip']) === false) {
                        throw new DataCookerException("not exist IP");
                    }

                    if(isset($host['port']) === false) {
                        $hosts[$key]['port'] = 6379;
                    }


                    if(isset($host['password']) === false) {
                        $hosts[$key]['password'] = '';
                    }
                }
            }
            $this->store = $option['store'];
        }

        if(count($hosts) > 1) {
            try {
                $this->redis = new \RedisCluster(null, array('localhost:6379'));
            } catch (\RedisClusterException $e) {
                throw new DataCookerException("redis cluster exception");
            }
        } else {
            $this->redis = new \Redis();
            if ($this->redis->pconnect($hosts[0]['ip'], $hosts[0]['port']) === false) {
                throw new DataCookerException("redis connection failed");
            }

            if ($hosts[0]['password'] !== "") {
                if ($this->redis->auth($hosts[0]['password']) === false) {
                    throw new DataCookerException("redis auth failed");
                }
            }
            $ret = $this->redis->ping();
            if ($ret !== '+PONG') {
                throw new DataCookerException("redis ping failed");
            }
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
    public function commitAll($data = null)
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