<?php

namespace NoamanAhmed\ApiCrudGenerator\Imports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use NoamanAhmed\ApiCrudGenerator\Contracts\BaseImporterContract;
use NoamanAhmed\ApiCrudGenerator\Enums\ImporterEnum;

abstract class BaseImporter implements BaseImporterContract
{
    /**
     * Allows import of dependend relationships
     *
     * @var bool
     */
    public $importRelationsShips = true;

    /**
     * The default format to import the data
     *
     * @var \NoamanAhmed\ApiCrudGenerator\Enums\ImporterEnum
     */
    public $defaultImportFormat = ImporterEnum::CSV;

    /**
     * The selected format to import the data.
     *
     * @var \NoamanAhmed\ApiCrudGenerator\Enums\ImporterEnum
     */
    public $importFormat = ImporterEnum::CSV;

    /**
     * The number of records to batch insert.
     *
     * @var int
     */
    public $importQueryChunk = 20;

    /**
     * The Eloquent Model to import the data
     *
     * @var Model
     */
    public $model;

    /**
     * Importer errors.
     *
     * @var array
     */
    public $importErrors = [];

    /**
     * The column name mapping to translations.
     */
    const TRANSLATIONS_COLUMN_NAME = 'translations';

    /**
     * The column name mapping to entity name to identify correct file upload.
     */
    const ENTITY_NAME_COLUMN_NAME = 'entityName';

    public function importRelationsShipsBeforeMainImport($data, $columns)
    {

        if (! $this->importRelationsShips) {
            return;
        }
        $relations = [];
        foreach ($this->relations() as $relation) {
            // Only run if set to run BEFORE main importer
            if (! ($relation['runBeforeMainImporter'])) {
                continue;
            }
            $relations[] = $relation;
        }

        $this->importRelationsShips($data, $relations, $columns);

    }

    public function importRelationsShipsAfterMainImport($data, $columns)
    {
        if (! $this->importRelationsShips) {
            return;
        }
        $relations = [];
        foreach ($this->relations() as $relation) {
            // Only run if set to run AFTER main importer
            if ($relation['runBeforeMainImporter']) {
                continue;
            }
            $relations[] = $relation;
        }

        $this->importRelationsShips($data, $relations, $columns);

    }

    public function importRelationsShips($data, $relations, $columns)
    {
        if (! $this->importRelationsShips) {
            return;
        }
        if (empty($relations)) {
            return;
        }

        foreach ($relations as $relation) {
            $this->importRelationsShip($data, $relation, $columns);
        }

    }

    public function importRelationsShip($data, $relation, $columns)
    {
        if (! $this->importRelationsShips) {
            return;
        }
        $data = collect($data);
        $relationName = Str::snake($relation['columnName']);
        $oldData = $data;
        $data = $data->pluck($relationName)->filter()->collapse();
        $dataChunks = $data;
        if (($relation['relationType'] ?? null) !== 'parent') {
            $dataChunks = $data->chunk($this->importQueryChunk);
        }
        DB::beginTransaction();
        $importer = new $relation['importer'];
        $objModel = new ($importer->getModel());
        // having multiple parent relationModels.
        if (isset($relation['relationType']) && $relation['relationType'] === 'parent') {
            $relationData = $dataChunks->toArray();
            foreach ($relationData as $key => $column) {
                if (in_array($key, $objModel->getFillable())) {
                    continue;
                }

                unset($relationData[$key]);
            }

            $objModel->upsert($relationData, $importer->uniqueColumns(), array_keys($relationData));
        } else {
            foreach ($dataChunks as $chunk) {
                $chunk = $chunk->map(function ($row) use (&$objModel, &$importer) {
                    foreach ($row as $key => $column) {
                        if (in_array($key, $objModel->getFillable())) {
                            continue;
                        }
                        if (in_array($key, $importer->uniqueColumns())) {
                            continue;
                        }
                        unset($row[$key]);
                    }

                    return $row;
                });
                $objModel->upsert($chunk->toArray(), $importer->uniqueColumns(), array_keys($chunk->first()));
            }
        }
        DB::commit();
    }

    public function import($data)
    {
        $data = collect($data);
        $dataChunks = $data->chunk($this->importQueryChunk);
        DB::beginTransaction();
        foreach ($dataChunks as $chunk) {
            $objModel = new $this->model;
            $objModel->upsert($chunk->toArray(), $this->uniqueColumns(), array_keys($chunk->first()));
        }
        DB::commit();
    }

    public function getColumnListFromHeaderRow($results)
    {
        $headerRow = [];
        if (empty($results)) {
            return $headerRow;
        }
        foreach ($results[0] as $key => $column) {
            if (! in_array($column, $this->headerColumns())) {
                continue;
            }
            if (in_array($column, $this->skipHeaderColumns())) {
                continue;
            }
            $headerRow[] = $column;
        }

        return $headerRow;
    }

    public function validColumnListInHeaderRow(array $headerRow)
    {
        foreach ($headerRow as $column) {
            if (
                ! in_array($column, $this->columns()) &&
                ! in_array($column, $this->skipHeaderColumns()) &&
                ! in_array($column, $this->relationsColumns())
            ) {

                $this->importErrors[] = 'The header row contains an invalid column : '.$column;

                return false;
            }
        }

        return true;
    }

    public function buildDataRows($results, $columns, $headerRow)
    {
        $dataRows = [];
        if (empty($results)) {
            return $dataRows;
        }
        foreach ($results as $key => $row) {
            $rowData = [];
            $skippedColumns = 0;
            foreach ($row as $key2 => $value) {
                // Skip Header column and fix offset
                if (in_array($headerRow[$key2], $this->skipHeaderColumns())) {
                    $skippedColumns++;

                    continue;
                }
                if (in_array($headerRow[$key2], $this->relationsColumns())) {
                    $skippedColumns++;

                    continue;
                }
                if (! in_array($columns[$key2 - $skippedColumns], $this->columns())) {
                    continue;
                }
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $rowData[$columns[$key2 - $skippedColumns]] = $value;
            }
            $dataRows[] = $rowData;
        }

        return $dataRows;
    }

    public function buildRelationShipRows($results, $columns, $headerRow)
    {
        $relationShipRows = [];
        if (empty($results)) {
            return $relationShipRows;
        }
        foreach ($results as $key => $row) {
            $rowData = [];
            $skippedColumns = 0;
            foreach ($row as $key2 => $value) {
                // Skip Header column and fix offset
                if (in_array($headerRow[$key2], $this->skipHeaderColumns())) {
                    $skippedColumns++;

                    continue;
                }
                if (in_array($headerRow[$key2], $this->columns())) {
                    $skippedColumns++;

                    continue;
                }
                if (! in_array($headerRow[$key2], $this->relationsColumns())) {
                    continue;
                }
                $rowData[$headerRow[$key2]] = json_decode(base64_decode($value), true);
                if (is_null($rowData[$headerRow[$key2]])) {
                    continue;
                }
            }
            $relationShipRows[] = $rowData;
        }

        return $relationShipRows;
    }

    public function switchFormat($format): BaseImporterContract
    {
        $this->importFormat = constant('App\Enums\ImporterEnum::'.$format);

        return $this;
    }

    public function columns(): array
    {
        return [
            'id',
        ];
    }

    public function headerColumns(): array
    {
        return [
            'id',
        ];
    }

    public function skipHeaderColumns(): array
    {
        return [
            self::ENTITY_NAME_COLUMN_NAME,
            self::TRANSLATIONS_COLUMN_NAME,
        ];
    }

    public function uniqueColumns(): array
    {
        return [
            'id',
        ];
    }

    public function relations(): array
    {
        // Architecture to implement in child class to import relationsips
        // return [
        //     [
        //         'columnName' => '', // Column name to get the relationship data.
        //         'importTranslation' =>  true, // Import the relationships translations
        //         'runBeforeMainImporter' => true, // Imports dependend data before or after main data import
        //         'importer' => '', // The importer class dependent to import the data
        //     ]
        // ];

        // Override in child classes
        return [];
    }

    public function relationsColumns()
    {
        $columns = [];
        foreach ($this->relations() as $relation) {
            if (! array_key_exists('columnName', $relation)) {
                continue;
            }
            $columns[] = Str::snake($relation['columnName']);
        }

        return $columns;
    }

    public function fromRawCSV()
    {

        $data = request()->get('data', '');
        $data = $this->csv2array($data);
        // Only run importer if atleast one record is present exclusing header row
        if (empty($data) || count($data) < 2) {
            $this->importErrors[] = 'The file you are trying to upload is empty.';

            return 'ERROR';
        }
        $columns = $this->getColumnListFromHeaderRow($data);
        if (empty($columns)) {
            $this->importErrors[] = 'The file you are trying to upload doesn\'t have a header row.';

            return 'ERROR';

        }

        if (! $this->validColumnListInHeaderRow($data[0])) {
            $this->importErrors[] = 'The file you are trying to upload has an invalid header row.';

            return 'ERROR';

        }

        $headerRow = array_shift($data);

        $dataRows = $this->buildDataRows($data, $columns, $headerRow);
        $relationshipRows = $this->buildRelationShipRows($data, $columns, $headerRow);
        // Multiple and nested transactions to handle data and translations seperately but roll back if one fails
        // Adds performance overhead with nested transactions but ensure strong data consistency.
        DB::beginTransaction();
        $this->importRelationsShipsBeforeMainImport($relationshipRows, $columns);
        $this->import($dataRows);
        $this->importRelationsShipsAfterMainImport($relationshipRows, $columns);
        // Confirm if data rows import was successfull
        if (! $this->isSuccessfullImport()) {
            // Rollback data rows if there were any errors
            DB::rollBack();

            return 'ERROR';
        }
        // Confirm if translations import were successfull
        if ($this->isSuccessfullImport() == false) {
            // Rollback translations rows if there were any errors
            DB::rollBack();

            return 'ERROR';
        }
        DB::commit();

        return 'OK';

    }

    public function fromRawXLSX()
    {
        return 'TODO';
        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function fromRawPdf()
    {
        return 'TODO';
        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function fromCSV()
    {
        return 'TODO';
    }

    public function fromXLSX()
    {
        return 'TODO';

        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function fromPdf()
    {
        return 'TODO';
        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function isSuccessfullImport()
    {
        if (count($this->importErrors) >= 1) {
            return false;
        }

        return true;
    }

    public function getErrors()
    {
        return $this->importErrors;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function csv2Array($csv, $delimiter = ',', $enclosure = '"', $escape_char = '\\')
    {
        $f = fopen('php://memory', 'r+');
        fwrite($f, $csv);
        rewind($f);
        $data = [];
        while (($row = fgetcsv($f, 0, $delimiter, $enclosure, $escape_char)) !== false) {
            $data[] = $row;
        }
        fclose($f);

        return $data;
    }
}
