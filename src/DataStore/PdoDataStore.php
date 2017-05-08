<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;
use Closure;

class PdoDataStore implements DataStore
{
    private $store;

    private $shardStrategy;
    /** @var \PDO pdo */
    private $pdo;

    private $lastAddedDataList;

    public function __construct(DataStore $store = null, Closure $closure, ShardStrategy $shardStrategy = null)
    {
        $this->store = $store;

        $this->shardStrategy = $shardStrategy;
        $this->pdo = $closure();
        $this->lastAddedDataList = array();
    }

    public function get(Model $object)
    {
        $ret = array();
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
                $ret[] = $loadedData;
            }
        }

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
        $tableName = $object->getShortName();
        $pdo = $this->pdo;

        $identifiers = $object->getIdentifiers();
        $attributes = $object->getAttributes();
        $autoIncrements = $object->getAutoIncrements();

        $fields = array_diff($attributes, $autoIncrements);

        $sql = "UPDATE $tableName SET ";
        foreach($fields as $field)
        {
            $sql .= $field;
            $sql .= ' = ';
            $sql .= ":$field";
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

        foreach($fields as $field)
        {
            $pdoStatement->bindValue(':' . $field, $object->$field);
        }
        foreach ($identifiers as $identifier)
        {
            $pdoStatement->bindValue(':' . $identifier, $object->$identifier);
        }

        $this->execute($pdoStatement, $sql);

        $rowCount = $pdoStatement->rowCount();
        if ($rowCount == 0)
        {
            throw new \exception("FAILURE: no affected row");
        }

        return $rowCount;
    }

    public function setChangedAttributes(Model $object, $changedAttributes)
    {
        $tableName = $object->getShortName();
        $pdo = $this->pdo;

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
    }

    public function add(Model $object)
    {
        $tableName = $object->getShortName();
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

        $this->lastAddedDataList[] = $object;
    }

    public function remove(Model $object)
    {
        $tableName = $object->getShortName();
        $identifiers = $object->getIdentifiers();
        $sql = "delete from $tableName";
        $delimiter = ' where ';
        foreach ($identifiers as $identifier)
        {
            $identifierValue = $object->$identifier;
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
            $identifierValue =  $object->$identifier;
            if($identifierValue === null)
            {
                continue;
            }
            $pdoStatement->bindValue(':' . $identifier, $identifierValue);
        }

        $this->execute($pdoStatement, $sql);

        return $pdoStatement->rowCount();
    }

    public function removeMulti($objects)
    {
        $tableName = $objects[0]->getShortName();
        foreach($objects as $removedData)
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

    public function flush()
    {
        $this->lastAddedDataList = array();
        //todo rollback query 제거
        //todo rollback 이 가능하도록 rollback 쿼리 작성할 것
    }

    public function rollback()
    {

    }

    public function getLastAddedDataList()
    {
        return $this->lastAddedDataList;
    }
}