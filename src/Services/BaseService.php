<?php

namespace App\Services;

use App\Repositories\BaseRepositoryContract;
use App\Transformers\BaseCollectionTransformerContract;
use App\Transformers\BaseTransformerContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Class BaseService
 *
 * @package App\Services
 */
class BaseService implements BaseServiceContract
{
    /**
     * The Repository to interact with.
     *
     * @var BaseRepositoryContract
     */
    protected BaseRepositoryContract $repository;

    /**
     * The transformer to transform API responses for a single entity.
     *
     * @var BaseTransformerContract
     */
    protected BaseTransformerContract $transformer;

    /**
     * The transformer to transform API responses for collections of a single entity.
     *
     * @var BaseCollectionTransformerContract
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
     *
     * @param Request $request
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
     * @param BaseRepositoryContract $repository
     * @return $this
     */
    public function setRepository(BaseRepositoryContract $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Return a collection of resources.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $transformer = new $this->collectionTransformer();
        $transformer = $transformer->setResource($this->repository->index());
        return $this->successfullApiResponse($transformer->toArray());
    }

    /**
     * Return dropdown data.
     *
     * @return JsonResponse
     */
    public function dropdown(): JsonResponse
    {
        return $this->successfullApiResponse($this->repository->dropdown());
    }

    /**
     * Return dropdown data for statuses.
     *
     * @return JsonResponse
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
     * @param array $validatedRequestData
     * @return JsonResponse
     */
    public function store($validatedRequestData): JsonResponse
    {
        $this->repository->store($validatedRequestData);
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($this->repository->getModel());
        return $this->successfullApiResponse($transformer->toArray(), 201);
    }

    /**
     * Retrieve a specific resource by ID.
     *
     * @param int|string $modelId
     * @return JsonResponse
     */
    public function get($modelId): JsonResponse
    {
        $model = $this->repository->find($modelId);
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($model);
        return $this->successfullApiResponse($transformer->toArray(), 200);
    }

    /**
     * Update a specific resource.
     *
     * @param int|string $modelId
     * @param array $validatedRequestData
     * @return JsonResponse
     */
    public function update($modelId, $validatedRequestData): JsonResponse
    {
        $this->repository->update($modelId, $validatedRequestData);
        $transformer = new $this->transformer();
        $transformer = $transformer->setResource($this->repository->getModel());
        return $this->successfullApiResponse($transformer->toArray(), 200);
    }

    /**
     * Delete a resource by ID.
     *
     * @param int|string $modelId
     * @return JsonResponse
     */
    public function delete($modelId): JsonResponse
    {
        return $this->destroy($modelId);
    }

    /**
     * Destroy a resource by ID.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $this->repository->destroy($id);
        return $this->apiResponse([], 204);
    }

    /**
     * Destroy multiple resources by their IDs.
     *
     * @param array $array
     * @return JsonResponse
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
     * @param callable $filterFunction
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
     * @param mixed $binaryData
     * @param string $fileName
     * @param string $folder
     * @return string
     */
    protected function uploadFile($binaryData, $fileName, $folder = 'uploads'): string
    {
        $uniqueFileName = uniqid();
        $filePath = $folder . DIRECTORY_SEPARATOR . $uniqueFileName;
        $path = Storage::disk(config('filesystems.default'))->putFileAs($filePath, $binaryData, $fileName);
        return $path;
    }

    /**
     * Return a JSON API response.
     *
     * @param mixed $data
     * @param int $statusCode
     * @return JsonResponse
     */
    public function apiResponse($data, $statusCode): JsonResponse
    {
        if ($statusCode === 204) return response()->json(null, 204);
        return response()->json($data, $statusCode);
    }

    /**
     * Return a successful API response (HTTP 200).
     *
     * @param array|Collection|EloquentCollection $data
     * @return JsonResponse
     */
    public function successfullApiResponse(array|Collection|EloquentCollection $data): JsonResponse
    {
        return $this->apiResponse($data, 200);
    }

    /**
     * Return an API response with validation errors (HTTP 422).
     *
     * @param mixed $data
     * @return JsonResponse
     */
    public function apiResponseWithValidationErrors($data)
    {
        return $this->apiResponse($data, 422);
    }

    /**
     * Return an API response with server errors (HTTP 500).
     *
     * @param mixed $data
     * @return JsonResponse
     */
    public function apiResponseWithServerErrors($data)
    {
        return $this->apiResponse($data, 500);
    }

    /**
     * Return an API response when authentication fails (HTTP 401).
     *
     * @param mixed $data
     * @return JsonResponse
     */
    public function apiResponseWithAuthenticationFailedError($data)
    {
        return $this->apiResponse($data, 401);
    }

    /**
     * Return an API response when authorization fails (HTTP 403).
     *
     * @param mixed $data
     * @return JsonResponse
     */
    public function apiResponseWithAuthorizationFailedError($data)
    {
        return $this->apiResponse($data, 403);
    }
}
