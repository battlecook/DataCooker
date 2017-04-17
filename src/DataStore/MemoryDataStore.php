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
        $this->store = $store;
    }

    public function get(Model $object)
    {
        if(empty($this->buffer))
        {
            if(!empty($this->data))
            {
                $this->data = $this->buffer;
            }
        }

        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                parent::addClear($data);
            }
            $this->data = $this->buffer;
        }

        $ret = parent::get($object);

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
                    if($value[self::DATA] === $data)
                    {
                        $this->buffer[$key][self::DATA] = $object;
                        if($value[self::STATE] !== DataState::DIRTY_ADD)
                        {
                            $this->buffer[$key][self::STATE] = DataState::DIRTY_SET;
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
        parent::add($object);
        if($this->store)
        {
            $this->store->add($object);
        }
    }

    public function remove(Model $object)
    {
        $rowCount = parent::remove($object);
        if($rowCount > 0 && $this->store)
        {
            $this->store->remove($object);
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
            if($data[self::STATE] === DataState::DIRTY_ADD)
            {
                $this->lastAddedDataList[] = $data[self::DATA];
            }
            if($data[self::STATE] === DataState::DIRTY_DEL)
            {
                unset($this->buffer[$key]);
            }
            else
            {
                $this->buffer[$key][self::STATE] = DataState::CLEAR;
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