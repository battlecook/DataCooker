<?php
declare(strict_types=1);

namespace battlecook\DataStore;


final class Memcached extends AbstractMeta implements IDataStore
{
    private $storage;
    private $memcached;

    /**
     * Memcached constructor.
     * @param IDataStore|null $storage
     * @param \battlecook\Config\Memcache[] $configArr
     */
    public function __construct(?IDataStore $storage, array $configArr)
    {
        $this->storage = $storage;

        $this->memcached = new \Memcached();
        foreach ($configArr as $config) {
            $this->memcached->addServer($config->getIp(), $config->getPort());
        }
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

    public function commit($data)
    {
        $items = array();
        foreach ($data as $key => $tree) {
            $items[$key] = $tree;
        }

        if (empty($items) === false) {
            //todo expire time must be in the option.
            $ret = $this->memcached->setMulti($items, 60 * 60 * 7);
            if ($ret === false) {
                //leave the log message
            }
        }
    }
}