<?php
declare(strict_types=1);

namespace battlecook\DataStore;

use battlecook\DataCookerException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class Spreadsheet extends AbstractStore implements IDataStore
{
    private $store;

    private $spreadsheet;

    /**
     * $metaMap[$sheetName] = array( column1, column2, ... )
     */
    static private $columnsMap;

    /**
     * Spreadsheet constructor.
     * @param IDataStore|null $store
     * @param \battlecook\Config\Spreadsheet $config
     * @throws DataCookerException
     */
    public function __construct(?IDataStore $store, \battlecook\Config\Spreadsheet $config)
    {
        $this->store = $store;

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
                $columns[$column->getValue()] = $start + $count;
                $count++;
            }

            if (empty($columns) === true) {
                throw new DataCookerException("this sheet is empty");
            }

            self::$columnsMap[$sheetName] = $columns;
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
        $this->checkHaveAllFieldData($cacheKey, $object);

        $explodedObject = explode('\\', $cacheKey);
        $sheetName = end($explodedObject);
        if (isset(self::$columnsMap[$sheetName]) === false) {
            throw new DataCookerException("not exist sheet");
        }

        $fields = $this->getFieldKeysWithAutoIncrement($cacheKey);
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

        $fields = $this->getFieldKeysWithAutoIncrement($cacheKey);
        $columns = self::$columnsMap[$sheetName];
        $diff = array_diff($fields, array_keys($columns));
        if (count($diff) > 0) {
            throw new DataCookerException("difference fields and columns");
        }

        $ret = array();
        $sheet = $this->spreadsheet->getSheetByName($sheetName);
        $rowCount = 2;
        if ($this->isGetAll($cacheKey, $object) === true) {
            while (true) {
                if ($sheet->getCellByColumnAndRow(1, $rowCount)->getValue() === null) {
                    break;
                }

                $tmp = new $object();
                foreach ($this->getFieldKeys($cacheKey) as $field) {
                    $index = $columns[$field];
                    $cell = $sheet->getCellByColumnAndRow($index, $rowCount);
                    $tmp->$field = $cell->getValue();
                }

                $ret[] = $tmp;

                $rowCount++;
            }
        } else {
            while (true) {
                if ($sheet->getCellByColumnAndRow(1, $rowCount)->getValue() === null) {
                    break;
                }

                $count = 0;
                foreach ($this->getIdentifierKeys($cacheKey) as $identifier) {
                    $index = $columns[$identifier];
                    $cell = $sheet->getCellByColumnAndRow($index, $rowCount);
                    if ($cell->getValue() === null) {
                        break;
                    }

                    if ($object->$identifier == $cell->getValue()) {
                        $count++;
                    } else {
                        break;
                    }
                }
                if (count($this->getIdentifierKeys($cacheKey)) === $count) {

                    $tmp = new $object();
                    foreach ($columns as $column => $index) {

                        $cell = $sheet->getCellByColumnAndRow($index, $rowCount);
                        $tmp->$column = $cell->getValue();
                    }

                    $ret[] = $tmp;
                }

                $rowCount++;
            }
        }

        return $ret;
    }

    public function set($object)
    {
        $cacheKey = get_class($object);
        $this->setUpMeta($cacheKey, $object);
        $this->checkHaveAllFieldData($cacheKey, $object);
    }

    public function remove($object)
    {
    }

    public static function initialize()
    {
        parent::initialize();
        self::$columnsMap = array();
    }

    public function commit($data = null)
    {

    }
}