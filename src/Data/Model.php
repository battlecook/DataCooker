<?php
declare(strict_types=1);

namespace battlecook\Data;

use battlecook\DataCookerException;

abstract class Model implements IData
{
    protected $version = 1;

    /**
     * Model constructor.
     * @throws DataCookerException
     */
    public function __construct()
    {
        if (empty($this->getIdentifiers()) === true) {
            throw new DataCookerException("identifiers cant't be an empty array");
        }

        if ($this->getAutoIncrement() === '') {
            throw new DataCookerException("autoIncrement must not be an empty string ('')");
        }

        if (is_null($this->getAutoIncrement()) !== null) {
            if (in_array($this->getAutoIncrement(),
                    $this->getIdentifiers()) === false && in_array($this->getAutoIncrement(),
                    $this->getAttributes()) === false) {
                throw new DataCookerException("autoIncrement have to include identifiers or attributes");
            }
        }
    }

    abstract public function getIdentifiers(): array;

    //optional
    public function getAutoIncrement(): ?string
    {
        return null;
    }

    abstract public function getAttributes(): array;

    public function getVersion(): int
    {
        return $this->version;
    }
}