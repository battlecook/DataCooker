<?php
namespace battlecook\DataStore;

use battlecook\DataObject\Model;

abstract class BufferDataStore
{
    const DATA = 0;
    const STATE = 1;
    const CHANGED = 2;
    const FIRST_STATE = 3;

    protected $buffer;
    protected $index;

    protected $lastAddedDataList;

    protected $autoIncrement = 0;

    public function __construct()
    {
        $this->buffer = array();
        $this->index = array();
    }

    private function isRemoved($data)
    {
        return $data[self::STATE] === DataState::DIRTY_DEL;
    }

    public function getLastAddedDataList()
    {
        return $this->lastAddedDataList;
    }

    private function getDepth($identifiers, $object)
    {
        $depth = 0;
        foreach($identifiers as $identifier)
        {
            if(isset($object->$identifier))
            {
                $depth++;
            }
            else
            {
                break;
            }
        }

        return $depth;
    }

    protected function get(Model $object)
    {
        $identifiers = $object->getIdentifiers();
        $depth = $this->getDepth($identifiers, $object);
        if($depth === 0)
        {
            $ret = $this->getDataAll();
        }
        else if($depth === count($identifiers))
        {
            $depth = 0;
            $identifiers = $object->getIdentifiers();
            $maxDepth = $this->getDepth($identifiers, $object);
            $ret = $this->getByIndex($this->index, $object, $identifiers, $depth, $maxDepth);
        }
        else
        {
            $ret = $this->getBufferData($identifiers, $object);
        }

        return $ret;
    }

    private function getDataAll()
    {
        $ret = array();
        foreach ($this->buffer as $key => $data)
        {
            if($data[self::STATE] === DataState::DIRTY_DEL)
            {
                continue;
            }
            $ret[] = $data[self::DATA];
        }

        return $ret;
    }

    private function getByIndex(&$index, $data, $identifiers, $depth, $maxDepth)
    {
        if($depth === $maxDepth)
        {
            if($this->buffer[$index][self::STATE] === DataState::DIRTY_DEL)
            {
                return array();
            }
            return array($this->buffer[$index][self::DATA]);
        }
        $identifier = $identifiers[$depth];
        $value = $data->$identifier;
        if(!isset($index[$value]))
        {
            return array();
        }
        $depth++;
        return $this->getByIndex($index[$value], $data, $identifiers, $depth, $maxDepth);
    }

    private function getBufferData($identifiers, $object)
    {
        $depth = $this->getDepth($identifiers, $object);

        $ret = array();
        foreach ($this->buffer as $key => $data)
        {
            if($this->isRemoved($data))
            {
                continue;
            }

            $count = 0;
            foreach($identifiers as $identifier)
            {
                if($data[self::DATA]->$identifier === $object->$identifier)
                {
                    $count++;
                }
                else
                {
                    break;
                }
            }

            if($count >= $depth)
            {
                $ret[] = $data[self::DATA];
            }
        }

        return $ret;
    }

    protected function set(Model $object)
    {
        $rowCount = 0;
        $bufferedData = $this->get($object);
        if(!empty($bufferedData))
        {
            $rowCount = 1;
            //todo have to multi update feature, but once single update only
            $data = $bufferedData[0];
            $attributes = $object->getAttributes();
            $changedAttributes = array();
            foreach($attributes as $attribute)
            {
                if($data->$attribute !== $object->$attribute)
                {
                    $dataType = \PDO::PARAM_STR;
                    if(is_integer($object->$attribute))
                    {
                        $dataType = \PDO::PARAM_INT;
                    }

                    $changedAttributes[] = array('name' => $attribute, 'value' => $object->$attribute, 'dataType' => $dataType);
                }
            }

            if(!empty($changedAttributes))
            {
                $depth = 0;
                $identifiers = $data->getIdentifiers();
                $maxDepth = $this->getDepth($identifiers, $data);
                $this->setIndex($this->index, $object, $identifiers, $depth, $maxDepth);
            }
        }

        return $rowCount;
    }

    private function setIndex($index, $data, $identifiers, $depth, $maxDepth)
    {
        if($depth === $maxDepth)
        {
            if($this->buffer[$index][self::STATE] === DataState::DIRTY_DEL)
            {
                throw new \Exception("deleted data can not be updated.");
            }
            $this->buffer[$index][self::DATA] = $data;
            $this->buffer[$index][self::STATE] = DataState::DIRTY_SET;
            return;
        }
        $identifier = $identifiers[$depth];
        $value = $data->$identifier;
        if(!isset($index[$value]))
        {
            throw new \Exception("not exist set date");
        }
        $depth++;
        $this->setIndex($index[$value], $data, $identifiers, $depth, $maxDepth);
    }

    protected function add(Model $object)
    {
        $rowCount = 0;
        $bufferedData = $this->get($object);
        if(empty($bufferedData))
        {
            $rowCount = 1;
            //todo have to multi update feature, but once single update only
            $depth = 0;
            $identifiers = $object->getIdentifiers();
            $maxDepth = $this->getDepth($identifiers, $object);
            $this->addIndex($this->index, $object, $identifiers, $depth, $maxDepth);
        }

        return $rowCount;
    }

    private function addIndex(&$index, $data, $identifiers, $depth, $maxDepth)
    {
        if($depth === $maxDepth)
        {
            $index = $this->autoIncrement;
            $this->buffer[$this->autoIncrement] = array(self::DATA => $data, self::STATE => DataState::DIRTY_ADD, self::FIRST_STATE => DataState::DIRTY_ADD);
            $this->autoIncrement++;
            return;
        }

        $identifier = $identifiers[$depth];
        $value = $data->$identifier;
        if(!isset($index[$value]))
        {
            $index[$value] = array();
        }
        $depth++;
        $this->addIndex($index[$value], $data, $identifiers, $depth, $maxDepth);
    }

    protected function addClear(Model $data)
    {
        $depth = 0;
        $identifiers = $data->getIdentifiers();
        $maxDepth = $this->getDepth($identifiers, $data);
        $this->clearIndex($this->index, $data, $identifiers, $depth, $maxDepth);
    }

    private function clearIndex(&$index, $data, $identifiers, $depth, $maxDepth)
    {
        if($depth === $maxDepth)
        {
            $index = $this->autoIncrement;
            $this->buffer[$this->autoIncrement] = array(self::DATA => $data, self::STATE => DataState::CLEAR, self::FIRST_STATE => DataState::CLEAR);
            $this->autoIncrement++;
            return;
        }
        $identifier = $identifiers[$depth];
        $value = $data->$identifier;
        if(!isset($index[$value]))
        {
            $index[$value] = array();
        }
        $depth++;
        $this->clearIndex($index[$value], $data, $identifiers, $depth, $maxDepth);
    }

    protected function remove(Model $object)
    {
        $rowCount = 0;
        $bufferedData = $this->get($object);
        if(!empty($bufferedData))
        {
            $rowCount = 1;
            //todo have to multi update feature, but once single update only
            $depth = 0;
            $identifiers = $object->getIdentifiers();
            $maxDepth = $this->getDepth($identifiers, $object);
            $this->removeIndex($this->index, $object, $identifiers, $depth, $maxDepth);
        }

        return $rowCount;
    }

    private function removeIndex(&$index, $data, $identifiers, $depth, $maxDepth)
    {
        if($depth === $maxDepth)
        {
            if($this->buffer[$index][self::STATE] === DataState::DIRTY_DEL)
            {
                throw new \Exception("already data removed");
            }
            $this->buffer[$index][self::STATE] = DataState::DIRTY_DEL;
            return;
        }
        $identifier = $identifiers[$depth];
        $value = $data->$identifier;
        if(!isset($index[$value]))
        {
            throw new \Exception("not exist remove date");
        }
        $depth++;
        $this->removeIndex($index[$value], $data, $identifiers, $depth, $maxDepth);
    }

    public function flush()
    {

    }
}