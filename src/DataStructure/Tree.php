<?php
declare(strict_types=1);

namespace battlecook\DataStructure;

use battlecook\Data\Status;
use battlecook\DataCookerException;

final class Tree
{
    private $depth;
    private $tree;
    private $withAutoIncrement;

    public function __construct(bool $withAutoIncrement, int $depth)
    {
        $this->tree = array();
        $this->withAutoIncrement = $withAutoIncrement;
        // for later need depth constraint
        $this->depth = $depth;
    }

    private function insertRecursive(&$tree, array $keys, $data): bool
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
                $tree[$key] = new LeafNode($data);
                return true;
            }
        }
        else
        {
            return $this->insertRecursive($tree[$key], $keys,  $data);
        }
    }

    /**
     * @param array $keys
     * @param array $data
     * @return bool
     * @throws DataCookerException
     */
    public function insert(array $keys, array $data): bool
    {
        if(count($keys) !== $this->depth)
        {
            throw new DataCookerException("invalid depth");
        }
        $ret = $this->insertRecursive($this->tree, $keys, $data);

        return $ret;
    }

    /**
     * @param $tree
     * @param array $keys
     * @return array
     * @throws DataCookerException
     */
    private function searchRecursive(&$tree, array $keys): array
    {
        if(empty($tree) === true)
        {
            return array();
        }

        $searchKey = array_shift($keys);
        if($searchKey !== null && ($searchKey instanceof LeafNode) === false)
        {
            return $this->searchRecursive($tree[$searchKey], $keys);
        }
        //leaf
        elseif(is_array($tree) === false)
        {
            /**
             * @var $tree LeafNode
             */
            if($tree->getStatus() === Status::DELETED)
            {
                return array();
            }

            return array($tree->getData());
        }
        //middle node
        else
        {
            $leafs = array();
            array_walk_recursive($tree, function($data) use (&$leafs)
            {
                /**
                 * @var $data LeafNode
                 */
                if($data->getStatus() !== Status::DELETED)
                {
                    $leafs[] = $data->getData();
                }
            });
            return $leafs;
        }
    }

    /**
     * @param array $keys
     * @return array
     * @throws DataCookerException
     */
    public function search(array $keys)
    {
        if(count($keys) > $this->depth)
        {
            throw new DataCookerException("");
        }
        return $this->searchRecursive($this->tree, $keys);
    }

    /**
     * @param array $keys
     * @throws DataCookerException
     */
    public function delete(array $keys)
    {
        if(count($keys) !== $this->depth)
        {
            throw new DataCookerException("invalid depth");
        }

        //if status is unset, remove node


    }

    /**
     * @param array $keys
     * @throws DataCookerException
     */
    public function update(array $keys)
    {
        if(count($keys) !== $this->depth)
        {
            throw new DataCookerException("invalid depth");
        }

    }
}