<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;
use Closure;

class PdoDataStore extends BufferDataStore implements DataStore
{
    private $store;

    private $shardStrategy;
    /** @var \PDO pdo */
    private $pdo;

    public function __construct(DataStore $store = null, Closure $closure, ShardStrategy $shardStrategy = null)
    {
        parent::__construct();

        $this->store = $store;

        $this->shardStrategy = $shardStrategy;
        $this->pdo = $closure();
    }

    public function get(Model $object)
    {
        if(empty($this->buffer))
        {
            if($this->shardStrategy)
            {
                $shardKey = $object->getShardKey();
                $shardId = $this->shardStrategy->getShardId($shardKey);
            }

            $tableName = $object->getShortName();
            $identifiers = $object->getIdentifiers();
            $attributes = $object->getAttributes();

            $sql = 'select ';
            $delimiter = '';
            foreach ($identifiers as $identifier)
            {
                $sql .= $delimiter . $identifier;
                $delimiter = ', ';
            }
            foreach ($attributes as $attribute)
            {
                $sql .= $delimiter . $attribute;
                $delimiter = ', ';
            }
            $sql .= ' from ' . $tableName;
            $delimiter = ' where ';
            $rootIdentifier = $identifiers[0];
            $sql .= $delimiter . $rootIdentifier . ' = :' . $rootIdentifier;
            $sql .= ';';

            $pdoStatement = $this->pdo->prepare($sql);
            $rootIdentifier = $identifiers[0];
            $pdoStatement->bindValue(':' . $rootIdentifier, $object->$rootIdentifier);

            $this->execute($pdoStatement, $sql);
            $rowCount = $pdoStatement->rowCount();

            if ($rowCount > 0)
            {
                $className = get_class($object);
                while($loadedData = $pdoStatement->fetchObject($className))
                {
                    parent::addClear($loadedData);
                }
            }
        }

        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                parent::addClear($data);
            }
        }

        $ret = parent::get($object);

        return $ret;
    }

    private function execute(\PDOStatement $stmt, $query)
    {
        try
        {
            $ret = $stmt->execute();
        }
        catch(\PDOException $e)
        {
            $log = array();
            $log['exception'] = "pdo data store exception";
            $log['error_info'] = $e->errorInfo;
            $log['error_message'] = $e->getMessage();
            $log['error_code'] = $e->getCode();
            $log['error_trace'] = $e->getTrace();
            $log['query'] = $query;

            $message = json_encode($log);

            throw new \Exception($message);
        }

        return $ret;
    }

    public function set(Model $object)
    {
        $rowCount = parent::set($object);
        if($rowCount > 0 && $this->store)
        {
            $this->store->set($object);
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
        $removedDataList = array();
        foreach($this->buffer as $key => $data)
        {
            /** @var Model $object */
            $object = $data[self::DATA];
            $tableName = $object->getShortName();
            if($this->shardStrategy)
            {
                $shardKey = $object->getShardKey();
                $shardId = $this->shardStrategy->getShardId($shardKey);
            }

            $state = $data[self::STATE];
            if($state === DataState::DIRTY_ADD && $data[self::FIRST_STATE] === DataState::DIRTY_DEL)
            {
                $state = DataState::DIRTY_SET;
            }
            else if($state === DataState::DIRTY_SET && $data[self::FIRST_STATE] === DataState::DIRTY_ADD)
            {
                $state = DataState::DIRTY_ADD;
            }
            else if($state === DataState::DIRTY_DEL && $data[self::FIRST_STATE] === DataState::DIRTY_ADD)
            {
                continue;
            }

            if($state === DataState::DIRTY_ADD)
            {
                $identifiers = $object->getIdentifiers();
                $attributes = $object->getAttributes();
                $autoIncrements = $object->getAutoIncrements();

                $fields = array_merge($identifiers, $attributes);
                $fields = array_diff($fields, $autoIncrements);

                $sql = "insert into {$tableName}";
                $delimiter = ' (';
                foreach ($fields as $field)
                {
                    $sql .= $delimiter . $field;
                    $delimiter = ', ';
                }
                $delimiter = ') values (';
                foreach ($fields as $field)
                {
                    $sql .= $delimiter . ':' . $field;
                    $delimiter = ', ';
                }
                $sql .= ");";

                $pdoStatement = $this->pdo->prepare($sql);
                foreach ($fields as $field)
                {
                    $name = $field;
                    $value = $object->$field;
                    $pdoStatement->bindValue(':' . $name, $value);
                }

                $this->execute($pdoStatement, $sql);
                if ($pdoStatement->rowCount() == 0)
                {
                    throw new \exception("FAILURE: no affected row");
                }

                foreach ($autoIncrements as $autoIncrement) //should be exact once
                {
                    $lastInsertId = $this->pdo->lastInsertId();
                    $object->$autoIncrement = (int)$lastInsertId;
                }

                $this->buffer[$key][self::STATE] = DataState::CLEAR;

                $this->lastAddedDataList[] = $data[self::DATA];
            }
            elseif($state === DataState::DIRTY_DEL)
            {
                //todo remove 된 녀석들 끼리 모아서 where 절에서 한번에 제거
                $removedDataList[] = $data[self::DATA];
                unset($this->buffer[$key]);
            }
            elseif($state === DataState::DIRTY_SET)
            {
                $pdo = $this->pdo;

                $changedAttributes = $data[self::CHANGED];
                $identifiers = $object->getIdentifiers();
                $sql = "UPDATE $tableName SET ";
                foreach($changedAttributes as $changedAttribute)
                {
                    $key = $changedAttribute['name'];
                    $sql .= $key;
                    $sql .= ' = ';
                    $sql .= ":$key";
                    $sql .= ' , ';
                }

                $sql = substr($sql, 0, -2);
                $delimiter = ' where ';
                foreach ($identifiers as $identifier)
                {
                    $identifierValue = $object->$identifier;
                    if($identifierValue === null)
                    {
                        throw new \exception("FAILURE: no identifier: ");
                    }
                    $identifierName = $identifier;
                    $sql .= $delimiter . $identifierName . ' = :' . $identifierName;
                    $delimiter = ' and ';
                }
                $sql .= ';';

                $pdoStatement = $pdo->prepare($sql);

                foreach($changedAttributes as $attributes)
                {
                    $pdoStatement->bindValue(':' . $attributes['name'], $attributes['value'], $attributes['dataType']);
                }
                foreach ($identifiers as $identifier)
                {
                    $pdoStatement->bindValue(':' . $identifier, $object->$identifier);
                }

                $this->execute($pdoStatement, $sql);
                if ($pdoStatement->rowCount() == 0)
                {
                    throw new \exception("FAILURE: no affected row");
                }

                $this->buffer[$key][self::STATE] = DataState::CLEAR;
                $this->buffer[$key][self::FIRST_STATE] = null;
            }
        }

        if(!empty($removedDataList))
        {
            foreach($removedDataList as $removedData)
            {
                $identifiers = $removedData->getIdentifiers();
                $sql = "delete from $tableName";
                $delimiter = ' where ';
                foreach ($identifiers as $identifier)
                {
                    $identifierValue = $removedData->$identifier;
                    if($identifierValue === null)
                    {
                        continue;
                    }
                    $sql .= $delimiter . $identifier . ' = :' . $identifier;
                    $delimiter = ' and ';
                }
                $sql .= ';';

                $pdoStatement = $this->pdo->prepare($sql);
                foreach ($identifiers as $identifier)
                {
                    $identifierValue =  $removedData->$identifier;
                    if($identifierValue === null)
                    {
                        continue;
                    }
                    $pdoStatement->bindValue(':' . $identifier, $identifierValue);
                }

                $this->execute($pdoStatement, $sql);
            }
        }

        //todo rollback 이 가능하도록 rollback 쿼리 작성할 것
    }

    public function rollback()
    {
        $this->buffer = array();
    }
}