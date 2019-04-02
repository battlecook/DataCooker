<?php

namespace battlecook\DataUtility;

use battlecook\DataCookerException;
use battlecook\DataStorage\Field;
use battlecook\DataStorage\Meta;

trait MetaTrait
{
    /**
     * @var $cachedMetaMap Meta[]
     */
    protected static $cachedMetaMap;

    protected function cache($cacheKey)
    {
        self::$cachedMetaMap[$cacheKey] = new Meta(new Field($this->getIdentifierKeys($cacheKey),
            $this->getAutoIncrementKey($cacheKey), $this->getAttributeKeys($cacheKey)), $cacheKey);
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    protected function setField($object)
    {
        try {
            $identifiers = array();
            $autoIncrement = "";
            $attributes = array();

            $cacheKey = get_class($object);
            $rc = new \ReflectionClass($cacheKey);
            $properties = $rc->getProperties();
            foreach ($properties as $property) {
                $doc = $property->getDocComment();
                if ($doc === false) {
                    continue;
                }

                if (stripos($doc, self::VERSION_DELIMITER)) {

                } else {
                    if (stripos($doc, self::IDENTIFIER_DELIMITER)) {
                        if (stripos($doc, self::AUTOINCREMENT_DELIMITER)) {
                            if ($autoIncrement !== "") {
                                throw new DataCookerException("auto increment have to has only one");
                            }
                            $autoIncrement = $property->getName();
                            if (isset($object->$autoIncrement) === true && is_int($object->$autoIncrement) === false) {
                                throw new DataCookerException("auto increment have to has integer type");
                            }
                        }
                        $identifiers[] = $property->getName();
                    } else {
                        if (stripos($doc, self::ATTRIBUTE_DELIMITER)) {
                            if (stripos($doc, self::AUTOINCREMENT_DELIMITER)) {
                                if ($autoIncrement !== "") {
                                    throw new DataCookerException("auto increment have to has only one");
                                }
                                $autoIncrement = $property->getName();
                                if (isset($object->$autoIncrement) === true && is_int($object->$autoIncrement) === false) {
                                    throw new DataCookerException("auto increment have to has integer type");
                                }
                            }
                            $attributes[] = $property->getName();
                        } else {
                            if (stripos($doc, self::AUTOINCREMENT_DELIMITER)) {
                                if (stripos($doc, self::IDENTIFIER_DELIMITER) === false && stripos($doc,
                                        self::ATTRIBUTE_DELIMITER) === false) {
                                    throw new DataCookerException("autoincrement must be included in identifiers or attribute");
                                }
                            }
                        }
                    }
                }
            }

            if (empty($identifiers) === true) {
                throw new DataCookerException("identifiers is empty");
            }

            self::$cachedMetaMap[$cacheKey] = new Meta(new Field($identifiers, $autoIncrement, $attributes), $cacheKey);
        } catch (\ReflectionException $e) {
            throw new DataCookerException("reflection error");
        }
    }

    protected function getRootIdentifierKey(string $cacheKey): string
    {
        return self::$cachedMetaMap[$cacheKey]->getField()->getIdentifiers()[0];
    }

    protected function isGetAll(string $cacheKey, $object): bool
    {
        $id1 = $this->getRootIdentifierKey($cacheKey);
        if ($object->$id1 === null) {
            return true;
        }

        return false;
    }

    protected function isCached(string $cacheKey)
    {
        return isset(self::$cachedMetaMap[$cacheKey]);
    }

    protected function hasAutoIncrement(string $cacheKey): bool
    {
        return self::$cachedMetaMap[$cacheKey]->hasAutoIncrement();
    }

    protected function getIdentifierKeys(string $cacheKey): array
    {
        return self::$cachedMetaMap[$cacheKey]->getField()->getIdentifiers();
    }

    protected function getIdentifierValues(string $cacheKey, $object)
    {
        $keys = array();
        foreach (self::$cachedMetaMap[$cacheKey]->getField()->getIdentifiers() as $identifier) {
            $keys[] = $object->$identifier;
        }
        return $keys;
    }

    protected function getAttributeKeys(string $cacheKey): array
    {
        return self::$cachedMetaMap[$cacheKey]->getField()->getAttributes();
    }

    protected function getAttributeValues(string $cacheKey, $object)
    {
        $data = array();
        foreach (self::$cachedMetaMap[$cacheKey]->getField()->getAttributes() as $attribute) {
            $data[] = $object->$attribute;
        }
        return $data;
    }

    protected function getFieldKeys(string $cacheKey): array
    {
        return self::$cachedMetaMap[$cacheKey]->getField()->getFields();
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    protected function setMeta($object)
    {
        $cacheKey = get_class($object);
        if (isset(self::$cachedMetaMap[$cacheKey]) === false) {
            $this->setField($object);
        }
    }

    /**
     * @param $cacheKey
     * @param $object
     * @throws DataCookerException
     */
    protected function checkHaveAllFieldData(string $cacheKey, $object)
    {
        $fields = self::$cachedMetaMap[$cacheKey]->getField()->getFields();
        foreach ($fields as $field) {
            if ($object->$field === null) {
                throw new DataCookerException("fields don't fill all");
            }
        }
    }

    protected function haveOneDataAtLeast(string $cacheKey, $object): bool
    {
        $fields = self::$cachedMetaMap[$cacheKey]->getField()->getFields();
        foreach ($fields as $field) {
            //is_null 이 더 맞는거 같지만 exception 이 빠져버림
            if ($object->$field !== null) {
                return true;
            }
        }
        return false;
    }

    protected function getDepth(string $cacheKey)
    {
        return self::$cachedMetaMap[$cacheKey]->getDepth();
    }

    /**
     * @param $cacheKey
     * @param $object
     * @throws DataCookerException
     */
    protected function checkNoHaveAnyFieldData(string $cacheKey, $object)
    {
        if ($this->haveOneDataAtLeast($cacheKey, $object) === false) {
            throw new DataCookerException();
        }
    }

    protected function getAutoIncrementKey(string $cacheKey)
    {
        return self::$cachedMetaMap[$cacheKey]->getField()->getAutoIncrement();
    }

    protected function getFieldKeysWithAutoIncrement(string $cacheKey)
    {
        return array_merge(self::$cachedMetaMap[$cacheKey]->getField()->getIdentifiers(),
            self::$cachedMetaMap[$cacheKey]->getField()->getAttributes());
    }

    public function getMetaData(string $dataName)
    {
        return self::$cachedMetaMap[$dataName];
    }

    protected static function initialize()
    {
        self::$cachedMetaMap = array();
    }
}