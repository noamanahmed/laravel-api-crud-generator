<?php

namespace NoamanAhmed\Exporters;

use Illuminate\Support\Str;
use NoamanAhmed\Enums\ExporterEnum;
use NoamanAhmed\Translation;

abstract class BaseExporter implements BaseExportContract
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
     * @var [ExporterEnum]
     */
    public $defaultExportFormat = ExporterEnum::CSV;

    /**
     * The selected format to export the data.
     *
     * @var [ExporterEnum]
     */
    public $exportFormat = ExporterEnum::CSV;

    /**
     * The Eloquent Model to export the data
     *
     * @var [Modal]
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
            ->when(! empty($request->ids) && is_array($request->ids) && count($request->ids) > 0, function ($records) use ($request) {
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
            if ($this->exportTranslations) {
                $translations = [];
                $model = $this->model::find($row['id']);
                if (! empty($model)) {
                    foreach ($this->buildModelTranslations($model) as $translation) {
                        $translations[] = $translation;
                    }
                }

                foreach ($this->buildModelRelationsTranslations($relationIds) as $translation) {
                    $translations[] = $translation;
                }
                $rowData[] = base64_encode(json_encode($translations));
            }
            $dataRows[] = $rowData;
        }

        return $dataRows;
    }

    public function buildModelTranslations($model)
    {
        $language_ids = activeLanguages()->pluck('id', 'territory_code')->toArray();
        $translations = Translation::whereIn('language_id', $language_ids)->where('key', 'like', translationKeyBase($model).'%')->with(['language'])->get()->groupBy('language_id')->toArray();
        $output = [];

        foreach ($translations as $key => $singleLanguageTranslations) {
            foreach ($singleLanguageTranslations as $translation) {
                $output[$translation['language']['territory_code']][] = [
                    'id' => $translation['id'],
                    'key' => $translation['key'],
                    'value' => $translation['value'],
                    'language_id' => $translation['language']['id'],
                ];
            }
        }

        return $output;
    }

    public function buildModelRelationsTranslations($relationsIds)
    {
        $output = [];
        $objModel = new $this->model;
        if (empty($relationsIds)) {
            return $output;
        }
        foreach ($this->relations() as $relation) {
            if (! array_key_exists($relation, $relationsIds)) {
                continue;
            }
            $model = $objModel->$relation()->getRelated();
            foreach ($relationsIds[$relation] as $relationId) {
                $relationModel = $model->find($relationId);
                if (empty($relationModel)) {
                    continue;
                }
                $relationModelTranslations = $this->buildModelTranslations($relationModel);
                if (empty($relationModelTranslations)) {
                    continue;
                }
                foreach ($relationModelTranslations as $key => $relationModelTranslationsByLanguageCode) {
                    $output[$key] = [...($output[$key] ?? []), ...$relationModelTranslationsByLanguageCode];
                }
            }
        }

        return $output;
    }

    public function switchFormat($format): BaseExportContract
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
        return [

        ];
    }

    public function toRawCSV()
    {
        $data = $this->data();

        return $this->array2csv($data);
    }

    public function toRawXLSX()
    {
        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function toRawPdf()
    {
        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function toCSV() {}

    public function toXLSX()
    {
        // TODO
        // Architecture written. Conversion will be done over here.
    }

    public function toPdf()
    {
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
