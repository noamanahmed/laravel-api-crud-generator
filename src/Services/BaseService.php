<?php

namespace NoamanAhmed\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use NoamanAhmed\Repositories\BaseRepositoryContract;
use NoamanAhmed\Transformers\BaseCollectionTransformerContract;
use NoamanAhmed\Transformers\BaseTransformerContract;

/**
 * Class BaseService
 */
class BaseService implements BaseServiceContract
{
    /**
     * The Repository to interact with.
     */
    protected BaseRepositoryContract $repository;

    /**
     * The transformer to transform API responses for a single entity.
     */
    protected BaseTransformerContract $transformer;

    /**
     * The transformer to transform API responses for collections of a single entity.
     */
    protected BaseCollectionTransformerContract $collectionTransformer;

    /**
     * Status mapper enum for mapping statuses to pretty formats.
     *
     * @var mixed
     */
    protected $statusMapperEnum;

    /**
     * Create a new BaseService instance.
     */
    public function __construct(
        protected Request $request
    ) {}

    /**
     * Get the repository instance.
     *
     * @return BaseRepositoryContract
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set the repository instance.
     *
     * @return $this
     */
    public function setRepository(BaseRepositoryContract $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Return a collection of resources.
     */
    public function index(): JsonResponse
    {
        $transformer = new $this->collectionTransformer;
        $transformer = $transformer->setResource($this->repository->index());

        return $this->successfullApiResponse($transformer->toArray());
    }

    /**
     * Return dropdown data.
     */
    public function dropdown(): JsonResponse
    {
        return $this->successfullApiResponse($this->repository->dropdown());
    }

    /**
     * Return dropdown data for statuses.
     */
    public function dropdownForStatus(): JsonResponse
    {
        return $this->successfullApiResponse(
            $this->statusMapperEnum::dropdown()
        );
    }

    /**
     * Store a new resource.
     *
     * @param  array  $validatedRequestData
     */
    public function store($validatedRequestData): JsonResponse
    {
        $this->repository->store($validatedRequestData);
        $transformer = new $this->transformer;
        $transformer = $transformer->setResource($this->repository->getModel());

        return $this->apiResponse($transformer->toArray(), 201);
    }

    /**
     * Retrieve a specific resource by ID.
     *
     * @param  int|string  $modelId
     */
    public function get($modelId): JsonResponse
    {
        $model = $this->repository->find($modelId);
        $transformer = new $this->transformer;
        $transformer = $transformer->setResource($model);

        return $this->successfullApiResponse($transformer->toArray());
    }

    /**
     * Retrieve a specific resource by ID.
     *
     * @param  int|string  $modelId
     */
    public function find($modelId): JsonResponse
    {
        $model = $this->repository->find($modelId);
        $transformer = new $this->transformer;
        $transformer = $transformer->setResource($model);

        return $this->successfullApiResponse($transformer->toArray());
    }

    /**
     * Update a specific resource.
     *
     * @param  int|string  $modelId
     * @param  array  $validatedRequestData
     */
    public function update($modelId, $validatedRequestData): JsonResponse
    {
        $this->repository->update($modelId, $validatedRequestData);
        $transformer = new $this->transformer;
        $transformer = $transformer->setResource($this->repository->getModel());

        return $this->successfullApiResponse($transformer->toArray());
    }

    /**
     * Delete a resource by ID.
     *
     * @param  int|string  $modelId
     */
    public function delete($modelId): JsonResponse
    {
        return $this->destroy($modelId);
    }

    /**
     * Destroy a resource by ID.
     *
     * @param  int|string  $id
     */
    public function destroy($id): JsonResponse
    {
        $this->repository->destroy($id);

        return $this->apiResponse([], 204);
    }

    /**
     * Destroy multiple resources by their IDs.
     *
     * @param  array  $array
     */
    public function destroyMulti($array): JsonResponse
    {
        $this->repository->destroyMulti($array);

        return $this->apiResponse([], 204);
    }

    /**
     * Get query filters for the repository.
     *
     * @return array
     */
    protected function getQueryFilters()
    {
        return $this->repository->queryFilters;
    }

    /**
     * Add a custom query filter.
     *
     * @return $this
     */
    protected function addQueryFilter(callable $filterFunction)
    {
        $this->repository->addQueryFilter($filterFunction);

        return $this;
    }

    /**
     * Upload a file to the storage disk.
     *
     * @param  mixed  $binaryData
     * @param  string  $fileName
     * @param  string  $folder
     */
    protected function uploadFile($binaryData, $fileName, $folder = 'uploads'): string
    {
        $uniqueFileName = uniqid();
        $filePath = $folder.DIRECTORY_SEPARATOR.$uniqueFileName;
        $path = Storage::disk(config('filesystems.default'))->putFileAs($filePath, $binaryData, $fileName);

        return $path;
    }

    /**
     * Return a JSON API response.
     *
     * @param  mixed  $data
     * @param  int  $statusCode
     */
    public function apiResponse($data, $statusCode): JsonResponse
    {
        if ($statusCode === 204) {
            return response()->json(null, 204);
        }

        return response()->json($data, $statusCode);
    }

    /**
     * Return a successful API response (HTTP 200).
     */
    public function successfullApiResponse(array|Collection|EloquentCollection $data): JsonResponse
    {
        return $this->apiResponse($data, 200);
    }

    /**
     * Return an API response with validation errors (HTTP 422).
     *
     * @param  mixed  $data
     * @return JsonResponse
     */
    public function apiResponseWithValidationErrors($data)
    {
        return $this->apiResponse($data, 422);
    }

    /**
     * Return an API response with server errors (HTTP 500).
     *
     * @param  mixed  $data
     * @return JsonResponse
     */
    public function apiResponseWithServerErrors($data)
    {
        return $this->apiResponse($data, 500);
    }

    /**
     * Return an API response when authentication fails (HTTP 401).
     *
     * @param  mixed  $data
     * @return JsonResponse
     */
    public function apiResponseWithAuthenticationFailedError($data)
    {
        return $this->apiResponse($data, 401);
    }

    /**
     * Return an API response when authorization fails (HTTP 403).
     *
     * @param  mixed  $data
     * @return JsonResponse
     */
    public function apiResponseWithAuthorizationFailedError($data)
    {
        return $this->apiResponse($data, 403);
    }
}
