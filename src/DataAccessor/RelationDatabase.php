<?php
declare(strict_types=1);

namespace battlecook\DataAccessor;

use battlecook\Config\Database;
use battlecook\DataCookerException;

final class RelationDatabase extends AbstractMeta implements IDataAccessor
{
    private $pdo = array();

    private $storage;

    public function __construct(?IDataAccessor $storage, Database $config)
    {
        $this->storage = $storage;

        $dbName = $config->getDatabaseName();
        $ip = $config->getIp();
        $port = $config->getPort();
        $dsn = "mysql:$dbName=demo;host=$ip;port=$port;charset=utf8";

        try {

            $this->pdo = new \PDO($dsn, $config->getDatabaseName(), $config->getPassword(), array());
        } catch(\PDOException $e) {
            $log = array();
            $log['exception'] = "sql data store exception";
            $log['error_info'] = $e->errorInfo;
            $log['error_message'] = $e->getMessage();
            $log['error_code'] = $e->getCode();
            $log['error_trace'] = $e->getTrace();

            throw new DataCookerException($e);
        }
        $this->pdo->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8mb4");
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function add($object)
    {
        $this->setMeta($object);

        $cacheKey = $tableName = get_class($object);
        $fields = $this->cachedFieldMap[$cacheKey]->getFields();

        $sql = "insert into {$tableName}";
        $delimiter = ' (';
        foreach($fields as $field)
        {
            $sql .= $delimiter . $field;
            $delimiter = ', ';
        }
        $delimiter = ') values (';
        foreach($fields as $field)
        {
            $sql .= $delimiter . ':' . $field;
            $delimiter = ', ';
        }
        $sql .= ");";

        try
        {
            $pdoStatement = $this->pdo->prepare($sql);
            foreach($fields as $field)
            {
                $pdoStatement->bindValue(':' . $field, $object->$field);
            }

            $pdoStatement->execute();
            if($pdoStatement->rowCount() == 0)
            {
                throw new DataCookerException("no affected row");
            }

            $autoIncrement = $this->cachedFieldMap[$cacheKey]->getAutoIncrement();
            $object->$autoIncrement = $this->pdo->lastInsertId();
        } catch(\PDOException $e) {
            $log = array();
            $log['exception'] = "sql data store exception";
            $log['error_info'] = $e->errorInfo;
            $log['error_message'] = $e->getMessage();
            $log['error_code'] = $e->getCode();
            $log['error_trace'] = $e->getTrace();
            $log['query'] = $sql;

            throw new DataCookerException($e);
        }

        return clone $object;
    }

    public function get($object): array
    {
        return array();
    }

    public function set($object)
    {
    }

    public function remove($object)
    {
    }
}