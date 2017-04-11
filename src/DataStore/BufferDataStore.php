<?php
namespace battlecook\DataStore;

use battlecook\DataObject\Model;

abstract class BufferDataStore
{
    const NODE = 0;
    const STATE = 1;
    const CHANGED = 2;

    const INDEX = 0;
    const DATA = 1;

    protected $buffer;
    protected $index;

    protected $lastAddedDataList;

    protected $autoIncrement = 0;

    private function isRemoved($data)
    {
        return $data[self::STATE] === DataState::DIRTY_DEL;
    }

    private function isSameDepth($count, $depth)
    {
        return $count === $depth;
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
            $ret[] = $data[self::NODE];
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
                if($data[self::NODE]->$identifier === $object->$identifier)
                {
                    $count++;
                }
                else
                {
                    break;
                }
            }

            if($this->isSameDepth($count, $depth))
            {
                $ret[] = $data[self::NODE];
            }
        }

        return $ret;
    }

    protected function add(Model $object)
    {
        $ret = $this->get($object);
        if(empty($ret))
        {
            $this->buffer[] = array(self::NODE => $object, self::STATE => DataState::DIRTY_ADD);
        }
        else
        {
            $data = $ret[0];
            foreach($this->buffer as $key => $value)
            {
                if($value === $data)
                {
                    $state = $data[self::NODE];
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
                    $this->buffer[$key][self::NODE] = $data;

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
            $identifiers = $data[self::NODE]->getIdentifiers();
            $maxDepth = $this->getDepth($identifiers, $data[self::NODE]);
            $this->recursion($this->index, $data[self::NODE], $identifiers, $depth, $maxDepth);
        }
    }

    private function recursion(&$index, $data, $identifiers, $depth, $maxDepth)
    {
        if($depth === $maxDepth)
        {
            $index = $data;
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
}