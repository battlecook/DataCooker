<?php
declare(strict_types=1);

namespace battlecook\DataStorage;

use battlecook\Data\Status;
use battlecook\DataCookerException;

final class PhpMemory
{
    private $trees;
    /**
     * @var $metas Meta[]
     */
    private $metas;

    public function __construct()
    {
        $this->metas = array();
        $this->trees = array();
    }

    //for test case
    public function getTree()
    {
        return $this->trees;
    }

    private function insertRecursive(&$tree, array $keys, $key, $data, $changedStatus)
    {
        if (empty($keys) === true) {
            $tree = new LeafNode($key, $data);
            return;
        }
        $searchKey = array_shift($keys);
        if (isset($tree[$searchKey]) === false) {
            $tree[$searchKey] = null;
        }
        $this->insertRecursive($tree[$searchKey], $keys, $key, $data, $changedStatus);
    }

    /**
     * @param string $dataName
     * @param array $keys
     * @param array $data
     * @throws DataCookerException
     */
    public function insert(string $dataName, array $keys, array $data)
    {
        $meta = $this->metas[$dataName];
        if (count($keys) !== $meta->getDepth()) {
            throw new DataCookerException("invalid depth");
        }

        /**
         * @var $leafNodeArr LeafNode[]
         */
        $leafNodeArr = $this->searchRecursive($this->trees[$dataName], $keys);
        if (empty($leafNodeArr)) {
            $this->insertRecursive($this->trees[$dataName], $keys, $keys, $data, Status::INSERTED);
        } else {
            if ($meta->hasAutoIncrement()) {
                $changedStatus = Status::getStatusWithAutoIncrement($leafNodeArr[0]->getStatus(), Status::INSERTED);
            } else {
                $changedStatus = Status::getStatusWithoutAutoincrement($leafNodeArr[0]->getStatus(), Status::INSERTED);
            }
            $this->insertRecursive($this->trees[$dataName], $keys, $keys, $data, $changedStatus);
        }
    }

    /**
     * @param $tree
     * @param array $keys
     * @return LeafNode[]
     * @throws DataCookerException
     */
    private function searchRecursive(&$tree, array $keys): array
    {
        if (empty($tree) === true) {
            return array();
        }

        $searchKey = array_shift($keys);
        if ($searchKey !== null && ($searchKey instanceof LeafNode) === false) {
            return $this->searchRecursive($tree[$searchKey], $keys);
        } //leafs
        elseif (is_array($tree) === false) {
            /**
             * @var $tree LeafNode
             */
            return array(clone $tree);
        } //internals
        else {
            $leafs = array();
            array_walk_recursive($tree, function ($data) use (&$leafs) {
                /**
                 * @var $data LeafNode
                 */
                $leafs[] = $data;
            });
            return $leafs;
        }
    }

    /**
     * @param string $dataName
     * @param array $keys
     * @return LeafNode[]
     * @throws DataCookerException
     */
    public function search(string $dataName, array $keys)
    {
        $meta = $this->metas[$dataName];
        if (count($keys) > $meta->getDepth()) {
            throw new DataCookerException("");
        }
        return $this->searchRecursive($this->trees[$dataName], $keys);
    }

    private function deleteRecursive(&$tree, array $keys, $changedStatus): bool
    {
        $key = array_shift($keys);
        if (empty($keys) === true) {
            if ($tree[$key] instanceof LeafNode) {
                $tree[$key]->setStatus($changedStatus);
                return true;
            }
            return false;
        } else {
            return $this->deleteRecursive($tree[$key], $keys, $changedStatus);
        }
    }

    private function unsetRecursive(&$tree, array $keys, $changedStatus)
    {
        $key = array_shift($keys);
        if (empty($keys) === true) {
            if ($tree[$key] instanceof LeafNode) {
                unset($tree[$key]);
                return;
            }
        } else {
            $this->unsetRecursive($tree[$key], $keys, $changedStatus);
            if (empty($tree[$key]) === true) {
                unset($tree[$key]);
                return;
            }
        }
    }

    /**
     * @param string $dataName
     * @param array $keys
     * @throws DataCookerException
     */
    public function delete(string $dataName, array $keys)
    {
        $meta = $this->metas[$dataName];
        if (count($keys) !== $meta->getDepth()) {
            throw new DataCookerException("invalid depth");
        }

        /**
         * @var $leafNodeArr LeafNode[]
         */
        $leafNodeArr = $this->searchRecursive($this->trees[$dataName], $keys);
        if (empty($leafNodeArr)) {
            return;
        }
        $changedStatus = Status::getStatusWithoutAutoincrement($leafNodeArr[0]->getStatus(), Status::DELETED);
        if ($meta->hasAutoIncrement()) {
            $changedStatus = Status::getStatusWithAutoIncrement($leafNodeArr[0]->getStatus(), Status::DELETED);
        }

        if ($changedStatus === Status::UNSET) {
            $this->unsetRecursive($this->trees[$dataName], $keys, $changedStatus);
        } else {
            $this->deleteRecursive($this->trees[$dataName], $keys, $changedStatus);
        }
    }

    /**
     * @param $tree
     * @param array $keys
     * @param array $treeKey
     * @param $data
     * @param $changedStatus
     */
    private function updateRecursive(&$tree, array $keys, array $treeKey, $data, $changedStatus)
    {
        $key = array_shift($keys);
        if (empty($keys) === true) {
            if ($tree[$key] instanceof LeafNode) {
                $tree[$key]->update($changedStatus, $data);
            } else {
                $tree[$key] = new LeafNode($treeKey, $data);
            }
        } else {
            $this->updateRecursive($tree[$key], $keys, $treeKey, $data, $changedStatus);
        }
    }

    /**
     * @param string $dataName
     * @param array $keys
     * @param array $data
     * @throws DataCookerException
     */
    public function update(string $dataName, array $keys, array $data)
    {
        $meta = $this->metas[$dataName];
        if (count($keys) !== $meta->getDepth()) {
            throw new DataCookerException("invalid depth");
        }

        /**
         * @var $leafNodeArr LeafNode[]
         */
        $leafNodeArr = $this->searchRecursive($this->trees[$dataName], $keys);
        if (empty($leafNodeArr)) {
            throw new DataCookerException("data is empty for update");
        } else {
            if ($leafNodeArr[0]->getData() === $data || count($leafNodeArr[0]->getData()) !== count($data)) {
                return;
            }

            $changedStatus = Status::getStatusWithoutAutoincrement($leafNodeArr[0]->getStatus(), Status::UPDATED);
            if ($meta->hasAutoIncrement()) {
                $changedStatus = Status::getStatusWithAutoIncrement($leafNodeArr[0]->getStatus(), Status::UPDATED);
            }
            $this->updateRecursive($this->trees[$dataName], $keys, $keys, $data, $changedStatus);
        }
    }

    public function hasData(string $dataName)
    {
        return isset($this->metas[$dataName]);
    }

    public function addMetaData(Meta $meta)
    {
        $this->metas[$meta->getDataName()] = $meta;
    }

    public function getMetaData($dataName)
    {
        return $this->metas[$dataName];
    }

    public function getTrees(): array
    {
        $created = $this->trees;

        return $created;
    }
}