<?php
namespace battlecook\DataStore;

use battlecook\DataObject\Model;

abstract class BufferDataStore
{
    const DATA = 0;
    const STATE = 1;
    const CHANGED = 2;
    const STATE_HISTORY = 3;

    protected $buffer;
    protected $index;

    protected $lastAddedDataList;

    protected $autoIncrement = 0;

    private function isRemoved($data)
    {
        return $data[self::STATE] === DataState::DIRTY_DEL;
    }

    protected function getDepth($identifiers, $object)
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

    protected function setIndex($index, $data, $identifiers, $depth, $maxDepth)
    {
        if($depth === $maxDepth)
        {
            $this->buffer[$index][self::DATA] = $data;
            $this->buffer[$index][self::STATE] = DataState::DIRTY_SET;
            $this->buffer[$index][self::STATE_HISTORY][] = DataState::DIRTY_SET;
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
        $ret = $this->get($object);
        if(empty($ret))
        {
            $this->addIndex($object);
        }
        else
        {
            $data = $ret[0];
            foreach($this->buffer as $key => $value)
            {
                if($value === $data)
                {
                    $state = $data[self::DATA];
                    if($state === DataState::DIRTY_DEL)
                    {
                        $state = DataState::DIRTY_SET;
                    }
                    elseif($state === DataState::DIRTY_SET)
                    {
                    }
                    elseif($state === DataState::CLEAR || $state === DataState::DIRTY_ADD)
                    {
                        throw new \Exception("already data exist");
                    }
                    else
                    {
                        throw new \Exception("invalid state");
                    }
                    $this->buffer[$key][self::STATE] = $state;
                    $this->buffer[$key][self::DATA] = $data;

                    break;
                }
            }
        }
    }

    public function getLastAddedDataList()
    {
        return $this->lastAddedDataList;
    }

    protected function createIndex()
    {
        foreach($this->buffer as $data)
        {
            $depth = 0;
            $identifiers = $data[self::DATA]->getIdentifiers();
            $maxDepth = $this->getDepth($identifiers, $data[self::DATA]);
            $this->recursion($this->index, $data[self::DATA], $identifiers, $depth, $maxDepth);
        }
    }

    protected function addIndex($data)
    {
        $depth = 0;
        $identifiers = $data->getIdentifiers();
        $maxDepth = $this->getDepth($identifiers, $data);
        $this->recursion($this->index, $data, $identifiers, $depth, $maxDepth);
    }

    private function recursion(&$index, $data, $identifiers, $depth, $maxDepth)
    {
        if($depth === $maxDepth)
        {
            $index = $this->autoIncrement;
            $this->buffer[$this->autoIncrement] = array(self::DATA => $data, self::STATE => DataState::CLEAR, self::STATE_HISTORY => array());
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
        $this->recursion($index[$value], $data, $identifiers, $depth, $maxDepth);
    }

    private function optimizeDataState()
    {

    }
}