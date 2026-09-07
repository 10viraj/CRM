<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Activity::with(['user', 'subject']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtering
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($subjectType = $request->input('subject_type')) {
            $query->where('subject_type', $subjectType);
        }

        if ($subjectId = $request->input('subject_id')) {
            $query->where('subject_id', $subjectId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('activity_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('activity_date', '<=', $dateTo);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'activity_date');
        $sortDirection = strtolower($request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'title', 'type', 'status', 'activity_date', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest('activity_date');
        }

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        if ($request->boolean('all')) {
            $activities = $query->get();
            return response()->json([
                'success' => true,
                'data' => ActivityResource::collection($activities),
            ]);
        }

        $activities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ActivityResource::collection($activities),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
        ]);
    }

    /**
     * Store a newly created activity.
     */
    public function store(ActivityRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }
        if (empty($data['activity_date'])) {
            $data['activity_date'] = now();
        }

        $activity = Activity::create($data);
        $activity->load(['user', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Activity logged successfully',
            'data' => new ActivityResource($activity),
        ], 201);
    }

    /**
     * Display the specified activity.
     */
    public function show(Activity $activity): JsonResponse
    {
        $activity->load(['user', 'subject']);

        return response()->json([
            'success' => true,
            'data' => new ActivityResource($activity),
        ]);
    }

    /**
     * Update the specified activity.
     */
    public function update(ActivityRequest $request, Activity $activity): JsonResponse
    {
        $activity->update($request->validated());
        $activity->load(['user', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Activity updated successfully',
            'data' => new ActivityResource($activity),
        ]);
    }

    /**
     * Remove the specified activity.
     */
    public function destroy(Activity $activity): JsonResponse
    {
        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity deleted successfully',
        ]);
    }
}
