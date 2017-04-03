<?php
namespace battlecook\DataStore;

use battlecook\DataObject\Model;

abstract class BufferDataStore
{
    /** @var Model[]  */
    protected $buffer;

    protected function getBufferData($identifiers, $object)
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

    protected function getDataAll()
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
}