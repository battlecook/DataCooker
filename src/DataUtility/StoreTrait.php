<?php

namespace battlecook\DataUtility;

use battlecook\DataCookerException;

trait StoreTrait
{
    /**
     * @param string $cacheKey
     * @param $object
     * @return mixed
     * @throws DataCookerException
     */
    protected function checkAutoIncrementAndAddIfNeed(string $cacheKey, $object)
    {
        $autoIncrement = $this->getAutoIncrementKey($cacheKey);
        if ($autoIncrement !== "" && empty($object->$autoIncrement) === true) {
            if ($this->store === null) {
                throw new DataCookerException("autoIncrement value is null");
            } else {
                $object = $this->store->add($object);
                if (empty($object->$autoIncrement) === true) {
                    throw new DataCookerException("autoIncrement value is null");
                }
            }
        }

        return $object;
    }
}