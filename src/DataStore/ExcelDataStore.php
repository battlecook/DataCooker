<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelDataStore implements DataStore
{
    private $store;

    private $path;

    public function __construct(DataStore $store = null, $path)
    {
        $this->store = $store;

        $this->path = $path;
    }

    public function get(Model $object)
    {
        $inputFileName = $this->path;
        $spreadsheet = IOFactory::load($inputFileName);
        $highest = $spreadsheet->getActiveSheet()->getHighestRowAndColumn();

        $fields = array();
        $data = array();
        for($row=1; $row<=$highest['row']; $row++)
        {
            $loadedData = array();
            foreach (range('A', $highest['column']) as $column)
            {
                $value = $spreadsheet->getActiveSheet()->getCell($column . $row)->getValue();
                $loadedData[] = $value;
            }
            if($row === 1)
            {
                $fields = $loadedData;
            }
            else
            {
                $data[] = $loadedData;
            }
        }

        $dataList = array();
        $className = get_class($object);
        foreach($data as $datum)
        {
            $data = new $className;
            for($i=0; $i<count($datum); $i++)
            {
                $name = $fields[$i];
                $value = $datum[$i];
                $data->$name = $value;
            }
            $dataList[] = $data;
        }

        $identifiers = $object->getIdentifiers();
        $ret = array();
        $count = 0;
        $depth = $this->getDepth($identifiers, $object);

        foreach($dataList as $data)
        {
            foreach($identifiers as $identifier)
            {
                if($data->$identifier === $object->$identifier)
                {
                    $count++;
                }
                else
                {
                    break;
                }
            }

            if($count >= $depth)
            {
                $ret[] = $data;
            }
        }

        return $ret;
    }

    private function getDepth($identifiers, $object)
    {
        $depth = 0;
        foreach($identifiers as $identifier)
        {
            if(isset($object->$identifier))
            {
                $depth++;
            }
            else
            {
                break;
            }
        }

        return $depth;
    }

    /**
     * @param Model $object
     * @return int $rowCount;
     */
    public function set(Model $object)
    {
        // TODO: Implement set() method.
    }

    /**
     * @param Model $object
     * @return Model[];
     */
    public function add(Model $object)
    {
        // TODO: Implement add() method.
    }

    /**
     * @param Model $object
     * @return int
     */
    public function remove(Model $object)
    {
        // TODO: Implement remove() method.
    }

    public function flush()
    {
        throw new \Exception('not use this function');
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }

    /**
     * @param Model[] $objects
     * @return int
     */
    public function removeMulti($objects)
    {
        // TODO: Implement removeMulti() method.
    }

    public function setChangedAttributes(Model $object, $changedAttributes)
    {
        // TODO: Implement setChangedAttributes() method.
    }

    public function getLastAddedDataList()
    {
        // TODO: Implement getLastAddedDataList() method.
    }
}