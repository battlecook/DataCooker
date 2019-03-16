<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\Data\Status;
use battlecook\DataCookerException;
use battlecook\DataStorage\Field;
use battlecook\DataStorage\Meta;
use battlecook\DataStorage\PhpMemory;
use battlecook\DataStore\KeyValue\AbstractKeyValue;
use battlecook\DataUtility\TreeTrait;

final class Buffer extends AbstractMeta implements IDataStore
{
    use TreeTrait;

    /**
     * @var $phpData PhpMemory
     */
    private static $phpData;
    private static $cache;

    private $store;

    public function __construct(IDataStore $storage = null)
    {
        $this->store = $storage;
        if (empty(self::$phpData) === true) {
            self::initialize();
        }
    }

    /**
     * @param string $cacheKey
     * @param $object
     * @throws DataCookerException
     */
    private function cacheData(string $cacheKey, $object)
    {
        if (isset(self::$cache[$cacheKey]) === false) {

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
                    self::$phpData->insert($cacheKey, $keys, $data);
                }
            }

            self::$cache[$cacheKey] = true;
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
        if (self::$phpData->hasData($cacheKey) === false) {
            self::$phpData->addMetaData(new Meta(new Field($this->getIdentifierKeys($cacheKey),
                $this->getAutoIncrementKey($cacheKey), $this->getAttributeKeys($cacheKey)), $cacheKey));
        }

        $this->cacheData($cacheKey, $object);
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
                //rollback 을 위해 적어 둬야 하나 ...
                $object = $this->store->add($object);
                if (empty($object->$autoIncrement) === true) {
                    throw new DataCookerException("autoIncrement value is null");
                }
            }
        }

        $keys = $this->getIdentifierValues($cacheKey, $object);
        $data = $this->getAttributeValues($cacheKey, $object);
        self::$phpData->insert($cacheKey, $keys, $data);

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

        self::$phpData->update($cacheKey, $keys, $data);
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

        self::$phpData->delete($cacheKey, $keys);
    }

    public function commit($data = null)
    {
        if ($data === null) {
            $trees = self::$phpData->getTrees();
            if ($this->store instanceof AbstractKeyValue) {
                $this->store->commit($trees);
            } else {

                $leafNodes = array();
                foreach ($trees as $className => $tree) {
                    $meta = self::$phpData->getMetaData($className);
                    array_walk_recursive($trees, function ($data) use ($className, $meta, &$leafNodes) {
                        $object = new $className();
                        array_map(function ($key, $value) use ($object) {
                            $object->$key = $value;
                        }, $meta->getField()->getIdentifiers(), $data->getKey());
                        array_map(function ($key, $value) use ($object) {
                            $object->$key = $value;
                        }, $meta->getField()->getAttributes(), $data->getData());

                        if ($data->getStatus() !== Status::NONE) {
                            $leafNodes[$data->getStatus()] = $object;
                        }
                    });
                }

                $this->store->commit($leafNodes);
            }
        }

        self::initialize();
    }

    public function rollback()
    {
        self::initialize();
    }

    public static function initialize()
    {
        parent::initialize();

        self::$phpData = new PhpMemory();
        self::$cache = null;
    }
}