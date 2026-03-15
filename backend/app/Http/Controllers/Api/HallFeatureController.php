<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHallFeatureRequest;
use App\Http\Requests\UpdateHallFeatureRequest;
use App\Http\Resources\HallFeatureApiResource;
use App\Models\HallFeature;
use App\Services\HallFeatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HallFeatureController extends Controller
{
    public function __construct(
        private readonly HallFeatureService $service,
    ) {}

    public function index(Request $request)
    {
        $hallFeatures = $this->service->paginate(
            $request->only(['search', 'feature_type', 'unlock_level', 'jump_page', 'sort_by', 'sort_direction', 'per_page']),
        );

        return HallFeatureApiResource::collection($hallFeatures);
    }

    public function store(StoreHallFeatureRequest $request): JsonResponse
    {
        $hallFeature = $this->service->create($request->validated());

        return HallFeatureApiResource::make($hallFeature)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(HallFeature $hallFeature): HallFeatureApiResource
    {
        return HallFeatureApiResource::make($hallFeature);
    }

    public function update(UpdateHallFeatureRequest $request, HallFeature $hallFeature): HallFeatureApiResource
    {
        return HallFeatureApiResource::make(
            $this->service->update($hallFeature, $request->validated()),
        );
    }

    public function destroy(HallFeature $hallFeature): Response
    {
        $this->service->delete($hallFeature);

        return response()->noContent();
    }
}
