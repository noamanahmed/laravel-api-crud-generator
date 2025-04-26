<?php

namespace NoamanAhmed\ApiCrudGenerator\Controllers;

use Illuminate\Routing\Controller as Controller;


class BaseController extends Controller
{
    public function __construct(
        private WorkflowService $workflowService
    ){}

    /**
     * Create Workflow.
    */
    public function index()
    {
        return $this->workflowService->index();
    }

    /**
     * Get Workflow.
     */
    public function dropdown()
    {
        return $this->workflowService->dropdown();
    }


    /**
     * Workflow constants.
     */
    public function constants()
    {
        return $this->workflowService->constants();
    }

    /**
     * Store Workflow.
     */
    public function store(StoreRequest $request)
    {
        return $this->workflowService->store($request->validated());
    }

    /**
     * Get Workflow
     */
    public function show(Workflow $workflow)
    {
        return $this->workflowService->get($workflow->id);
    }


    /**
     * Update Workflow
     */
    public function update(UpdateRequest $request, Workflow $workflow)
    {
        return $this->workflowService->update($workflow->id,$request->validated());
    }

    /**
     * Delete Workflow
     */
    public function destroy(Workflow $workflow)
    {
        return $this->workflowService->delete($workflow->id);
    }

    /**
     * Multi delete Workflow
     */
    public function multiDestroy(Workflow $workflow)
    {
        return $this->workflowService->multiDelete($workflow->id);
    }


    /**
     * Exports Workflow.
     */
    public function export(ImportRequest $request)
    {
        return $this->workflowService->export($request->validated());
    }

    /**
     * Import Workflow.
    */
    public function import(ExportRequest $request)
    {
        return $this->workflowService->import($request->validated());
    }

    /**
     * Workflow Analytics.
    */
    public function analytics(Request $request)
    {
        return $this->workflowService->analytics($request);
    }

}
