<?php

namespace NoamanAhmed\Importers;

use NoamanAhmed\Enums\ImporterEnum;

interface BaseImporterContract
{
    /**
     * Runs the actual importer
     *
     * @return mixed
     */
    public function import($data);

    /**
     * Imports the required relationships before running the main import
     *
     * @param  array  $data
     * @param  array  $columns
     * @return void
     */
    public function importRelationsShipsBeforeMainImport($data, $columns);

    /**
     * Imports the required relationships before running the main import
     *
     * @param  array  $data
     * @param  array  $columns
     * @return void
     */
    public function importRelationsShipsAfterMainImport($data, $columns);

    /**
     * Imports the relationships
     *
     * @param  array  $data
     * @param  array  $relations
     * @param  array  $columns
     * @return void
     */
    public function importRelationsShips($data, $relations, $columns);

    /**
     * Changes format of the import
     *
     * @param  string  $format
     */
    public function switchFormat($format): self;

    /**
     * The database columns which are required to be imported
     *
     * @return array
     */
    public function columns();

    /**
     * The header row columns which will be the first row in the imported file.
     *
     * @return array
     */
    public function headerColumns();

    /**
     * The relations which need to be imported.
     *
     * @return array
     */
    public function relations();

    /**
     * Imports a CSV file from S3 Storage
     *
     * @return string
     */
    public function fromCSV();

    /**
     * Imports a XLSX file from S3 Storage
     *
     * @return string
     */
    public function fromXLSX();

    /**
     * Imports raw CSV contents which can be then send using a JSON response. Inefficient for big payloads
     *
     * @return string
     */
    public function fromRawCSV();

    /**
     * Imports raw XLSX contents which can be then send using a JSON response. Inefficient for big payloads
     *
     * @return string
     */
    public function fromRawXLSX();
}
