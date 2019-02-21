<?php
declare(strict_types=1);

namespace battlecook\DataAccessor;

use battlecook\DataCookerException;
use battlecook\DataStorage\Field;

class AbstractMeta
{
    const VERSION_DELIMITER = "@dataCookerVersion";
    const IDENTIFIER_DELIMITER = "@dataCookerIdentifier";
    const AUTOINCREMENT_DELIMITER = "@dataCookerAutoIncrement";
    const ATTRIBUTE_DELIMITER = "@dataCookerAttribute";

    /**
     * @var $cachedFieldMap Field[]
     */
    protected $cachedFieldMap = array();

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

            $this->cachedFieldMap[$cacheKey] = new Field($identifiers, $autoIncrement, $attributes);
        } catch (\ReflectionException $e) {
            throw new DataCookerException("reflection error");
        }
    }

    protected function isGetAll($cacheKey, $object): bool
    {
        $id1 = $this->cachedFieldMap[$cacheKey]->getIdentifiers()[0];
        if($object->$id1 === null) {
            return true;
        }

        return false;
    }

    protected function getIdentifierValues($cacheKey, $object)
    {
        $keys = array();
        foreach ($this->cachedFieldMap[$cacheKey]->getIdentifiers() as $identifier) {
            $keys[] = $object->$identifier;
        }
        return $keys;
    }

    protected function getAttributeValues($cacheKey, $object)
    {
        $data = array();
        foreach ($this->cachedFieldMap[$cacheKey]->getAttributes() as $attribute) {
            $data[] = $object->$attribute;
        }
        return $data;
    }

    /**
     * @param $object
     * @return bool
     * @throws DataCookerException
     */
    protected function setMeta($object): bool
    {
        $cacheKey = get_class($object);
        if (isset($this->cachedFieldMap[$cacheKey]) === false) {
            $this->setField($object);
            return true;
        }
        return false;
    }


    /**
     * @param $cacheKey
     * @param $object
     * @throws DataCookerException
     */
    protected function checkField($cacheKey, $object)
    {
        $fields = $this->cachedFieldMap[$cacheKey]->getFields();
        foreach ($fields as $field) {
            //is_null 이 더 맞는거 같지만 exception 이 빠져버림
            if (empty($object->$field) === true) {
                throw new DataCookerException("fields don't fill all");
            }
        }
    }

    protected function getFieldsWithAutoIncrement(string $cacheKey)
    {
        return array_merge($this->cachedFieldMap[$cacheKey]->getIdentifiers(), $this->cachedFieldMap[$cacheKey]->getAttributes());
    }
}