<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\DataUtility\MetaTrait;

abstract class AbstractStore
{
    use MetaTrait;

    const VERSION_DELIMITER = "@dataCookerVersion";
    const IDENTIFIER_DELIMITER = "@dataCookerIdentifier";
    const AUTOINCREMENT_DELIMITER = "@dataCookerAutoIncrement";
    const ATTRIBUTE_DELIMITER = "@dataCookerAttribute";
}