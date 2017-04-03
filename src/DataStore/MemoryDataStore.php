<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class MemoryDataStore extends BufferDataStore implements DataStore
{
    private $store;

    /** @var Model[]  */
    private $data;

    public function __construct(DataStore $store = null)
    {
        $this->data = array();
        $this->buffer = array();
        $this->store = $store;
    }

    public function get(Model $object)
    {
        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                $this->data[] = array('data' => $data, 'state' => DataState::NOT_CHANGED);
            }
            $this->buffer = $this->data;
        }

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

    public function set(Model $object)
    {
        $rowCount = 0;
        $bufferedData = $this->get($object);
        if(!empty($bufferedData))
        {
            $data = $bufferedData[0];
            $attributes = $object->getAttributes();
            $isDirty = false;
            foreach($attributes as $attribute)
            {
                if($data->$attribute !== $object->$attribute)
                {
                    $isDirty = true;
                    break;
                }
            }
            if($isDirty)
            {
                foreach($this->buffer as $key => $value)
                {
                    if($value['data'] === $data)
                    {
                        $this->buffer[$key]['data'] = $object;
                        if($value['state'] !== DataState::ADD)
                        {
                            $this->buffer[$key]['state'] = DataState::SET;
                        }
                        $rowCount++;
                        break;
                    }
                }
            }

            if($this->store)
            {
                $this->store->set($object);
            }
        }

        return $rowCount;
    }

    public function add(Model $object)
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

        if($this->store)
        {
            $this->store->add($object);
        }
    }

    public function remove(Model $object)
    {
        $rowCount = 0;
        $identifiers = $object->getIdentifiers();
        $depth = $this->getDepth($identifiers, $object);
        if($depth === 0)
        {
            return $rowCount;
        }
        $ret = $this->get($object);
        if(!empty($ret))
        {
            foreach ($this->buffer as $key => $data)
            {
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

                if($count >= $depth)
                {
                    array_splice($this->buffer, $key, 1);
                    $rowCount++;
                    break;
                }
            }
        }

        return $rowCount;
    }

    public function flush()
    {
        if($this->store)
        {
            try
            {
                $this->store->flush();
            }
            catch (\Exception $e)
            {
                $this->rollback();
            }
        }

        foreach($this->buffer as $key => $data)
        {
            if($data['state'] === DataState::REMOVE)
            {
                unset($this->buffer[$key]);
            }
            else
            {
                $this->buffer[$key]['state'] = DataState::NOT_CHANGED;
            }
        }

        $this->data = array();
        foreach($this->buffer as $data)
        {
            $this->data[] = $data;
        }
    }

    public function rollback()
    {
        $this->buffer = $this->data;
    }
}