<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;

class PdoDataStore extends BufferDataStore implements DataStore
{
    private $store;

    private $shardStrategy;
    private $pdo;

    public function __construct(DataStore $store = null, $config, ShardStrategy $shardStrategy = null)
    {
        $this->buffer = array();
        $this->store = $store;

        $this->shardStrategy = $shardStrategy;

        $dbo = new DBO($config);
        $this->pdo = $dbo->getPdo();
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
                    $this->buffer[] = array(self::DATA => $loadedData, self::STATE => DataState::NOT_CHANGED);
                }
            }
        }

        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                $this->buffer[] = array(self::DATA => $data, self::STATE => DataState::NOT_CHANGED);
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
        $rowCount = 0;
        $bufferedData = $this->get($object);
        if(!empty($bufferedData))
        {
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
                foreach($this->buffer as $key => $value)
                {
                    if($value[self::DATA] === $data)
                    {
                        $this->buffer[$key][self::DATA] = $object;
                        if($value[self::STATE] !== DataState::ADD)
                        {
                            $this->buffer[$key][self::STATE] = DataState::SET;
                        }
                        $this->buffer[$key][self::CHANGED] = $changedAttributes;
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
                    $this->buffer[$key][self::STATE] = DataState::REMOVE;
                    $rowCount++;
                    break;
                }
            }
        }

        if($this->store)
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
            if($data[self::STATE] === DataState::ADD)
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

                $this->buffer[$key][self::STATE] = DataState::NOT_CHANGED;
            }
            elseif($data[self::STATE] === DataState::REMOVE)
            {
                //todo remove 된 녀석들 끼리 모아서 where 절에서 한번에 제거
                $removedDataList[] = $data['data'];
                unset($this->buffer[$key]);
            }
            elseif($data[self::STATE] === DataState::SET)
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
                $delimiter = ' WHERE ';
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

                $this->buffer[$key][self::STATE] = DataState::NOT_CHANGED;
            }
        }

        foreach($removedDataList as $removedData)
        {
            $sql = 'delete from ';

        }

        //todo rollback 이 가능하도록 rollback 쿼리 작성할 것
    }

    public function rollback()
    {
        $this->buffer = array();
    }
}