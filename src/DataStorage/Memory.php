<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

use battlecook\Data\Model;
use battlecook\DataCookerException;

class Memory
{
    const NONE = 0;
    const INSERT = 1;
    const DELETE = 2;
    const UPDATE = 3;

    private $statusTree;
    private $dataTree;

    private $storage;

    public function __construct(IDataStorage $storage = null)
    {
        $this->storage = $storage;
    }

    private function addToTree(Model $object)
    {

    }

    public function add($object)
    {

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