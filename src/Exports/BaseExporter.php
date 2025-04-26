<?php

namespace NoamanAhmed\ApiCrudGenerator\Exports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use NoamanAhmed\ApiCrudGenerator\Contracts\BaseExporterContract;
use NoamanAhmed\ApiCrudGenerator\Enums\ExporterEnum;

abstract class BaseExporter implements BaseExporterContract
{
    /**
     * Allows enable/disabling translations export for the modal
     *
     * @var bool
     */
    public $exportTranslations = true;

    /**
     * Allows exporting of dependend relationships
     *
     * @var bool
     */
    public $exportRelationsShips = true;

    /**
     * The default format to export the data
     *
     * @var \NoamanAhmed\ApiCrudGenerator\Enums\ExporterEnum
     */
    public $defaultExportFormat = ExporterEnum::CSV;

    /**
     * The selected format to export the data.
     *
     * @var \NoamanAhmed\ApiCrudGenerator\Enums\ExporterEnum
     */
    public $exportFormat = ExporterEnum::CSV;

    /**
     * The Eloquent Model to export the data
     *
     * @var Model
     */
    public $model;

    /**
     * The column name mapping to translations.
     */
    const TRANSLATIONS_COLUMN_NAME = 'translations';

    /**
     * The column name mapping to entity name to identify correct file upload.
     */
    const ENTITY_NAME_COLUMN_NAME = 'entityName';

    public function data()
    {
        $data = [];
        $request = request();
        $modelObj = new $this->model;
        $results = $modelObj
            ->when(! empty($request->ids) && is_array($request->ids), function ($records) use ($request) {
                return $records->whereIn('id', (array) $request->ids);
            })
            ->when(! empty($request->type) && is_array($request->ids) && $request->type === 'module', function ($records) use ($request) {
                return $records->where('module_id', (array) $request->module_id);
            });
        if ($this->exportRelationsShips) {
            $results = $results->with($this->relations());
        }
        $results = $results->get()->toArray();
        if (empty($results)) {
            return $data;
        }
        $data[0] = $this->buildHeaderRow($results);
        $data = [...$data, ...$this->buildDataRows($results)];

        return $data;
    }

    public function buildHeaderRow($results)
    {
        $headerRow = [];
        if (empty($results)) {
            return $headerRow;
        }
        $headerRow[] = self::ENTITY_NAME_COLUMN_NAME;

        foreach ($results[0] as $key => $column) {
            if (! in_array($key, $this->headerColumns())) {
                continue;
            }
            $headerRow[] = $key;
        }
        foreach ($results[0] as $key => $column) {
            if (! $this->exportRelationsShips) {
                continue;
            }
            $relationShipColumnIndex = Str::camel($key);
            if (! in_array($relationShipColumnIndex, $this->relations())) {
                continue;
            }
            $headerRow[] = $key;
        }

        if ($this->exportTranslations) {
            $headerRow[] = self::TRANSLATIONS_COLUMN_NAME;
        }

        return $headerRow;
    }

    public function buildDataRows($results)
    {
        $dataRows = [];
        if (empty($results)) {
            return $dataRows;
        }
        foreach ($results as $key => $row) {
            $rowData = [];
            $rowData[] = $this->model;
            $relationIds = [];
            foreach ($row as $key2 => $column) {
                if (! in_array($key2, $this->columns())) {
                    continue;
                }
                if (is_array($column)) {
                    $column = json_encode($column);
                }
                $rowData[] = $column;
            }
            foreach ($row as $key2 => $relation) {
                if (! $this->exportRelationsShips) {
                    continue;
                }
                $relationShipColumnIndex = Str::camel($key2);
                if (! in_array($relationShipColumnIndex, $this->relations())) {
                    continue;
                }
                foreach ($relation ?? [] as $relationModel) {
                    $relationIds[$key2][] = $relationModel['id'] ?? null;
                }

                $relation = base64_encode(json_encode($relation));
                $rowData[] = $relation;
            }
            $dataRows[] = $rowData;
        }

        return $dataRows;
    }

    public function switchFormat($format): BaseExporterContract
    {
        $this->exportFormat = constant('App\Enums\ExporterEnum::'.$format);

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

    public function relations(): array
    {
        // Override in child classes
        return [];
    }

    public function toRawCSV()
    {
        $data = $this->data();

        return $this->array2csv($data);
    }

    public function toRawXLSX()
    {
        return 'TODO';
        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function toRawPdf()
    {
        return 'TODO';

        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function toCSV()
    {
        return 'TODO';

    }

    public function toXLSX()
    {
        return 'TODO';

        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function toPdf()
    {
        return 'TODO';

        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function array2csv($data, $delimiter = ',', $enclosure = '"', $escape_char = '\\')
    {
        $f = fopen('php://memory', 'r+');
        foreach ($data as $item) {
            fputcsv($f, $item, $delimiter, $enclosure, $escape_char);
        }
        rewind($f);

        return stream_get_contents($f);
    }
}
