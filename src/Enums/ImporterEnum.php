<?php

namespace NoamanAhmed\ApiCrudGenerator\Enums;

enum ExporterEnum: string
{
    use BaseEnum;

    case CSV = 'csv';
    case XLSX = 'xlsx';
    case PDF = 'pdf';

}
