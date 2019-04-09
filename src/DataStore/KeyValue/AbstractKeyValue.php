<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\Types\Status;
use battlecook\DataCookerException;
use battlecook\Types\LeafNode;
use battlecook\DataStore\AbstractStore;
use battlecook\DataStore\IDataStore;
use battlecook\Types\Attribute;

abstract class AbstractKeyValue extends AbstractStore implements IDataStore
{
    /**
     * @param $tree
     * @return int
     * @throws DataCookerException
     */
    protected function travel(&$tree)
    {
        //leaf
        if (is_array($tree) === false && $tree instanceof LeafNode) {
            if ($tree->getStatus() === Status::DELETED) {
                return Status::DELETED;
            }

            if ($tree->getStatus() === Status::NONE) {
                return Status::NONE;
            }

            if ($tree->getStatus() === Status::UPDATED) {
                return Status::UPDATED;
            }

            if ($tree->getStatus() === Status::INSERTED) {
                return Status::INSERTED;
            }
        }
        $keys = array_keys($tree);
        foreach ($keys as $key) {
            $ret = $this->travel($tree[$key]);

            //leaf node process
            if ($ret === Status::DELETED) {
                unset($tree[$key]);
            } else {
                if ($ret === Status::UNSET) {
                    throw new DataCookerException('invalid status ( unset status )');
                } else {
                    if ($ret === Status::INSERTED || $ret === Status::UPDATED || $ret === Status::NONE) {
                        $object = $tree[$key]->getData();
                        $tree[$key] = new Attribute($this->getAttributeValues(get_class($object), $object));
                    }
                }
            }
            //end of leaf node process

            //internals node
            if ($ret === null) {
                if (empty($tree[$key]) === true) {
                    unset($tree[$key]);
                }
            }
        }
    }

    protected function getKey(string $cacheKey, $object): string
    {
        $id1 = $this->getRootIdentifierKey($cacheKey);

        return $cacheKey . '\\' . $object->$id1;
    }

    protected function getCurrentIdentifierValue($cacheKey, $object): array
    {
        $keys = array();
        foreach ($this->getIdentifierKeys($cacheKey) as $identifierKey) {
            if ($object->$identifierKey === null) {
                break;
            }
            $keys[] = $object->$identifierKey;
        }

        return $keys;
    }

    private function getIdentifierKeyByDepth($cacheKey, $depth): string
    {
        $identifierKeys = $this->getIdentifierKeys($cacheKey);

        return $identifierKeys[$depth];
    }

    protected function searchRecursive(&$tree, array $keys, &$object): array
    {
        $searchKey = array_shift($keys);
        if ($searchKey !== null) {
            return $this->searchRecursive($tree[$searchKey], $keys, $object);
        } elseif ($tree instanceof Attribute) { //leafs
            $created = clone $object;
            $cacheKey = get_class($created);
            $attributeKeys = $this->getAttributeKeys($cacheKey);

            $attributeValues = $tree->getAttributes();
            foreach ($attributeKeys as $attributeKey) {
                $created->$attributeKey = array_shift($attributeValues);
            }

            return array($created);
        } else { //internals
            $cacheKey = get_class($object);

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveArrayIterator($tree),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $ret = array();
            $created = clone $object;
            $depth = count($this->getCurrentIdentifierValue($cacheKey, $created));
            foreach ($iterator as $key => $value) {
                $currentIdentifier = $this->getIdentifierKeyByDepth($cacheKey, $depth + $iterator->getDepth());
                $created->$currentIdentifier = $key;
                if ($value instanceof Attribute) { //leafs

                    $attributeKeys = $this->getAttributeKeys($cacheKey);
                    $attributeValues = $value->getAttributes();
                    foreach ($attributeKeys as $attributeKey) {
                        $created->$attributeKey = array_shift($attributeValues);
                    }

                    $ret[] = clone $created;
                }
            }

            return $ret;
        }
    }


    /**
     * @param $tree
     * @param array $keys
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    protected function insertRecursive(&$tree, array $keys, &$object)
    {
        $searchKey = array_shift($keys);
        if ($searchKey !== null) {
            return $this->insertRecursive($tree[$searchKey], $keys, $object);
        } elseif ($tree instanceof Attribute) { //leafs
            throw new DataCookerException("already exist data at leafnode");
        } else {
            $cacheKey = get_class($object);
            $attributeValues = $this->getAttributeValues($cacheKey, $object);
            $tree = new Attribute($attributeValues);
        }
    }


    /**
     * @param $tree
     * @param array $keys
     * @param $object
     * @return array
     * @throws DataCookerException
     */
    protected function updateRecursive(&$tree, array $keys, &$object)
    {
        $searchKey = array_shift($keys);
        if ($searchKey !== null) {
            return $this->updateRecursive($tree[$searchKey], $keys, $object);
        } elseif ($tree instanceof Attribute) { //leafs
            $cacheKey = get_class($object);
            $attributeValues = $this->getAttributeValues($cacheKey, $object);
            $tree = new Attribute($attributeValues);
        } else {
            throw new DataCookerException("update keys can not be empty leaf node");
        }
    }

    protected function removeRecursive(&$tree, array $keys, &$object)
    {
        $searchKey = array_shift($keys);
        if ($searchKey !== null) {
            $this->removeRecursive($tree[$searchKey], $keys, $object);
            if ($tree[$searchKey] === null || empty($tree[$searchKey]) === true) {
                unset($tree[$searchKey]);
            }
        } else {
            $tree = null;
        }
    }

}