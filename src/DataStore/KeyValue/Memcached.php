<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

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
     * @param \battlecook\Config\Memcache[] $configArr
     */
    public function __construct(?IDataStore $store, array $configArr)
    {
        $this->store = $store;

        $this->timeExpired = self::DEFAULT_EXPIRE_TIME;
        /*
        $this->memcached = new \Memcached();
        foreach ($configArr as $config) {
            $this->memcached->addServer($config->getIp(), $config->getPort());
        }
        */
    }

    public function add($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $this->checkField($cacheKey, $object);

        $cacheKey = get_class($object);
        $keys = $this->getIdentifierValues($cacheKey, $object);


        $this->memcached->get();
        $this->memcached->getResultCode();

        return clone $object;
    }

    public function get($object): array
    {
        $this->setMeta($object);
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
            }
        }
    }
}