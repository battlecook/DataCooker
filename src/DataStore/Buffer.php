<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\Data\Status;
use battlecook\DataCookerException;
use battlecook\DataStorage\Field;
use battlecook\DataStorage\Meta;
use battlecook\DataStorage\PhpMemory;
use battlecook\DataUtility\StoreTrait;

final class Buffer extends AbstractStore implements IDataStore
{
    use StoreTrait;

    /**
     * @var $phpData PhpMemory
     */
    private static $phpData;

    private $store;

    public function __construct(IDataStore $storage = null)
    {
        $this->store = $storage;
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
        $this->setMeta($object);

        if ($this->isCached($cacheKey) === false) {
            $this->cache($cacheKey);
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
                    $data = $this->getAttributeValues($cacheKey, $object);

                    if (count($keys) !== $this->getDepth($cacheKey)) {
                        throw new DataCookerException("invalid depth");
                    }
                    self::$phpData->insert($cacheKey, $keys, $data, $this->hasAutoIncrement($cacheKey));
                }
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
        $cacheKey = get_class($object);
        $this->setUp($cacheKey, $object);
        $this->checkHaveAllFieldData($cacheKey, $object);

        $object = $this->checkAutoIncrementAndAddIfNeed($cacheKey, $object);

        $keys = $this->getIdentifierValues($cacheKey, $object);
        if (count($keys) !== $this->getDepth($cacheKey)) {
            throw new DataCookerException("invalid depth");
        }

        $data = $this->getAttributeValues($cacheKey, $object);

        self::$phpData->insert($cacheKey, $keys, $data, $this->hasAutoIncrement($cacheKey));

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

        $identifierKeys = $this->getIdentifierKeys($cacheKey);
        $attributeKeys = $this->getAttributeKeys($cacheKey);

        $ret = array();
        foreach ($nodeArr as $node) {
            if ($node->getStatus() !== Status::DELETED) {
                //order dependency
                $identifierDataArr = $node->getKey();
                $attributeDataArr = $node->getData();

                $tmp = new $object();
                for ($i = 0; $i < count($identifierKeys); $i++) {
                    $key = $identifierKeys[$i];
                    $tmp->$key = $identifierDataArr[$i];
                }

                for ($i = 0; $i < count($attributeKeys); $i++) {
                    $key = $attributeKeys[$i];
                    $tmp->$key = $attributeDataArr[$i];
                }

                $ret[] = $tmp;
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
        $data = $this->getAttributeValues($cacheKey, $object);

        if (count($keys) !== $this->getDepth($cacheKey)) {
            throw new DataCookerException("invalid depth");
        }
        self::$phpData->update($cacheKey, $keys, $data, $this->hasAutoIncrement($cacheKey));
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
            throw new DataCookerException("BufferStore can't commit to data");
        }

        $trees = self::$phpData->getTrees();
        if($this->store !== null) {
            $this->store->commit($trees);
        }
    }

    public function rollback()
    {
        self::initialize();
    }

    public static function initialize()
    {
        parent::initialize();

        self::$phpData = new PhpMemory();
    }
}