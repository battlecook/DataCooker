<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\DataCookerException;
use battlecook\DataUtility\MetaTrait;

class AbstractStore
{
    use MetaTrait;

    const VERSION_DELIMITER = "@dataCookerVersion";
    const IDENTIFIER_DELIMITER = "@dataCookerIdentifier";
    const AUTOINCREMENT_DELIMITER = "@dataCookerAutoIncrement";
    const ATTRIBUTE_DELIMITER = "@dataCookerAttribute";

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