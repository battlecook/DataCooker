<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\Types\Status;
use battlecook\DataCookerException;
use battlecook\Types\PhpMemory;

final class Buffered extends AbstractStore implements IDataStore
{
    /**
     * @var $bufferedData PhpMemory
     */
    private static $bufferedData;

    private static $addedObjectGroup = array();

    private $store;

    public function __construct(IDataStore $store = null)
    {
        $this->store = $store;
        if (empty(self::$bufferedData) === true) {
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
                $objectArray = $this->store->search($paramObject);
                foreach ($objectArray as $object) {
                    $keys = $this->getIdentifierValues($cacheKey, $object);

                    if (count($keys) !== $this->getDepth($cacheKey)) {
                        throw new DataCookerException("invalid depth");
                    }
                    self::$bufferedData->insert($cacheKey, $keys, $object, Status::NONE);
                }
            }
        }
    }

    /**
     * @param $cacheKey
     * @param $keys
     * @return int
     * @throws DataCookerException
     */
    private function getChangeStatus($cacheKey, $keys)
    {
        $ret = self::$bufferedData->search($cacheKey, $keys);
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

                $addedObjectGroup[] = $object;

                $keys = $this->getIdentifierValues($cacheKey, $object);
                self::$bufferedData->insert($cacheKey, $keys, $object, Status::NONE);
            }
        } else {
            $keys = $this->getIdentifierValues($cacheKey, $object);
            self::$bufferedData->insert($cacheKey, $keys, $object, $this->getChangeStatus($cacheKey, $keys));
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
        $this->setUp($cacheKey, $object);

        $keys = $this->getIdentifierValues($cacheKey, $object);

        if (count($keys) > $this->getDepth($cacheKey)) {
            throw new DataCookerException("");
        }
        $nodeArr = self::$bufferedData->search($cacheKey, $keys);

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
        self::$bufferedData->update($cacheKey, $keys, $object, $this->hasAutoIncrement($cacheKey));
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
        self::$bufferedData->delete($cacheKey, $keys, $this->hasAutoIncrement($cacheKey));
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

        $trees = self::$bufferedData->getTrees();
        if ($this->store !== null) {
            $this->store->commit($trees);
        }

        $this->initialize();
    }

    public function convert()
    {
        foreach(self::$addedObjectGroup as $addedObject) {
            $this->store->remove($addedObject);
        }

        $this->initialize();
    }

    private function initialize() {

        self::$addedObjectGroup = array();
        self::$bufferedData = new PhpMemory();
    }
}