<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSkillRequest;
use App\Http\Requests\UpdateSkillRequest;
use App\Http\Resources\SkillApiResource;
use App\Models\Skill;
use App\Services\SkillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SkillController extends Controller
{
    public function __construct(
        private readonly SkillService $service,
    ) {}

    public function index(Request $request)
    {
        $skills = $this->service->paginate(
            $request->only(['search', 'class_id', 'type', 'is_open', 'sort_by', 'sort_direction', 'per_page']),
        );

        return SkillApiResource::collection($skills);
    }

    public function store(StoreSkillRequest $request): JsonResponse
    {
        $skill = $this->service->create($request->validated());

        return SkillApiResource::make($skill)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Skill $skill): SkillApiResource
    {
        return SkillApiResource::make($skill);
    }

    public function update(UpdateSkillRequest $request, Skill $skill): SkillApiResource
    {
        return SkillApiResource::make(
            $this->service->update($skill, $request->validated()),
        );
    }

    public function destroy(Skill $skill): Response
    {
        $this->service->delete($skill);

        return response()->noContent();
    }
}
