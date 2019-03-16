<?php

namespace battlecook\DataUtility;

use battlecook\Data\Status;
use battlecook\DataStorage\LeafNode;

trait TreeTrait
{
    private function travel(&$tree)
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
            }
            if ($ret === Status::INSERTED) {
                $tree[$key] = $tree[$key]->getData();
            }
            if ($ret === Status::UPDATED) {
                $tree[$key] = $tree[$key]->getData();
            }
            if ($ret === Status::NONE) {
                unset($tree[$key]);
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