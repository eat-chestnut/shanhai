<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCharacterClassRequest;
use App\Http\Requests\UpdateCharacterClassRequest;
use App\Http\Resources\CharacterClassApiResource;
use App\Models\CharacterClass;
use App\Services\CharacterClassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CharacterClassController extends Controller
{
    public function __construct(
        private readonly CharacterClassService $service,
    ) {}

    public function index(Request $request)
    {
        $characterClasses = $this->service->paginate(
            $request->only(['search', 'role_type', 'is_open', 'sort_by', 'sort_direction', 'per_page']),
        );

        return CharacterClassApiResource::collection($characterClasses);
    }

    public function store(StoreCharacterClassRequest $request): JsonResponse
    {
        $characterClass = $this->service->create($request->validated());

        return CharacterClassApiResource::make($characterClass)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(CharacterClass $characterClass): CharacterClassApiResource
    {
        return CharacterClassApiResource::make($characterClass);
    }

    public function update(UpdateCharacterClassRequest $request, CharacterClass $characterClass): CharacterClassApiResource
    {
        return CharacterClassApiResource::make(
            $this->service->update($characterClass, $request->validated()),
        );
    }

    public function destroy(CharacterClass $characterClass): Response
    {
        $this->service->delete($characterClass);

        return response()->noContent();
    }
}
