<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = Task::with(['relatedTo', 'assignee']);

        if ($status && $status !== 'All') {
            $query->where('status', $status);
        }

        // Sort by id desc for latest first
        $tasks = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'related_to_type' => 'nullable|string',
            'related_to_id' => 'nullable|integer',
            'type' => 'nullable|string',
            'priority' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assign_to_id' => 'nullable|exists:users,id',
            'status' => 'nullable|string',
        ]);

        $task = Task::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task->load(['relatedTo', 'assignee'])
        ], 201);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'related_to_type' => 'nullable|string',
            'related_to_id' => 'nullable|integer',
            'type' => 'nullable|string',
            'priority' => 'nullable|string',
            'due_date' => 'nullable|date',
            'assign_to_id' => 'nullable|exists:users,id',
            'status' => 'nullable|string',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task->load(['relatedTo', 'assignee'])
        ]);
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }
}
