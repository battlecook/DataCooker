<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\Config\Memcache;
use battlecook\DataCookerException;
use battlecook\DataStore\IDataStore;

final class Memcached extends AbstractKeyValue
{
    private $store;
    private $memcached;

    const DEFAULT_EXPIRE_TIME = 60 * 60 * 7;

    //todo expire time must be in the option.
    // have expire time option with each object
    private $timeExpired;

    /**
     * Memcached constructor.
     * @param IDataStore|null $store
     * @param Memcache[] $configArr
     * @throws DataCookerException
     */
    public function __construct(?IDataStore $store, array $configArr)
    {
        $this->store = $store;

        $this->timeExpired = self::DEFAULT_EXPIRE_TIME;

        $this->memcached = new \Memcached();
        foreach ($configArr as $config) {

            if ( $this->memcached->addServer($config->getIp(), $config->getPort()) === false) {
                throw new DataCookerException("connection error");
            }
        }

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

        $object = $this->checkAutoIncrementAndAddIfNeed($cacheKey, $object);

        $key = $this->getKey($cacheKey, $object);

        $this->memcached->get($key);

/*
        $cacheKey = get_class($object);
        $keys = $this->getIdentifierValues($cacheKey, $object);


        $this->memcached->get();
        $this->memcached->getResultCode();
*/
        return $object;
    }

    private function getKey(string $cacheKey, $object): string
    {
        $id1 = $this->getRootIdentifierKey($cacheKey);

        return $cacheKey . '\\' . $id1 . ':' . $object->$id1;
    }

    /**
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    public function get($object): array
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        if ($this->isGetAll($cacheKey, $object) === true && $this->store === null) {
            throw new DataCookerException("Key Value store (Memcached) doesn't provide GetAll");
        }

        $key = $this->getKey($cacheKey, $object);

        $ret = $this->memcached->get($key);

        return array();
    }

    public function set($object)
    {
        $this->setMeta($object);
    }

    public function remove($object)
    {
        $this->setMeta($object);
    }

    public function commit($data = null)
    {
        if($data !== null) {
            $items = array();
            foreach ($data as $key => $tree) {
                $created = $tree;
                $this->travel($created);
                $items[$key] = $created;
            }

            if (empty($items) === false) {
                $ret = $this->memcached->setMulti($items, $this->timeExpired);
                if ($ret === false) {
                    //leave the log message
                    //need a policy whether rollback or not
                }
            }

            if($this->store !== null) {
                $this->store->commit($data);
            }
        }
    }
}