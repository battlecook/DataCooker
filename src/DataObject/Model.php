<?php
namespace battlecook\DataObject;

use ReflectionClass;

abstract class Model implements DataObject
{
    private $identifiers = array();
    private $autoIncrements = array();
    private $attributes = array();
    private $shortName = null;
    public function __construct()
    {
        $reflection = new ReflectionClass($this);
        foreach($reflection->getProperties() as $property)
        {
            if(stripos($property->getDocComment(), 'dataStoreIdentifier'))
            {
                $this->identifiers[] = $property->getName();
            }
            if(stripos($property->getDocComment(), 'dataStoreAttribute'))
            {
                $this->attributes[] = $property->getName();
            }
            if(stripos($property->getDocComment(), 'dataStoreAutoIncrement'))
            {
                $this->autoIncrements[] = $property->getName();
            }
        }
        $this->shortName = $reflection->getShortName();
    }
    public function getIdentifiers()
    {
        return $this->identifiers;
    }
    public function getAutoIncrements()
    {
        return $this->autoIncrements;
    }
    public function getAttributes()
    {
        return $this->attributes;
    }
    public function getShortName()
    {
        return $this->shortName;
    }
}