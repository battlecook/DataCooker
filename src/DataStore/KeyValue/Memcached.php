<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\Config\Memcache;
use battlecook\DataCookerException;
use battlecook\DataStore\IDataStore;
use battlecook\DataStructure\Attribute;
use battlecook\DataUtility\StoreTrait;

final class Memcached extends AbstractKeyValue
{
    use StoreTrait;

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

            if ($this->memcached->addServer($config->getIp(), $config->getPort()) === false) {
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
        $tree = $this->memcached->get($key);
        if (empty($tree) === false) {
            throw new DataCookerException("data already exist");
        }

        return $object;
    }

    private function getKey(string $cacheKey, $object): string
    {
        $id1 = $this->getRootIdentifierKey($cacheKey);

        return $cacheKey . '\\' . $object->$id1;
    }

    private function getCurrentIdentifierValue($cacheKey, $object): array
    {
        $keys = array();
        foreach ($this->getIdentifierKeys($cacheKey) as $identifierKey) {
            if ($object->$identifierKey === null) {
                break;
            }
            $keys[] = $object->$identifierKey;
        }

        return $keys;
    }

    private function getIdentifierKeyByDepth($cacheKey, $depth): string
    {
        $identifierKeys = $this->getIdentifierKeys($cacheKey);

        return $identifierKeys[$depth];
    }

    private function searchRecursive(&$tree, array $keys, &$object): array
    {
        $searchKey = array_shift($keys);
        if ($searchKey !== null) {
            return $this->searchRecursive($tree[$searchKey], $keys, $object);
        } elseif ($tree instanceof Attribute) { //leafs
            $created = clone $object;
            $cacheKey = get_class($created);
            $attributeKeys = $this->getAttributeKeys($cacheKey);

            $attributeValues = $tree->getAttributes();
            foreach ($attributeKeys as $attributeKey) {
                $created->$attributeKey = array_shift($attributeValues);
            }

            return array($created);
        } else { //internals
            $cacheKey = get_class($object);

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator($tree),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $ret = array();
            $created = clone $object;
            $depth = count($this->getCurrentIdentifierValue($cacheKey, $created));
            foreach ($iterator as $key => $value) {
                $currentIdentifier = $this->getIdentifierKeyByDepth($cacheKey, $depth + $iterator->getDepth());
                $created->$currentIdentifier = $key;
                if ($value instanceof Attribute) { //leafs

                    $attributeKeys = $this->getAttributeKeys($cacheKey);
                    $attributeValues = $value->getAttributes();
                    foreach ($attributeKeys as $attributeKey) {
                        $created->$attributeKey = array_shift($attributeValues);
                    }

                    $ret[] = clone $created;
                }
            }

            return $ret;
        }
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

        $tree = $this->memcached->get($key);
        if (empty($tree) === true) {
            return array();
        }

        return $this->searchRecursive($tree, $this->getCurrentIdentifierValue($cacheKey, $object), $object);
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
                    //leave the log message
                    //need a policy whether rollback or not
                }
            }

            if ($this->store !== null) {
                $this->store->commit($data);
            }
        }
    }
}