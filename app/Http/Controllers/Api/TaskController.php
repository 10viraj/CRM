<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks with search, filtering, sorting, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::with(['assignee', 'creator', 'relatedTo']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtering
        if ($status = $request->input('status')) {
            if (strtolower($status) === 'overdue') {
                $query->whereDate('due_date', '<', now()->toDateString())
                      ->where('status', '!=', 'Completed');
            } elseif (strtolower($status) !== 'all') {
                $query->where('status', 'like', $status);
            }
        }

        if ($priority = $request->input('priority')) {
            if (strtolower($priority) !== 'all') {
                $query->where('priority', 'like', $priority);
            }
        }

        if ($type = $request->input('type')) {
            $query->where('type', 'like', $type);
        }

        if ($assigneeId = $request->input('assign_to_id') ?: $request->input('assignee_id')) {
            $query->where('assign_to_id', $assigneeId);
        }

        if ($creatorId = $request->input('creator_id')) {
            $query->where('creator_id', $creatorId);
        }

        if ($relatedType = $request->input('related_to_type')) {
            $morphed = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($relatedType) ?? $relatedType;
            $query->where(function ($q) use ($relatedType, $morphed) {
                $q->where('related_to_type', $relatedType)
                  ->orWhere('related_to_type', $morphed)
                  ->orWhere('related_to_type', 'like', "%{$relatedType}%");
            });
        }

        if ($relatedId = $request->input('related_to_id')) {
            $query->where('related_to_id', $relatedId);
        }

        if ($dueFrom = $request->input('due_date_from')) {
            $query->whereDate('due_date', '>=', $dueFrom);
        }

        if ($dueTo = $request->input('due_date_to')) {
            $query->whereDate('due_date', '<=', $dueTo);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'due_date');
        $sortDirection = strtolower($request->input('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['id', 'title', 'priority', 'status', 'due_date', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('due_date', 'asc');
        }

        // Pagination
        $perPage = (int) $request->input('per_page', 50);
        $perPage = min(max($perPage, 1), 100);

        if ($request->boolean('all')) {
            $tasks = $query->get();
            return response()->json([
                'success' => true,
                'data' => TaskResource::collection($tasks),
            ]);
        }

        $tasks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TaskResource::collection($tasks),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
            ],
        ]);
    }

    /**
     * Store a newly created task.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['creator_id']) && Auth::check()) {
            $data['creator_id'] = Auth::id();
        }
        if (empty($data['assign_to_id']) && Auth::check()) {
            $data['assign_to_id'] = Auth::id();
        }

        $task = Task::create($data);
        $task->load(['assignee', 'creator', 'relatedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        $task->load(['assignee', 'creator', 'relatedTo', 'activities']);

        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Update the specified task.
     */
    public function update(TaskRequest $request, Task $task): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['status']) && $data['status'] === 'Completed' && empty($data['completed_at'])) {
            $data['completed_at'] = now();
        }

        $task->update($data);
        $task->load(['assignee', 'creator', 'relatedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Update task status (quick toggle/status update).
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:Pending,In Progress,Completed,Cancelled',
        ]);

        $status = $request->input('status');
        $completedAt = $status === 'Completed' ? now() : null;

        $task->update([
            'status' => $status,
            'completed_at' => $completedAt,
        ]);

        $task->load(['assignee', 'creator', 'relatedTo']);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully',
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Bulk delete tasks.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No tasks provided for deletion.',
            ], 422);
        }

        $count = Task::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} tasks.",
        ]);
    }

    /**
     * Bulk complete tasks.
     */
    public function bulkComplete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No tasks provided.',
            ], 422);
        }

        $count = Task::whereIn('id', $ids)->update([
            'status' => 'Completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully marked {$count} tasks as completed.",
        ]);
    }

    /**
     * Get tasks metadata & aggregate statistics.
     */
    public function metadata(): JsonResponse
    {
        $total = Task::count();
        $pending = Task::where('status', 'Pending')->count();
        $inProgress = Task::where('status', 'In Progress')->count();
        $completed = Task::where('status', 'Completed')->count();
        $overdue = Task::where('status', '!=', 'Completed')
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $leads = \App\Models\Lead::latest()->take(50)->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'name' => trim("{$l->first_name} {$l->last_name}") ?: ($l->company_name ?: "Lead #{$l->id}"),
            ];
        });
        $contacts = \App\Models\Contact::latest()->take(50)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => trim("{$c->first_name} {$c->last_name}") ?: "Contact #{$c->id}",
            ];
        });
        $deals = \App\Models\Deal::latest()->take(50)->get()->map(function ($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
            ];
        });

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'pending' => $pending,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'overdue' => $overdue,
            ],
            'options' => [
                'users' => $users,
                'leads' => $leads,
                'contacts' => $contacts,
                'deals' => $deals,
            ],
        ]);
    }
}
