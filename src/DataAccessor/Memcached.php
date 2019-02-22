<?php
declare(strict_types=1);

namespace battlecook\DataAccessor;


final class Memcached extends AbstractMeta implements IDataAccessor
{
    private $storage;
    private $memcached;

    /**
     * Memcached constructor.
     * @param IDataAccessor|null $storage
     * @param \battlecook\Config\Memcache[] $configArr
     */
    public function __construct(?IDataAccessor $storage, array $configArr)
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
        $keys = $this->getIdentifierValues($cacheKey, $object);


        $this->memcached->get();
        $this->memcached->getResultCode();

        return clone $object;
    }

    public function get($object): array
    {
        return array();
    }

    public function set($object)
    {
    }

    public function remove($object)
    {
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