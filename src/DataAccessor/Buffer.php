<?php
declare(strict_types=1);

namespace battlecook\DataAccessor;

use battlecook\Data\Status;
use battlecook\DataCookerException;
use battlecook\DataStorage\Field;
use battlecook\DataStorage\Meta;
use battlecook\DataStorage\PhpMemory;

final class Buffer extends AbstractMeta implements IDataAccessor
{
    /**
     * @var $phpData PhpMemory
     */
    private static $phpData;

    private $storage;

    public function __construct(IDataAccessor $storage = null)
    {
        $this->storage = $storage;
        if (empty(self::$phpData) === true) {
            $this->initialize();
        }
    }

    /**
     * @param $cacheKey
     * @param $object
     * @throws DataCookerException
     */
    private function setUpMeta($cacheKey, $object)
    {
        if ($this->setMeta($object) === true) {
            $identifiers = $this->cachedFieldMap[$cacheKey]->getIdentifiers();
            $autoIncrement = $this->cachedFieldMap[$cacheKey]->getAutoIncrement();
            $attributes = $this->cachedFieldMap[$cacheKey]->getAttributes();
            self::$phpData->addMetaData(new Meta(new Field($identifiers, $autoIncrement, $attributes), $cacheKey));
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
        $this->setUpMeta($cacheKey, $object);
        $this->checkField($cacheKey, $object);

        $autoIncrement = $this->cachedFieldMap[$cacheKey]->getAutoIncrement();
        if ($autoIncrement !== "" && empty($object->$autoIncrement) === true) {
            if ($this->storage === null) {
                throw new DataCookerException("autoIncrement value is null");
            } else {
                //rollback 을 위해 적어 둬야 하나 ...
                $object = $this->storage->add($object);
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
        $this->setUpMeta($cacheKey, $object);

        $keys = $this->getIdentifierValues($cacheKey, $object);
        $nodeArr = self::$phpData->search($cacheKey, $keys);

        $identifierKeys = $this->cachedFieldMap[$cacheKey]->getIdentifiers();
        $attributeKeys = $this->cachedFieldMap[$cacheKey]->getAttributes();

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
        $this->setUpMeta($cacheKey, $object);
        $this->checkField($cacheKey, $object);

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
        $this->setUpMeta($cacheKey, $object);

        $keys = $this->getIdentifierValues($cacheKey, $object);

        self::$phpData->delete($cacheKey, $keys);
    }

    public function commit($data)
    {
        //if other storage is key value storage ( redis, memcache etc), send to data for tree structure not object structure
    }

    public function rollback()
    {
    }

    public function initialize()
    {
        self::$phpData = new PhpMemory();
    }
}