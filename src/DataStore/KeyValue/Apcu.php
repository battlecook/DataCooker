<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\DataCookerException;
use battlecook\DataStore\IDataStore;

final class Apcu extends AbstractKeyValue
{
    private $store;

    /**
     * Redis constructor.
     * @param IDataStore|null $store
     */
    public function __construct(?IDataStore $store)
    {
        $this->store = $store;
    }

    public function add($object)
    {
        return clone $object;
    }

    public function get($object): array
    {
    }

    public function set($object)
    {
    }

    public function remove($object)
    {
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