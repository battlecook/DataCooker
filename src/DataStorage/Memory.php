<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

use battlecook\Data\Model;
use battlecook\DataCookerException;
use battlecook\DataStructure\Tree;

final class Memory
{
    const IDENTIFIERS = 0;
    const AUTOINCREMENT = 1;
    const ATTRIBUTES = 2;

    /**
     * @var $trees Tree[]
     */
    private static $trees;

    private $cachedFieldMap = array();

    private $storage;

    public function __construct(IDataStorage $storage = null)
    {
        $this->storage = $storage;
        self::$trees = array();
    }

    private function addToTree($object)
    {

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
                    if(stripos('@dataCookerVersion', $doc))
                    {

                    }
                    else if(stripos('@dataCookerIdentifier', $doc))
                    {
                        $identifiers[] = $property->getName();
                    }
                    else if(stripos('@dataCookerAutoIncrement', $doc))
                    {
                        $autoIncrement = $property->getName();
                    }
                    else if(stripos('@dataCookerAttribute', $doc))
                    {
                        $attributes[] = $property->getName();
                    }
                }

                $this->cachedFieldMap[$cacheKey][self::IDENTIFIERS] = $identifiers;
                $this->cachedFieldMap[$cacheKey][self::AUTOINCREMENT] = $autoIncrement;
                $this->cachedFieldMap[$cacheKey][self::ATTRIBUTES] = $attributes;

            }
            catch(\ReflectionException $e)
            {
                throw new DataCookerException("reflection error");
            }
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

        $this->addToTree($object);

        return clone $object;
    }

    public function get(Model $object)
    {
    }

    public function set(Model $object): int
    {
        // TODO: Implement update() method.
    }

    public function remove(Model $object): int
    {
        // TODO: Implement delete() method.
    }

    public function commit()
    {
        // TODO: Implement flush() method.
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }
}