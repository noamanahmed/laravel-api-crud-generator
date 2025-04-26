<?php

namespace App\Repositories;

use App\Models\Lead;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BaseRepository implements BaseRepositoryContract{
    /**
     * The Model to interact with
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Default Primary Key Column Name (ex: id)
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * All available paginators. Currently only simple pagination with OFFSET and LIMIT is supported
     *
     * @var string
     */
    protected string $paginator = 'simple'; // TODO: Implement Cursor based pagination

    /**
     * The maximum number of records per page in a paginated response
     *
     * @var int
     */
    protected int $perPage = 20;

    /**
     * The list of Eloquent Relationships to eagerload
     *
     * @var array
     */
    protected array $with = [];

    /**
     * The list of count of Eloquent Relationships. This will use COUNT on DB level instead of fetching all records
     *
     * @var array
     */
    protected array $withCount = [];

    /**
     * The list of default scopes to apply
     *
     * @var array
     */
    protected array $scopes = [];

    protected array $queryFilters = [];

    /**
     * List of available filters (DB columns to filter)
     *
     * @var array
     */
    protected array $filters = [];

    /**
     * List of searchable filters (DB columns to filter)
     *
     * @var array
     */
    protected array $searchableFilters = [];

    /**
     * List of available sorters (DB columns to sort on)
     *
     * @var array
     */
    protected array $sorters = [];

    /**
     * The column to sort on by default
     *
     * @var string
     */
    protected string $defaultSorter = 'id';

    /**
     * The default direction to sort on. i.e., DESC or ASC
     *
     * @var string
     */
    protected string $defaultSorterDirection = 'asc';

    /**
     * The search query to filter records
     *
     * @var string|null
     */
    protected ?string $searchQuery = null;

    /**
     * Allow ability to perform full-text search
     *
     * @var bool
     */
    protected bool $supportsFullTextSearch = false;

    /**
     * List of default column for dropdown. This fetches the complete table so be careful!
     *
     * @var array
     */
    protected array $defaultDropdownFields = ['id'];

    /**
     * BaseRepository constructor.
     * Initializes the repository and builds options from the request.
     */
    public function __construct()
    {
        $this->buildOptionsFromRequest();
    }

    /**
     * Set the model to interact with.
     *
     * @param Model|Authenticatable $model
     * @return $this
     */
    public function setModel(Model|Authenticatable $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Get the model being used in the repository.
     *
     * @return Model|Authenticatable
     */
    public function getModel(): Model|Authenticatable
    {
        return $this->model;
    }

    /**
     * Build repository options from the request.
     * This includes pagination, search, sorting, etc.
     */
    public function buildOptionsFromRequest()
    {
        if (app()->runningInConsole()) return;

        $options = request()->get('options') ?? [];

        if ($options['page'] ?? false) {
            request()->merge(['page' => $options['page']]);
        }

        if ($options['itemsPerPage'] ?? false && (int) $options['itemsPerPage'] >= 0 && (int) $options['itemsPerPage'] <= 100) {
            $this->perPage = $options['itemsPerPage'];
        }

        if (!empty(request()->get('q')) ?? false) {
            $this->searchQuery = request()->get('q');
        }

        if (!empty(request()->get('options')) && !empty(request()->get('options')['sortBy'])) {
            $sortByOptions = request()->get('options')['sortBy'];
            $sortString = '';
            foreach ($sortByOptions as $key => $sortOptions) {
                if (!array_key_exists('key', $sortOptions)) continue;
                if (!array_key_exists('order', $sortOptions)) continue;
                if (strtolower($sortOptions['order']) === 'desc') {
                    $sortString .= '-' . $sortOptions['key'];
                } else {
                    $sortString .= $sortOptions['key'];
                }
            }
            if (!empty($sortString)) {
                request()->merge(['sort' => $sortString]);
            }
        }
    }

    /**
     * Get the query builder instance for the model.
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = QueryBuilder::for($this->model);

        // Using the getter methods for `with`, `withCount`, `filters`, `sorters`, etc.
        if (!empty($this->getFilters())) {
            $queryBuilder = $queryBuilder->allowedFilters($this->getFilters());
        }

        if (!empty($this->getSorters())) {
            $queryBuilder = $queryBuilder->allowedSorts($this->getSorters());
        }

        if (!empty($this->defaultSorterDirection) && !empty($this->defaultSorter)) {
            $this->defaultSorterDirection = strtolower($this->defaultSorterDirection);
            if (!in_array($this->defaultSorterDirection, ['asc', 'desc'])) {
                throw new RuntimeException("The $this->defaultSorterDirection must be either ASC or DESC");
            }
            if ($this->defaultSorterDirection === 'desc') {
                $this->defaultSorter = '-' . $this->defaultSorter;
            }
            $queryBuilder = $queryBuilder->defaultSort($this->defaultSorter);
        }

        if (!empty($this->searchQuery) && $this->supportsFullTextSearch) {
            $queryBuilder = $queryBuilder->whereFullText($this->getSearchableFilters(), $this->searchQuery . '*', [
                'mode' => 'boolean'
            ]);
        }

        if (!empty($this->searchQuery) && !$this->supportsFullTextSearch) {
            $queryBuilder = $queryBuilder->where(function ($query) {
                foreach ($this->getSearchableFilters() as $key => $filter) {
                    if ($key === 0) {
                        $query->where($filter, 'LIKE', '%' . $this->searchQuery . '%');
                    } else {
                        $query->orWhere($filter, 'LIKE', '%' . $this->searchQuery . '%');
                    }
                }
            });
        }

        if (!empty($this->getWith())) {
            $queryBuilder = $queryBuilder->with($this->getWith());
        }

        if (!empty($this->getWithCount())) {
            $queryBuilder = $queryBuilder->withCount($this->getWithCount());
        }

        if (!empty($this->getScopes())) {
            $queryBuilder = $queryBuilder->scopes($this->getScopes());
        }

        foreach($this->getQueryFilters() as $queryFilter)
        {
            $queryBuilder = $queryFilter($queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * Get the paginated results.
     *
     * @return mixed
     */
    public function index()
    {
        $queryBuilder = $this->getQueryBuilder();

        if ($this->paginator === 'simple') return $queryBuilder->paginate($this->perPage);

        return $queryBuilder;
    }

    /**
     * Get the options for a dropdown list.
     *
     * @return mixed
     */
    public function dropdown()
    {
        $this->with = [];
        $this->withCount = [];
        return $this->getQueryBuilder()->select($this->defaultDropdownFields)->get();
    }

    /**
     * Find a single record by its ID.
     *
     * @param int $id
     * @return Model
     */
    public function find($id)
    {
        return $this->model->with($this->with)->withCount($this->withCount)->find($id);
    }

    /**
     * Get a single record by its ID.
     *
     * @param int $id
     * @return Model
     */
    public function get($id)
    {
        return $this->model->with($this->with)->withCount($this->withCount)->get($id);
    }

    /**
     * Get a collection of primary keys from the model.
     *
     * @return mixed
     */
    public function pluckIds()
    {
        return $this->model->pluck($this->primaryKey);
    }

    /**
     * Store a new record in the database.
     *
     * @param array $array
     * @return Model
     */
    public function store($array)
    {
        $this->model->fill($array)->save();
        $this->model->refresh();
        return $this->model;
    }

    /**
     * Update an existing record in the database.
     *
     * @param int $id
     * @param array $array
     * @return Model
     */
    public function update($id, $array)
    {
        $this->model = $this->model->find($id);
        $this->model->fill($array)->save();
        $this->model->refresh();
        return $this->model;
    }

    /**
     * Delete a record by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function destroy($id) 
    {
        return $this->model->where($this->primaryKey, $id)->delete();
    }

    /**
     * Delete multiple records by their IDs.
     *
     * @param array $array
     * @return bool
     */
    public function destroyMulti($array)
    {
        return $this->model->whereIn($this->primaryKey, $array)->delete();
    }

    /**
     * Get the list of eager load relationships.
     *
     * @return array
     */
    protected function getWith(): array
    {
        return $this->with;
    }

    /**
     * Get the list of count relationships.
     *
     * @return array
     */
    protected function getWithCount(): array
    {
        return $this->withCount;
    }

    /**
     * Get the list of sorters.
     *
     * @return array
     */
    protected function getSorters(): array
    {
        return $this->sorters;
    }

    /**
     * Get the list of scopes.
     *
     * @return array
     */
    protected function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * Get the list of filters.
     *
     * @return array
     */
    protected function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get the list of searchable filters.
     *
     * @return array
     */
    protected function getSearchableFilters(): array
    {
        return $this->searchableFilters;
    }

    protected function getQueryFilters()
    {
        return $this->queryFilters;
    }

    public function addQueryFilter(callable $filterFunction)
    {
        $this->queryFilters[] = $filterFunction;
        return $this;
    }

    /**
     * Add a custom scope to the repository.
     *
     * @param string $scope
     * @return $this
     */
    public function addScopes($scope)
    {
        $this->scopes[] = $scope;
        return $this;
    }
}
