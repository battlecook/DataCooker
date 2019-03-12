<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\Config\Database;
use battlecook\DataCookerException;

final class RelationDatabase extends AbstractMeta implements IDataStore
{
    private $pdo = array();

    private $storage;

    public function __construct(?IDataStore $storage, Database $config)
    {
        $this->storage = $storage;

        $dbName = $config->getDatabaseName();
        $ip = $config->getIp();
        $port = $config->getPort();

        $dsn = "mysql:host={$ip};port={$port};dbname={$dbName}";
        try {
            $this->pdo = new \PDO($dsn, $config->getUser(), $config->getPassword(), array());
        } catch (\PDOException $e) {
            throw new DataCookerException();
        }
        $this->pdo->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8mb4");
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    private function getTableName($className)
    {
        $explodedObject = explode('\\', $className);
        return end($explodedObject);
    }

    /**
     * @param $object
     * @return mixed
     * @throws DataCookerException
     */
    public function add($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $this->checkHaveAllFieldData($cacheKey, $object);

        $tableName = $this->getTableName($cacheKey);

        $autoIncrement = $this->cachedFieldMap[$cacheKey]->getAutoIncrement();

        if ($object->$autoIncrement === null) {
            $fields = $this->getFieldKeys($cacheKey);
        } else {
            $fields = $this->getFieldKeysWithAutoIncrement($cacheKey);
        }

        $sql = "insert into {$tableName}";
        $delimiter = ' (';
        foreach ($fields as $field) {
            $sql .= $delimiter . $field;
            $delimiter = ', ';
        }
        $delimiter = ') values (';
        foreach ($fields as $field) {
            $sql .= $delimiter . ':' . $field;
            $delimiter = ', ';
        }
        $sql .= ");";

        try {
            $pdoStatement = $this->pdo->prepare($sql);
            foreach ($fields as $field) {
                $pdoStatement->bindValue(':' . $field, $object->$field);
            }

            $pdoStatement->execute();
            if ($pdoStatement->rowCount() === 0) {
                throw new DataCookerException("no affected row");
            } else {
                if ($pdoStatement->rowCount() > 1) {
                    throw new DataCookerException("many affected row");
                }
            }

            $ret = clone $object;
            $ret->$autoIncrement = (int)$this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            throw new DataCookerException();
        }

        if ($this->storage !== null) {
            $ret = $this->storage->add($object);
        }

        return $ret;
    }

    /**
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    public function get($object): array
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $explodedObject = explode('\\', $cacheKey);
        $tableName = end($explodedObject);

        $fieldsWithAutoIncrement = $this->getFieldKeysWithAutoIncrement($cacheKey);

        $sql = 'select sql_no_cache ';
        $delimiter = '';
        foreach ($fieldsWithAutoIncrement as $identifierKey) {
            $sql .= $delimiter . $identifierKey;
            $delimiter = ', ';
        }
        $sql .= ' from ' . $tableName;

        //todo tuning point ( if it have autoincrement, change where statement with autoincrement )
        $identifierKeys = $this->getIdentifierKeys($cacheKey);
        $delimiter = ' where ';
        $whereStatement = '';
        foreach ($identifierKeys as $identifierKey) {
            if ($object->$identifierKey === null) {
                break;
            }
            $whereStatement .= $delimiter . $identifierKey . ' = :' . $identifierKey;
            $delimiter = ' and ';
        }
        $whereStatement .= ';';

        $sql .= $whereStatement;

        try {
            $pdoStatement = $this->pdo->prepare($sql);

            foreach ($identifierKeys as $identifierKey) {
                $identifierValue = $object->$identifierKey;
                if ($identifierValue === null) {
                    break;
                }
                $pdoStatement->bindValue(':' . $identifierKey, $identifierValue);
            }

            $pdoStatement->execute();
        } catch (\PDOException $e) {
            throw new DataCookerException($e);
        }

        $rowCount = $pdoStatement->rowCount();

        $ret = array();
        if ($rowCount === 0) {
            return $ret;
        } else {
            while ($loadedObject = $pdoStatement->fetchObject($cacheKey)) {
                $ret[] = $loadedObject;
            }
        }

        return $ret;
    }

    public function set($object)
    {
        $this->setMeta($object);
        $cacheKey = get_class($object);
        $explodedObject = explode('\\', $cacheKey);
        $tableName = end($explodedObject);

        $attributeKeys = $this->getAttributeKeys($cacheKey);
        $sql = "UPDATE $tableName SET ";
        foreach ($attributeKeys as $attributeKey) {
            $attributeValue = $object->$attributeKey;
            if ($attributeValue === null) {
                continue;
            }

            $sql .= "`" . $attributeKey . "`";
            $sql .= ' = ';
            $sql .= ":$attributeKey";
            $sql .= ' , ';
        }
        $sql = substr($sql, 0, -2);

        //todo tuning point ( if it have autoincrement, change where statement with autoincrement )
        $identifierKeys = $this->getIdentifierKeys($cacheKey);
        $delimiter = ' where ';
        $whereStatement = '';
        foreach ($identifierKeys as $identifierKey) {
            if ($object->$identifierKey === null) {
                break;
            }
            $whereStatement .= $delimiter . $identifierKey . ' = :' . $identifierKey;
            $delimiter = ' and ';
        }
        $whereStatement .= ';';

        $sql .= $whereStatement;

        $attributeKeys = $this->getAttributeKeys($cacheKey);

        try {
            $pdoStatement = $this->pdo->prepare($sql);

            //todo add dirty check
            foreach ($attributeKeys as $attributeKey) {
                $attributeValue = $object->$attributeKey;
                if ($attributeValue === null) {
                    continue;
                }
                $pdoStatement->bindValue(':' . $attributeKey, $attributeValue);
            }

            foreach ($identifierKeys as $identifierKey) {
                $identifierValue = $object->$identifierKey;
                if ($identifierValue === null) {
                    break;
                }
                $pdoStatement->bindValue(':' . $identifierKey, $identifierValue);
            }

            $pdoStatement->execute();
        } catch (\PDOException $e) {
            throw new DataCookerException();
        }

        $rowCount = $pdoStatement->rowCount();

        /*
        $ret = array();
        if ($rowCount === 0) {
            return $ret;
        } else {
        }
        */

        if ($this->storage !== null) {
            $this->storage->set($object);
        }
    }

    public function remove($object)
    {
        $this->setMeta($object);

        $cacheKey = get_class($object);
        $tableName = $this->getTableName($cacheKey);

        $sql = "delete from {$tableName}";
        $identifierKeys = $this->getIdentifierKeys($cacheKey);
        $delimiter = ' where ';
        $whereStatement = '';
        foreach ($identifierKeys as $identifierKey) {
            if ($object->$identifierKey === null) {
                break;
            }
            $whereStatement .= $delimiter . $identifierKey . ' = :' . $identifierKey;
            $delimiter = ' and ';
        }
        $whereStatement .= ';';

        $sql .= $whereStatement;

        try {
            $pdoStatement = $this->pdo->prepare($sql);

            foreach ($identifierKeys as $identifierKey) {
                $identifierValue = $object->$identifierKey;
                if ($identifierValue === null) {
                    break;
                }
                $pdoStatement->bindValue(':' . $identifierKey, $identifierValue);
            }

            $pdoStatement->execute();
        } catch (\PDOException $e) {
            throw new DataCookerException();
        }

        $ret = true;
        if ($pdoStatement->rowCount() == 0) {
            $ret = false;
        }

        if ($this->storage !== null) {
            $ret = $this->storage->remove($object);
        }

        return $ret;
    }
}