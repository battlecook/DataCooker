<?php
namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class BufferDataStore implements DataStore
{
    const DATA = 0;
    const STATE = 1;
    const CHANGED = 2;
    const FIRST_STATE = 3;

    private $store;

    protected $buffer;
    protected $index;

    protected $lastAddedDataList;

    protected $autoIncrement = 0;

    public function __construct(DataStore $store = null)
    {
        $this->buffer = array();
        $this->index = array();

        $this->store = $store;
    }

    public function getLastAddedDataList()
    {
        return $this->lastAddedDataList;
    }

    public function get(Model $object)
    {
        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                $this->addClear($data);
            }
        }

        $identifiers = $object->getIdentifiers();
        $depth = $this->getDepth($identifiers, $object);
        if($depth === 0)
        {
            $ret = $this->getDataAll();
        }
        else if($depth === count($identifiers))
        {
            //todo 전체 검색해서 가져올지 인덱스를 타서 가져올지 선택해야만 한다.
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

    protected function addClear(Model $data)
    {
        $depth = 0;
        $identifiers = $data->getIdentifiers();
        $maxDepth = $this->getDepth($identifiers, $data);
        $this->addIndex($this->index, $data, $identifiers, $depth, $maxDepth, DataState::CLEAR);
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

    private function isRemoved($data)
    {
        return $data[self::STATE] === DataState::DIRTY_DEL;
    }

    public function set(Model $object)
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

    public function add(Model $object)
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
            $this->addIndex($this->index, $object, $identifiers, $depth, $maxDepth, DataState::DIRTY_ADD);
        }

        return $rowCount;
    }

    private function addIndex(&$index, $data, $identifiers, $depth, $maxDepth, $firstState)
    {
        if($depth === $maxDepth)
        {
            $index = $this->autoIncrement;
            $this->buffer[$this->autoIncrement] = array(self::DATA => $data, self::STATE => $firstState, self::FIRST_STATE => $firstState);
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
        $this->addIndex($index[$value], $data, $identifiers, $depth, $maxDepth, $firstState);
    }

    public function remove(Model $object)
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
        if(!$this->store)
        {
            throw new \Exception("not exist store that BufferDataStore can do");
        }

        $lastAddedDataList = array();
        $removedDataList = array();
        foreach($this->buffer as $key => $data)
        {
            /** @var Model $object */
            $object = $data[BufferDataStore::DATA];
            $state = $data[BufferDataStore::STATE];
            if($state === DataState::DIRTY_ADD && $data[BufferDataStore::FIRST_STATE] === DataState::DIRTY_DEL)
            {
                $state = DataState::DIRTY_SET;
            }
            else if($state === DataState::DIRTY_SET && $data[BufferDataStore::FIRST_STATE] === DataState::DIRTY_ADD)
            {
                $state = DataState::DIRTY_ADD;
            }
            else if($state === DataState::DIRTY_DEL && $data[BufferDataStore::FIRST_STATE] === DataState::DIRTY_ADD)
            {
                continue;
            }

            if($state === DataState::DIRTY_ADD)
            {
                $this->store->add($object);
                $lastAddedDataList[] = $data[BufferDataStore::DATA];
            }
            elseif($state === DataState::DIRTY_DEL)
            {
                //todo remove 된 녀석들 끼리 모아서 where 절에서 한번에 제거
                $removedDataList[] = $data[BufferDataStore::DATA];
            }
            elseif($state === DataState::DIRTY_SET)
            {
                $changedAttributes = $data[BufferDataStore::CHANGED];
                $this->store->setChangedAttributes($object, $changedAttributes);
            }
        }

        if(!empty($removedDataList))
        {
            $this->store->removeMulti($removedDataList);
        }

        $this->lastAddedDataList = $lastAddedDataList;





        try {
            foreach ($this->buffer as $key => $data)
            {
                if($data[self::STATE] === DataState::DIRTY_DEL)
                {
                    unset($this->buffer[$key]);
                    //todo index 도 지울것
                }
                elseif($data[self::STATE] === DataState::DIRTY_ADD)
                {
                    $this->buffer[$key][self::STATE] = DataState::CLEAR;
                }
                elseif($data[self::STATE] === DataState::DIRTY_SET)
                {
                    $this->buffer[$key][self::STATE] = DataState::CLEAR;
                }
            }

            $newDataList = array();
            foreach($this->buffer as $bufferedData)
            {
                $newDataList[] = $bufferedData;
            }
            $this->buffer = array();
            $this->index = array();
            $this->autoIncrement = 0;

            foreach($newDataList as $data)
            {
                $this->addClear($data[self::DATA]);
            }

            //index도 제거

        } catch (\Exception $e) {

            $this->store->rollback();
        }
    }

    private function unsetIndex($key)
    {

    }

    /**
     * @param Model[] $objects
     * @return int
     */
    public function removeMulti($objects)
    {
        // TODO: Implement removeMulti() method.
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }

    public function setChangedAttributes(Model $object, $changedAttributes)
    {
        // TODO: Implement setChangedAttributes() method.
    }
}