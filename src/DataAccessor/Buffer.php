<?php
declare(strict_types=1);

namespace battlecook\DataAccessor;

use battlecook\DataCookerException;
use battlecook\DataStorage\Field;
use battlecook\DataStorage\Meta;
use battlecook\DataStorage\PhpMemory;

final class Buffer implements IDataAccessor
{
    const IDENTIFIERS = 0;
    const AUTOINCREMENT = 1;
    const ATTRIBUTES = 2;

    const VERSION_DELIMITER = "@dataCookerVersion";
    const IDENTIFIER_DELIMITER = "@dataCookerIdentifier";
    const AUTOINCREMENT_DELIMITER = "@dataCookerAutoIncrement";
    const ATTRIBUTE_DELIMITER = "@dataCookerAttribute";


    /**
     * @var $phpData PhpMemory
     */
    private static $phpData;

    private $cachedFieldMap = array();

    private $storage;

    public function __construct(IDataAccessor $storage = null)
    {
        $this->storage = $storage;
        if(empty(self::$phpData) === true)
        {
            self::$phpData = new PhpMemory();
        }
    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    public function add($object)
    {
        $cacheKey = get_class($object);
        if(isset($this->cachedFieldMap[$cacheKey]))
        {
            $identifiers = $this->cachedFieldMap[$cacheKey][self::IDENTIFIERS];
            $autoIncrement = $this->cachedFieldMap[$cacheKey][self::AUTOINCREMENT];
            $attributes = $this->cachedFieldMap[$cacheKey][self::ATTRIBUTES];
        }
        else
        {
            try
            {
                $identifiers = array();
                $autoIncrement = "";
                $attributes = array();

                $class = get_class($object);
                $rc = new \ReflectionClass($class);
                $properties = $rc->getProperties();
                foreach($properties as $property)
                {
                    $doc = $property->getDocComment();
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
                }

                if(empty($identifiers) === true)
                {
                    throw new DataCookerException("identifiers is empty");
                }

                if(array_search($autoIncrement, $identifiers) === false && array_search($autoIncrement, $attributes) === false)
                {
                    throw new DataCookerException("autoincrement must be included in identifiers or attribute");
                }

                $this->cachedFieldMap[$cacheKey][self::IDENTIFIERS] = $identifiers;
                $this->cachedFieldMap[$cacheKey][self::AUTOINCREMENT] = $autoIncrement;
                $this->cachedFieldMap[$cacheKey][self::ATTRIBUTES] = $attributes;

            }
            catch(\ReflectionException $e)
            {
                throw new DataCookerException("reflection error");
            }

            self::$phpData->addMetaData(new Meta(new Field($identifiers, $autoIncrement, $attributes), $cacheKey));
        }

        $fields = array_merge($identifiers, $attributes);
        $fields = array_diff($fields, array($autoIncrement));

        foreach($fields as $field)
        {
            //is_null 이 더 맞는거 같지만 exception 이 빠져버림
            if(empty($object->$field) === true)
            {
                throw new DataCookerException("fields don't fill all");
            }
        }

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

        $keys = array();
        foreach($this->cachedFieldMap[$cacheKey][self::IDENTIFIERS] as $identifier)
        {
            $keys[] = $object->$identifier;
        }

        $data = array();
        foreach($this->cachedFieldMap[$cacheKey][self::ATTRIBUTES] as $attribute)
        {
            $data[] = $object->$attribute;
        }

        self::$phpData->insert($cacheKey, $keys, $data);

        return clone $object;
    }

    public function get($object)
    {
    }

    public function set($object): int
    {
    }

    public function remove($object): int
    {
    }

    public function flush()
    {
    }

    public function rollback()
    {
    }

    public function initialize()
    {
    }
}