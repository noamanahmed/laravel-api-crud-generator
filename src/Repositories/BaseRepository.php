<?php

namespace NoamanAhmed\ApiCrudGenerator\Repositories;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use NoamanAhmed\ApiCrudGenerator\BaseFilterContract;
use NoamanAhmed\ApiCrudGenerator\Contracts\BaseRepositoryContract;
use RuntimeException;
use Spatie\QueryBuilder\QueryBuilder;

class BaseRepository implements BaseRepositoryContract
{
    protected Model $model;

    protected ?BaseFilterContract $filter;

    protected string $primaryKey = 'id';

    protected string $paginator = 'simple';

    protected int $perPage = 20;

    protected array $with = [];

    protected array $withCount = [];

    protected array $scopes = [];

    protected array $queryFilters = [];

    protected array $filters = [];

    protected array $searchableFilters = [];

    protected array $sorters = [];

    protected string $defaultSorter = 'id';

    protected string $defaultSorterDirection = 'asc';

    protected ?string $searchQuery = null;

    protected bool $supportsFullTextSearch = false;

    protected array $defaultDropdownFields = ['id'];

    public function __construct()
    {
        $this->buildOptionsFromRequest();
    }

    public function setModel(Model|Authenticatable $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): Model|Authenticatable
    {
        return $this->model;
    }

    public function buildOptionsFromRequest(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $options = request()->get('options') ?? [];

        if (! empty($options['page'])) {
            request()->merge(['page' => $options['page']]);
        }

        if (! empty($options['itemsPerPage']) && (int) $options['itemsPerPage'] >= 0 && (int) $options['itemsPerPage'] <= 100) {
            $this->perPage = (int) $options['itemsPerPage'];
        }

        if (! empty(request()->get('q'))) {
            $this->searchQuery = request()->get('q');
        }

        if (! empty($options['sortBy'])) {
            $sortByOptions = $options['sortBy'];
            $sortString = '';

            foreach ($sortByOptions as $sortOptions) {
                if (! isset($sortOptions['key'], $sortOptions['order'])) {
                    continue;
                }

                $sortString .= strtolower($sortOptions['order']) === 'desc' ? '-'.$sortOptions['key'] : $sortOptions['key'];
            }

            if (! empty($sortString)) {
                request()->merge(['sort' => $sortString]);
            }
        }
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $builder = $this->model->query();

        $queryBuilder = QueryBuilder::for($builder);

        if (! empty($this->filters)) {
            $queryBuilder->allowedFilters($this->filters);
        }

        if (! empty($this->sorters)) {
            $queryBuilder->allowedSorts($this->sorters);
        }

        if (! empty($this->defaultSorter) && ! empty($this->defaultSorterDirection)) {
            $direction = strtolower($this->defaultSorterDirection);

            if (! in_array($direction, ['asc', 'desc'])) {
                throw new RuntimeException('The sort direction must be either ASC or DESC.');
            }

            $queryBuilder->defaultSort($direction === 'desc' ? '-'.$this->defaultSorter : $this->defaultSorter);
        }

        if (! empty($this->searchQuery)) {
            if ($this->supportsFullTextSearch) {
                $queryBuilder->whereFullText($this->searchableFilters, $this->searchQuery.'*', [
                    'mode' => 'boolean',
                ]);
            } else {
                $queryBuilder->where(function ($query) {
                    foreach ($this->searchableFilters as $i => $filter) {
                        if ($i === 0) {
                            $query->where($filter, 'LIKE', '%'.$this->searchQuery.'%');
                        } else {
                            $query->orWhere($filter, 'LIKE', '%'.$this->searchQuery.'%');
                        }
                    }
                });
            }
        }

        if (! empty($this->with)) {
            $queryBuilder->with($this->with);
        }

        if (! empty($this->withCount)) {
            $queryBuilder->withCount($this->withCount);
        }

        if (! empty($this->scopes)) {
            $queryBuilder->scopes($this->scopes);
        }

        foreach ($this->queryFilters as $filter) {
            $queryBuilder = $filter($queryBuilder);
        }

        if ($this->filter instanceof BaseFilterContract) {
            $queryBuilder = app($this->filter)->apply($queryBuilder);
        }

        return $queryBuilder;
    }

    public function index(): mixed
    {
        $queryBuilder = $this->getQueryBuilder();

        if ($this->paginator === 'simple') {
            return $queryBuilder->paginate($this->perPage);
        }

        return $queryBuilder;
    }

    public function dropdown(): mixed
    {
        $this->with = [];
        $this->withCount = [];

        return $this->getQueryBuilder()->select($this->defaultDropdownFields)->get();
    }

    public function find(int $id): ?Model
    {
        return $this->model->with($this->with)->withCount($this->withCount)->find($id);
    }

    public function get(int $id): ?Model
    {
        return $this->model->with($this->with)->withCount($this->withCount)->find($id);
    }

    public function pluckIds(): Collection
    {
        return $this->model->pluck($this->primaryKey);
    }

    public function store(array $data): Model
    {
        $this->model->fill($data)->save();
        $this->model->refresh();

        return $this->model;
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->model->findOrFail($id);
        $model->fill($data)->save();
        $model->refresh();

        return $model;
    }

    public function destroy(int $id): bool
    {
        return (bool) $this->model->where($this->primaryKey, $id)->delete();
    }

    public function destroyMulti(array $ids): bool
    {
        return (bool) $this->model->whereIn($this->primaryKey, $ids)->delete();
    }

    protected function getWith(): array
    {
        return $this->with;
    }

    protected function getWithCount(): array
    {
        return $this->withCount;
    }

    protected function getFilters(): array
    {
        return $this->filters;
    }

    protected function getSorters(): array
    {
        return $this->sorters;
    }

    protected function getScopes(): array
    {
        return $this->scopes;
    }

    protected function getSearchableFilters(): array
    {
        return $this->searchableFilters;
    }

    public function addQueryFilter(callable $callback): static
    {
        $this->queryFilters[] = $callback;

        return $this;
    }

    public function getQueryFilters(): array
    {
        return $this->queryFilters;
    }
}
