<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\Config\Database;
use battlecook\Types\Status;
use battlecook\DataCookerException;
use battlecook\DataStore\KeyValue\AbstractKeyValue;

final class RelationDatabase extends AbstractStore implements IDataStore
{
    private $pdo = array();

    private $store;

    /**
     * RelationDatabase constructor.
     * @param array $option
     * @throws DataCookerException
     */
    public function __construct(array $option)
    {
        if (empty($option) === false) {
            if (isset($option['store']) === true) {
                if (($option['store'] instanceof IDataStore) === false) {
                    throw new DataCookerException("store option have to be IDataStore instance.");
                }

                if ($option['store'] instanceof Buffered) {
                    throw new DataCookerException("BufferedDataStore can't be exist for other DataStore.");
                }
                $this->store = $option['store'];
            }

            if (isset($option['hosts']) === true) {
                $hosts = $option['hosts'];
                foreach ($hosts as $key => $host) {
                    if (isset($host['ip']) === false) {
                        throw new DataCookerException("not exist IP");
                    }

                    if (isset($host['port']) === false) {
                        $hosts[$key]['port'] = 3306;
                    }

                    if (isset($host['dbname']) === false) {
                        $hosts[$key]['dbname'] = '';
                    }

                    if (isset($host['user']) === false) {
                        $hosts[$key]['user'] = '';
                    }

                    if (isset($host['password']) === false) {
                        $hosts[$key]['password'] = '';
                    }
                }
            } else {
                throw new DataCookerException("Not Exist Host Information");
            }
            $this->store = $option['store'];
        } else {
            throw new DataCookerException("Not Exist Option Information");
        }

        $dbName = $hosts[0]['dbname'];
        $ip = $hosts[0]['ip'];
        $port = $hosts[0]['port'];
        $user = $hosts[0]['user'];
        $password = $hosts[0]['password'];

        $dsn = "mysql:host={$ip};port={$port};dbname={$dbName}";
        try {
            $this->pdo = new \PDO($dsn, $user, $password, array());
            $this->pdo->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8mb4");
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new DataCookerException();
        }
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

        $autoIncrement = $this->getAutoIncrementKey($cacheKey);
        if ($this->hasAutoIncrement($cacheKey) === true && $object->$autoIncrement !== null) {
            $fields = $this->getFieldKeysWithAutoIncrement($cacheKey);
        } else {
            $fields = $this->getFieldKeys($cacheKey);
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

        if ($this->store !== null) {
            $ret = $this->store->add($ret);
        }

        return $ret;
    }

    /**
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    public function get($object)
    {
        $cacheKey = get_class($object);
        $this->setMeta($object);
        $this->checkHaveAllIdentifiersData($cacheKey, $object);
        $ret = $this->search($object);

        return $ret[0];
    }

    /**
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    public function search($object): array
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
            throw new DataCookerException("Error Pdo Exception");
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

    /**
     * @param $object
     * @throws DataCookerException
     */
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

        if ($this->store !== null) {
            $this->store->set($object);
        }
    }

    /**
     * @param $object
     * @return bool
     * @throws DataCookerException
     */
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

        if ($this->store !== null) {
            $ret = $this->store->remove($object);
        }

        return $ret;
    }

    /**
     * @param null $data
     * @throws DataCookerException
     */
    public function commitAll($data = null)
    {
        if ($data !== null) {

            $trees = $data;

            $commitObjectMap = array();
            foreach ($trees as $className => $tree) {
                $meta = self::getMetaData($className);
                array_walk_recursive($tree, function ($data) use ($className, $meta, &$commitObjectMap) {
                    $object = new $className();
                    array_map(function ($key, $value) use ($object) {
                        $object->$key = $value;
                    }, $meta->getField()->getIdentifiers(), $data->getKey());

                    $dataObject = $data->getData();
                    array_map(function ($key) use ($object, $dataObject) {
                        $object->$key = $dataObject->$key;
                    }, $meta->getField()->getAttributes());

                    if ($data->getStatus() !== Status::NONE) {
                        $commitObjectMap[$data->getStatus()][] = $object;
                    }
                });
            }

            //todo this function would be tuning. ( multi insert, multi update and so on )
            foreach ($commitObjectMap as $status => $commitObjectGroup) {
                foreach ($commitObjectGroup as $object) {
                    if ($status === Status::DELETED) {
                        $this->remove($object);
                    } elseif ($status === Status::UPDATED) {
                        $this->set($object);
                    } elseif ($status === Status::INSERTED) {
                        $this->add($object);
                    } else {
                        throw new DataCookerException("invalid status");
                    }
                }
            }

            if ($this->store !== null) {
                if ($this->store instanceof AbstractKeyValue) {

                } else {
                    $this->store->commit($data);
                }
            }
        }
    }
}