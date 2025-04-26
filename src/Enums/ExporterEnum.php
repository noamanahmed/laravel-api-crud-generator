<?php
namespace NoamanAhmed\ApiCrudGenerator\Enums;

enum ImporterEnum: string {
    use BaseEnum;

    case CSV = 'csv';
    case XLSX = 'xlsx';
    case PDF = 'pdf';

}
