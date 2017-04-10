<?php

namespace battlecook\DataStore;

use battlecook\DataObject\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelDataStore extends BufferDataStore implements DataStore
{
    private $store;

    private $path;

    public function __construct(DataStore $store = null, $path)
    {
        $this->buffer = array();
        $this->store = $store;

        $this->path = $path;
    }

    public function get(Model $object)
    {
        if(empty($this->buffer))
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

            $className = get_class($object);
            foreach($data as $datum)
            {
                $object = new $className;
                for($i=0; $i<count($datum)-1; $i++)
                {
                    $name = $fields[$i];
                    $value = $datum[$i];
                    $object->$name = $value;
                }

                $this->buffer[] = array(self::NODE => $object, self::STATE => DataState::NOT_CHANGED);
            }
        }

        if(empty($this->buffer) && $this->store)
        {
            $storedData = $this->store->get($object);
            foreach($storedData as $data)
            {
                $this->buffer[] = array(self::NODE => $data, self::STATE => DataState::NOT_CHANGED);
            }
        }

        $ret = parent::get($object);

        return $ret;
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
        // TODO: Implement flush() method.
    }

    public function rollback()
    {
        // TODO: Implement rollback() method.
    }
}