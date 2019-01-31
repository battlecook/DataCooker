<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

use battlecook\Data\Model;
use battlecook\DataCookerException;
use battlecook\DataObject\Field;

final class Memory
{
    const NONE = 0;
    const INSERT = 1;
    const DELETE = 2;
    const UPDATE = 3;

    const IDENTIFIERS = 0;
    const ATTRIBUTES = 0;
    const AUTOINCREMENT = 0;

    private $statusTree;
    private $dataTree;

    private $cachedFieldMap = array();

    private $storage;

    public function __construct(IDataStorage $storage = null)
    {
        $this->storage = $storage;
    }

    private function addToTree(Model $object)
    {

    }

    /**
     * @param $object
     * @throws DataCookerException
     */
    private function setField($object)
    {
        if($object instanceof Model)
        {
            $identifiers = $object->getIdentifiers();
            $autoIncrement = $object->getAutoIncrement();
            $attributes = $object->getAttributes();
            $version = $object->getVersion();
        }
        else
        {
            $identifiers = array();
            $attributes = array();
            $autoIncrement = array();
            $version = 0;

            try
            {
                $class = get_class($object);
                $rc = new \ReflectionClass($class);
                $properties = $rc->getProperties();
                foreach($properties as $property)
                {
                    $doc = $property->getDocComment();
                    if(stripos('@dataCookerVersion', $doc))
                    {
                        $version = $property->getName();
                    }
                    else if(stripos('@dataCookerIdentifier', $doc))
                    {
                        $identifiers[] = $property->getName();
                    }
                    else if(stripos('@dataCookerAutoIncrement', $doc))
                    {
                        $autoIncrement[] = $property->getName();
                    }
                    else if(stripos('@dataCookerAttribute', $doc))
                    {
                        $attributes[] = $property->getName();
                    }
                }
            }
            catch(\ReflectionException $e)
            {
                throw new DataCookerException("Reflection Exception");
            }
        }

        $cacheKey = get_class($object);
        $this->cachedFieldMap[$cacheKey] = new Field($identifiers, $autoIncrement, $attributes);
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


        }
        else
        {
            $this->setField($object);


            if($object instanceof Model)
            {
                $identifiers = $object->getIdentifiers();
                $attributes = $object->getAttributes();
                $autoIncrement = $object->getAutoIncrement();

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

                if(in_array($autoIncrement, $identifiers, true) === true && empty($object->$autoIncrement) === true)
                {
                    if($this->storage === null)
                    {
                        throw new DataCookerException("autoIncrement value is null");
                    }
                    else
                    {
                        //rollback 을 위해 적어 둬야 하나 ...(일단 적고 나중에 옵션질...)
                        $object = $this->storage->add($object);
                    }
                }

                $this->addToTree($object);
            }
            else
            {
                try
                {
                    $cacheKey = get_class($object);
                    if(isset($this->cachedFieldMap[$cacheKey]))
                    {

                    }
                    else
                    {
                        $identifiers = array();
                        $attributes = array();
                        $autoIncrement = array();

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
                                $autoIncrement[] = $property->getName();
                            }
                            else if(stripos('@dataCookerAttribute', $doc))
                            {
                                $attributes[] = $property->getName();
                            }
                        }
                    }
                }
                catch(\ReflectionException $e)
                {
                    throw new DataCookerException("reflection error");
                }
            }
        }

        //return clone $object();
    }

    /*
    public function add(Model $object): Model
    {
        $identifiers = $object->getIdentifiers();
        $attributes = $object->getAttributes();
        $autoIncrement = $object->getAutoIncrement();

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

        if(in_array($autoIncrement, $identifiers, true) === true && empty($object->$autoIncrement) === true)
        {
            if($this->storage === null)
            {
                throw new DataCookerException("autoIncrement value is null");
            }
            else
            {
                //rollback 을 위해 적어 둬야 하나 ...
                $object = $this->storage->add($object);
            }
        }

        $this->addToTree($object);

        return clone $object;
    }
    */

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