<?php
declare(strict_types=1);

namespace battlecook\DataAccessor;

use battlecook\DataCookerException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class Spreadsheet extends AbstractMeta implements IDataAccessor
{
    private $storage;

    private $spreadsheet;


    /**
     * $metaMap[$sheetName] = array( column1, column2, ... )
     */
    static private $columnsMap;

    /**
     * Spreadsheet constructor.
     * @param IDataAccessor|null $storage
     * @param \battlecook\Config\Spreadsheet $config
     * @throws DataCookerException
     */
    public function __construct(?IDataAccessor $storage, \battlecook\Config\Spreadsheet $config)
    {
        $this->storage = $storage;

        if (file_exists($config->getPath()) === false) {
            throw new DataCookerException("this path is invalid path.");
        }
        try {
            $this->spreadsheet = IOFactory::load($config->getPath());
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            throw new DataCookerException($e);
        }

        foreach ($this->spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $this->spreadsheet->getSheetByName($sheetName);

            $columns = array();

            $start = 1;
            $count = 0;
            while (true) {
                $column = $sheet->getCellByColumnAndRow($start + $count, 1);
                if ($column->getValue() === null) {
                    break;
                }
                $columns[] = $column->getValue();
                $count++;
            }

            if (empty($columns) === true) {
                throw new DataCookerException("this sheet is empty");
            }

            self::$columnsMap[$sheetName] = $columns;

            /*
            $start = 2;
            $count = 0;
            while (true) {
                $row = $sheet->getCellByColumnAndRow(1, $start + $count);
                if ($row->getValue() === null) {
                    break;
                }
                $rows[] = $row->getValue();
                $count++;
            }
            */

        }
    }

    /**
     * @param $object
     * @return mixed
     * @throws DataCookerException
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function add($object)
    {
        $cacheKey = get_class($object);
        $this->setMeta($object);
        $this->checkField($cacheKey, $object);

        $explodedObject = explode('\\', $cacheKey);
        $sheetName = end($explodedObject);
        if (isset(self::$columnsMap[$sheetName]) === false) {
            throw new DataCookerException("not exist sheet");
        }

        $fields = $this->getFieldsWithAutoIncrement($cacheKey);
        $columns = self::$columnsMap[$sheetName];
        $diff = array_diff($fields, $columns);
        if (count($diff) > 0) {
            throw new DataCookerException("difference fields and columns");
        }

        $ret = $this->get($object);
        if (empty($ret) === false) {
            throw new DataCookerException("already data inserted");
        }


        /*
        $sheet = $this->spreadsheet->getSheetByName($sheetName);
        $header = $sheet->getCellByColumnAndRow(1, 1);

        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Hello World !');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('hello world.xlsx');
        */

        return clone $object;
    }

    public function get($object): array
    {
        $cacheKey = get_class($object);
        $this->setMeta($object);


        $explodedObject = explode('\\', $cacheKey);
        $sheetName = end($explodedObject);
        if (isset(self::$columnsMap[$sheetName]) === false) {
            throw new DataCookerException("not exist sheet");
        }

        $fields = $this->getFieldsWithAutoIncrement($cacheKey);
        $columns = self::$columnsMap[$sheetName];
        $diff = array_diff($fields, $columns);
        if (count($diff) > 0) {
            throw new DataCookerException("difference fields and columns");
        }


        $sheet = $this->spreadsheet->getSheetByName($sheetName);


        for ($i = 1; $i < count($columns); $i++) {
            //for ($j = 0; $j < c)
            {

            }
        }

        //$sheet->getCellByColumnAndRow()

        //$identifierValues = $this->getIdentifierValues($cacheKey, $object);


        return array();
    }

    public function set($object)
    {
        $cacheKey = get_class($object);
        $this->setUpMeta($cacheKey, $object);
        $this->checkField($cacheKey, $object);
    }

    public function remove($object)
    {
    }

    public function initialize()
    {
        self::$columnsMap = array();
    }
}