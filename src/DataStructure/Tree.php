<?php
declare(strict_types=1);

namespace battlecook\DataStructure;

use battlecook\DataCookerException;

final class Tree
{
    private $map;

    public function __construct()
    {
        $this->map = array();
    }

    /**
     * @param array $keys
     * @param array $data
     * @return bool
     * @throws DataCookerException
     */
    public function insert(array $keys, array $data): bool
    {
        if(empty($keys) === true)
        {
            throw new DataCookerException("insert function have to have keys.");
        }
        $ret = $this->insertRecursive($this->map, $keys, $data);

        return $ret;
    }

    public function search($keys): bool
    {
        return $this->searchRecursive($this->map, $keys);
    }

    public function delete($keys)
    {

    }

    public function update($keys)
    {

    }

    private function insertRecursive(&$tree, $keys, $data): bool
    {
        $key = array_shift($keys);
        if (empty($keys) === true)
        {
            if(isset($tree[$key]) === true)
            {
                return false;
            }
            else
            {
                $tree[$key] = $data;
                return true;
            }
        }
        else
        {
            return $this->insertRecursive($tree[$key], $keys,  $data);
        }
    }

    private function searchRecursive(&$tree, array $keys): bool
    {
        if(empty($keys) === true)
        {
            return false;
        }
        $searchKey = array_shift($keys);
        if(isset($tree[$searchKey]) === false)
        {
            $tree[$searchKey] = null;
        }
        $this->searchRecursive($tree[$searchKey], $keys);
    }
}