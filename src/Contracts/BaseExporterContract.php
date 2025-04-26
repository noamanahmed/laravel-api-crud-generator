<?php

namespace NoamanAhmed\Exporters;


interface BaseExporterContract
{
    /**
     * Gets the data to export
     *
     * @return mixed
     */
    public function data();

    /**
     * Changes format of the export
     *
     * @param  string $format
     */
    public function switchFormat($format): self;

    /**
     * The database columns which are required to be exported
     *
     * @return array
     */
    public function columns();

    /**
     * The header row columns which will be the first row in the exported file.
     *
     * @return array
     */
    public function headerColumns();

    /**
     * The dependent relationship to import first.
     *
     * @return void
     */
    public function relations();

    /**
     * Generates a CSV file on S3 Storage and returns the file URL to download
     *
     * @return string
     */
    public function toCSV();

    /**
     * Generates a XLSX file on S3 Storage and returns the file URL to download
     *
     * @return string
     */
    public function toXLSX();

    /**
     * Generates a PDF file on S3 Storage and returns the file URL to download
     *
     * @return string
     */
    public function toPdf();

    /**
     * Generates raw CSV contents which can be then send using a JSON response. Inefficient for big payloads
     *
     * @return string
     */
    public function toRawCSV();

    /**
     * Generates raw XLSX contents which can be then send using a JSON response. Inefficient for big payloads
     *
     * @return string
     */
    public function toRawXLSX();

    /**
     * Generates raw PDF contents which can be then send using a JSON response. Inefficient for big payloads
     *
     * @return string
     */
    public function toRawPdf();
}
