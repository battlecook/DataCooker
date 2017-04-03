<?php
namespace battlecook\DataStore;

use battlecook\DataObject\Model;

abstract class BufferDataStore
{
    /** @var Model[]  */
    protected $buffer;

    private function isRemoved($data)
    {
        return $data['state'] === DataState::REMOVE;
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
            if($data['state'] === DataState::REMOVE)
            {
                continue;
            }
            $ret[] = $data['data'];
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
                if($data['data']->$identifier === $object->$identifier)
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
                $ret[] = $data['data'];
            }
        }

        return $ret;
    }

    protected function add(Model $object)
    {
        $ret = $this->get($object);
        if(empty($ret))
        {
            $this->buffer[] = array('data' => $object, 'state' => DataState::ADD);
        }
        else
        {
            $data = $ret[0];
            foreach($this->buffer as $key => $value)
            {
                if($value === $data)
                {
                    $state = $data['state'];
                    if($state === DataState::REMOVE)
                    {
                        $state = DataState::SET;
                    }
                    elseif($state === DataState::SET)
                    {
                    }
                    elseif($state === DataState::NOT_CHANGED || $state === DataState::ADD)
                    {
                        throw new \Exception("already data exist");
                    }
                    else
                    {
                        throw new \Exception("invalid state");
                    }
                    $this->buffer[$key]['state'] = $state;
                    $this->buffer[$key]['data'] = $data;

                    break;
                }
            }
        }
    }
}