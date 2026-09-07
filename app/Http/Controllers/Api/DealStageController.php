<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DealStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealStageController extends Controller
{
    /**
     * Display a listing of deal pipeline stages.
     */
    public function index(): JsonResponse
    {
        $stages = DealStage::withCount('deals')->orderBy('order_index')->get();

        return response()->json([
            'success' => true,
            'data' => $stages,
        ]);
    }

    /**
     * Store a newly created deal stage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-pipelines') && !$user->hasRole(['Admin', 'Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:deal_stages,name',
            'color' => 'nullable|string|max:50',
            'order_index' => 'nullable|integer',
            'display_order' => 'nullable|integer',
            'probability' => 'nullable|integer|min:0|max:100',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
        ]);

        if (isset($validated['display_order']) && !isset($validated['order_index'])) {
            $validated['order_index'] = $validated['display_order'];
        }

        if (!isset($validated['order_index'])) {
            $validated['order_index'] = (DealStage::max('order_index') ?? 0) + 1;
        }

        $stage = DealStage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pipeline stage created successfully',
            'data' => $stage,
        ], 201);
    }

    /**
     * Update the specified deal stage.
     */
    public function update(Request $request, DealStage $dealStage): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-pipelines') && !$user->hasRole(['Admin', 'Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:deal_stages,name,' . $dealStage->id,
            'color' => 'nullable|string|max:50',
            'order_index' => 'nullable|integer',
            'display_order' => 'nullable|integer',
            'probability' => 'nullable|integer|min:0|max:100',
            'is_won' => 'nullable|boolean',
            'is_lost' => 'nullable|boolean',
        ]);

        if (isset($validated['display_order']) && !isset($validated['order_index'])) {
            $validated['order_index'] = $validated['display_order'];
        }

        $dealStage->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pipeline stage updated successfully',
            'data' => $dealStage,
        ]);
    }

    /**
     * Reorder pipeline stages.
     */
    public function reorder(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-pipelines') && !$user->hasRole(['Admin', 'Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $items = $request->input('stages') ?? $request->input('orders') ?? [];

        foreach ($items as $item) {
            $order = $item['order_index'] ?? $item['display_order'] ?? null;
            if ($order !== null && isset($item['id'])) {
                DealStage::where('id', $item['id'])->update(['order_index' => $order]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pipeline stages reordered successfully',
            'data' => DealStage::orderBy('order_index')->get(),
        ]);
    }

    /**
     * Remove the specified deal stage.
     */
    public function destroy(DealStage $dealStage): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-pipelines') && !$user->hasRole(['Admin', 'Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if ($dealStage->deals()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete stage containing active deals. Please reassign or close deals first.',
            ], 422);
        }

        $dealStage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pipeline stage deleted successfully',
        ]);
    }
}
