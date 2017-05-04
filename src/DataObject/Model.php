<?php
namespace battlecook\DataObject;

use ReflectionClass;

abstract class Model implements DataObject
{
    private static $data = array();

    private $shortName = null;

    protected $version = 1;

    public function __construct()
    {
        $className = get_class($this);
        $explodedClassName = explode('\\', $className);
        $shortName = end($explodedClassName);
        $this->shortName = $shortName;

        if(!isset(self::$data[$shortName]))
        {
            $identifiers = array();
            $attributes = array();
            $autoIncrements = array();

            $reflection = new ReflectionClass($this);
            foreach($reflection->getProperties() as $property)
            {
                if(stripos($property->getDocComment(), 'dataStoreIdentifier'))
                {
                    $identifiers[] = $property->getName();
                }
                if(stripos($property->getDocComment(), 'dataStoreAttribute'))
                {
                    $attributes[] = $property->getName();
                }
                if(stripos($property->getDocComment(), 'dataStoreAutoIncrement'))
                {
                    $autoIncrements[] = $property->getName();
                }
            }


            self::$data[$shortName]['identifiers'] = $identifiers;
            self::$data[$shortName]['attributes'] = $attributes;
            self::$data[$shortName]['autoIncrements'] = $autoIncrements;
        }
    }

    public function getIdentifiers()
    {
        return self::$data[$this->shortName]['identifiers'];
    }

    public function getAutoIncrements()
    {
        return self::$data[$this->shortName]['autoIncrements'];
    }

    public function getAttributes()
    {
        return self::$data[$this->shortName]['attributes'];
    }

    public function getShortName()
    {
        return $this->shortName;
    }

    public function getShardKey()
    {
        return false;
    }

    public function getVersion()
    {
        return $this->version;
    }
}