<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Models\Courier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CourierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Courier::query();

        if ($request->filled('search')) {
            $terms = preg_split(
                '/\s+/',
                trim($request->string('search')->toString()),
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            foreach ($terms as $term) {
                $query->where('name', 'like', '%' . $term . '%');
            }
        }

        if ($request->filled('level')) {
            $levels = explode(',', $request->string('level')->toString());

            foreach ($levels as $level) {
                $level = trim($level);

                if (
                    $level === ''
                    || !ctype_digit($level)
                    || (int) $level < 1
                    || (int) $level > 5
                ) {
                    throw ValidationException::withMessages([
                        'level' => 'Level must contain integers between 1 and 5.',
                    ]);
                }
            }

            $query->whereIn('level', array_map('intval', $levels));
        }

        $sort = $request->query('sort', 'name');

        if (!in_array($sort, ['name', 'registered_at'], true)) {
            throw ValidationException::withMessages([
                'sort' => 'Sort must be name or registered_at.',
            ]);
        }

        if ($sort === 'registered_at') {
            $query->orderByDesc('created_at')->orderByDesc('id');
        } else {
            $query->orderBy('name')->orderBy('id');
        }

        $perPage = min(max((int) $request->query('per_page', 10), 1), 100);

        return response()->json(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function store(StoreCourierRequest $request): JsonResponse
    {
        $courier = Courier::create($request->validated());

        return response()->json([
            'message' => 'Courier created successfully.',
            'data' => $courier,
        ], 201);
    }

    public function show(Courier $courier): JsonResponse
    {
        return response()->json($courier);
    }

    public function update(
        UpdateCourierRequest $request,
        Courier $courier
    ): JsonResponse {
        $courier->update($request->validated());

        return response()->json([
            'message' => 'Courier updated successfully.',
            'data' => $courier->fresh(),
        ]);
    }

    public function destroy(Courier $courier): JsonResponse
    {
        $courier->delete();

        return response()->json([
            'message' => 'Courier deleted successfully.',
        ]);
    }
}
