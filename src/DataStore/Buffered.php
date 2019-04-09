<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\Types\Status;
use battlecook\DataCookerException;
use battlecook\Types\PhpMemory;

final class Buffered extends AbstractStore implements IDataStore
{
    /**
     * @var $phpData PhpMemory
     */
    private static $phpData;

    private $store;

    public function __construct(IDataStore $store = null)
    {
        $this->store = $store;
        if (empty(self::$phpData) === true) {
            self::initialize();
        }
    }

    /**
     * @param $cacheKey
     * @param $object
     * @throws DataCookerException
     */
    private function setUp($cacheKey, $object)
    {
        if ($this->isCached($cacheKey) === false) {
            $this->setField($object);
            if ($this->store !== null) {

                $paramObject = new $object();
                if ($this->isGetAll($cacheKey, $object) === false) {
                    $rootIdentifier = $this->getIdentifierKeys($cacheKey)[0];
                    $paramObject->$rootIdentifier = $object->$rootIdentifier;
                }

                //todo if array is big, performance is raw. so need insertMulti which better than insert many time
                $objectArray = $this->store->get($paramObject);
                foreach ($objectArray as $object) {
                    $keys = $this->getIdentifierValues($cacheKey, $object);

                    if (count($keys) !== $this->getDepth($cacheKey)) {
                        throw new DataCookerException("invalid depth");
                    }
                    self::$phpData->insert($cacheKey, $keys, $object, Status::NONE);
                }
            }
        }
    }

    private function getChangeStatus($cacheKey, $keys)
    {
        $ret = self::$phpData->search($cacheKey, $keys);
        if (empty($ret) === false) {
            if ($this->hasAutoIncrement($cacheKey) === true) {
                $changedStatus = Status::getStatusWithAutoIncrement($ret[0]->getStatus(), Status::INSERTED);
            } else {
                $changedStatus = Status::getStatusWithoutAutoincrement($ret[0]->getStatus(), Status::INSERTED);
            }
        } else {
            $changedStatus = Status::INSERTED;
        }

        return $changedStatus;
    }

    /**
     * @param $object
     * @return mixed
     * @throws DataCookerException
     */
    public function add($object)
    {
        $cacheKey = get_class($object);
        $this->setUp($cacheKey, $object);
        $this->checkHaveAllFieldData($cacheKey, $object);

        $autoIncrement = $this->getAutoIncrementKey($cacheKey);
        if ($autoIncrement !== "" && empty($object->$autoIncrement) === true) {
            if ($this->store === null) {
                throw new DataCookerException("autoIncrement value is null");
            } else {
                $object = $this->store->add($object);
                if (empty($object->$autoIncrement) === true) {
                    throw new DataCookerException("autoIncrement value is null");
                }

                //todo have to have added $object, because when convert, can remove added data
                $keys = $this->getIdentifierValues($cacheKey, $object);
                self::$phpData->insert($cacheKey, $keys, $object, Status::NONE);
            }
        } else {
            $keys = $this->getIdentifierValues($cacheKey, $object);
            self::$phpData->insert($cacheKey, $keys, $object, $this->getChangeStatus($cacheKey, $keys));
        }

        return clone $object;
    }

    /**
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    public function get($object): array
    {
        $cacheKey = get_class($object);
        $this->setUp($cacheKey, $object);

        $keys = $this->getIdentifierValues($cacheKey, $object);

        if (count($keys) > $this->getDepth($cacheKey)) {
            throw new DataCookerException("");
        }
        $nodeArr = self::$phpData->search($cacheKey, $keys);

        $ret = array();
        foreach ($nodeArr as $node) {
            if ($node->getStatus() !== Status::DELETED) {
                $ret[] = $node->getData();
            }
        }

        return $ret;
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    public function set($object)
    {
        $cacheKey = get_class($object);
        $this->setUp($cacheKey, $object);
        $this->checkNoHaveAnyFieldData($cacheKey, $object);

        $keys = $this->getIdentifierValues($cacheKey, $object);
        if (count($keys) !== $this->getDepth($cacheKey)) {
            throw new DataCookerException("invalid depth");
        }
        self::$phpData->update($cacheKey, $keys, $object, $this->hasAutoIncrement($cacheKey));
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    public function remove($object)
    {
        $cacheKey = get_class($object);
        $this->setUp($cacheKey, $object);

        $keys = $this->getIdentifierValues($cacheKey, $object);

        if (count($keys) !== $this->getDepth($cacheKey)) {
            throw new DataCookerException("invalid depth");
        }
        self::$phpData->delete($cacheKey, $keys, $this->hasAutoIncrement($cacheKey));
    }

    /**
     * @param null $data
     * @throws DataCookerException
     */
    public function commit($data = null)
    {
        if ($data !== null) {
            throw new DataCookerException("BufferedDataStore can't commit to data");
        }

        $trees = self::$phpData->getTrees();
        if ($this->store !== null) {
            $this->store->commit($trees);
        }
    }

    public function convert()
    {
        parent::initialize();
        self::$phpData = new PhpMemory();
    }
}