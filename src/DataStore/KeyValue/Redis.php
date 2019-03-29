<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\DataCookerException;
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

    public function add($object)
    {
        // TODO: Implement add() method.
    }

    public function get($object): array
    {
        // TODO: Implement get() method.
    }

    public function set($object)
    {
        // TODO: Implement set() method.
    }

    public function remove($object)
    {
        // TODO: Implement remove() method.
    }

    public function commit($data = null)
    {
        // TODO: Implement commit() method.
    }
}