<?php
declare(strict_types=1);

namespace battlecook\DataStore\KeyValue;

use battlecook\Data\Status;
use battlecook\DataCookerException;
use battlecook\DataStorage\LeafNode;
use battlecook\DataStore\AbstractStore;
use battlecook\DataStore\IDataStore;
use battlecook\DataStructure\Attribute;

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
                        $tree[$key] = new Attribute($tree[$key]->getData());
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
}