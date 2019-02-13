<?php
declare(strict_types=1);

namespace battlecook\DataAccessor;

use battlecook\Data\Status;
use battlecook\DataCookerException;
use battlecook\DataStorage\Field;
use battlecook\DataStorage\Meta;
use battlecook\DataStorage\PhpMemory;

final class Buffer implements IDataAccessor
{
    const VERSION_DELIMITER = "@dataCookerVersion";
    const IDENTIFIER_DELIMITER = "@dataCookerIdentifier";
    const AUTOINCREMENT_DELIMITER = "@dataCookerAutoIncrement";
    const ATTRIBUTE_DELIMITER = "@dataCookerAttribute";

    /**
     * @var $phpData PhpMemory
     */
    private static $phpData;

    /**
     * @var $cachedFieldMap Field[]
     */
    private $cachedFieldMap = array();

    private $storage;

    public function __construct(IDataAccessor $storage = null)
    {
        $this->storage = $storage;
        if(empty(self::$phpData) === true)
        {
            $this->initialize();
        }
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    private function setField($object)
    {
        try
        {
            $identifiers = array();
            $autoIncrement = "";
            $attributes = array();

            $cacheKey = get_class($object);
            $rc = new \ReflectionClass($cacheKey);
            $properties = $rc->getProperties();
            foreach($properties as $property)
            {
                $doc = $property->getDocComment();
                if($doc === false)
                {
                    continue;
                }

                if(stripos($doc, self::VERSION_DELIMITER))
                {

                }
                else if(stripos($doc, self::IDENTIFIER_DELIMITER))
                {
                    if(stripos($doc, self::AUTOINCREMENT_DELIMITER))
                    {
                        if($autoIncrement !== "")
                        {
                            throw new DataCookerException("auto increment have to has only one");
                        }
                        $autoIncrement = $property->getName();
                        if(isset($object->$autoIncrement) === true && is_int($object->$autoIncrement) === false)
                        {
                            throw new DataCookerException("auto increment have to has integer type");
                        }
                    }
                    $identifiers[] = $property->getName();
                }
                else if(stripos($doc, self::ATTRIBUTE_DELIMITER))
                {
                    if(stripos($doc, self::AUTOINCREMENT_DELIMITER))
                    {
                        if($autoIncrement !== "")
                        {
                            throw new DataCookerException("auto increment have to has only one");
                        }
                        $autoIncrement = $property->getName();
                        if(isset($object->$autoIncrement) === true && is_int($object->$autoIncrement) === false)
                        {
                            throw new DataCookerException("auto increment have to has integer type");
                        }
                    }
                    $attributes[] = $property->getName();
                }
                else if(stripos($doc, self::AUTOINCREMENT_DELIMITER))
                {
                    if(stripos($doc, self::IDENTIFIER_DELIMITER) === false && stripos($doc, self::ATTRIBUTE_DELIMITER) === false)
                    {
                        throw new DataCookerException("autoincrement must be included in identifiers or attribute");
                    }
                }
            }

            if(empty($identifiers) === true)
            {
                throw new DataCookerException("identifiers is empty");
            }

            $this->cachedFieldMap[$cacheKey] = new Field($identifiers, $autoIncrement, $attributes);
        }
        catch(\ReflectionException $e)
        {
            throw new DataCookerException("reflection error");
        }
    }

    private function getIdentifierValues($cacheKey, $object)
    {
        $keys = array();
        foreach($this->cachedFieldMap[$cacheKey]->getIdentifiers() as $identifier)
        {
            $keys[] = $object->$identifier;
        }
        return $keys;
    }

    private function getAttributeValues($cacheKey, $object)
    {
        $data = array();
        foreach($this->cachedFieldMap[$cacheKey]->getAttributes() as $attribute)
        {
            $data[] = $object->$attribute;
        }
        return $data;
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    private function setMeta($object)
    {
        $cacheKey = get_class($object);
        if(isset($this->cachedFieldMap[$cacheKey]) === false)
        {
            $this->setField($object);

            $identifiers = $this->cachedFieldMap[$cacheKey]->getIdentifiers();
            $autoIncrement = $this->cachedFieldMap[$cacheKey]->getAutoIncrement();
            $attributes = $this->cachedFieldMap[$cacheKey]->getAttributes();
            self::$phpData->addMetaData(new Meta(new Field($identifiers, $autoIncrement, $attributes), $cacheKey));
        }
    }

    /**
     * @param $cacheKey
     * @param $object
     * @throws DataCookerException
     */
    private function checkField($cacheKey, $object)
    {
        $fields = $this->cachedFieldMap[$cacheKey]->getFields();
        foreach($fields as $field)
        {
            //is_null 이 더 맞는거 같지만 exception 이 빠져버림
            if(empty($object->$field) === true)
            {
                throw new DataCookerException("fields don't fill all");
            }
        }
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    public function add($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $this->checkField($cacheKey, $object);

        $autoIncrement = $this->cachedFieldMap[$cacheKey]->getAutoIncrement();
        if($autoIncrement !== "" && empty($object->$autoIncrement) === true)
        {
            if($this->storage === null)
            {
                throw new DataCookerException("autoIncrement value is null");
            }
            else
            {
                //rollback 을 위해 적어 둬야 하나 ...
                $object = $this->storage->add($object);
                if(empty($object->$autoIncrement) === true)
                {
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
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $keys = $this->getIdentifierValues($cacheKey, $object);

        $nodeArr = self::$phpData->search($cacheKey, $keys);

        $identifierKeys = $this->cachedFieldMap[$cacheKey]->getIdentifiers();
        $attributeKeys = $this->cachedFieldMap[$cacheKey]->getAttributes();

        $ret = array();
        foreach($nodeArr as $node)
        {
            if($node->getStatus() !== Status::DELETED)
            {
                //order dependency
                $identifierDataArr = $node->getKey();

                $attributeDataArr = $node->getData();

                $tmp = new $object();
                for($i=0; $i<count($identifierKeys); $i++)
                {
                    $key = $identifierKeys[$i];
                    $tmp->$key = $identifierDataArr[$i];
                }

                for($i=0; $i<count($attributeKeys); $i++)
                {
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
     * @return object
     * @throws DataCookerException
     */
    public function set($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $this->checkField($cacheKey, $object);

        $cacheKey = get_class($object);
        $keys = $this->getIdentifierValues($cacheKey, $object);
        $data = $this->getAttributeValues($cacheKey, $object);

        self::$phpData->update($cacheKey, $keys, $data);

        return clone $object;
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    public function remove($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $keys = $this->getIdentifierValues($cacheKey, $object);

        self::$phpData->delete($cacheKey, $keys);
    }

    public function flush()
    {
    }

    public function rollback()
    {
    }

    public function initialize()
    {
        self::$phpData = new PhpMemory();
    }
}