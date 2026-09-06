<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /**
     * Get a list of all deals for the API.
     */
    public function index(Request $request)
    {
        $query = Deal::with(['stage', 'owner', 'lead']);

        // Filter by stage name if provided
        if ($request->filled('stage')) {
            $stageName = $request->stage;
            if (strtolower($stageName) !== 'all deals') {
                $query->whereHas('stage', function ($q) use ($stageName) {
                    $q->where('name', $stageName);
                });
            }
        }

        $deals = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $deals
        ]);
    }

    /**
     * Create a new deal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'deal_stage_id' => 'nullable',
            'lead_id' => 'nullable',
            'owner_id' => 'nullable',
            'close_date' => 'nullable|date',
        ]);

        // If deal_stage_id is not set or is a string name, resolve it
        if (empty($validated['deal_stage_id'])) {
            $defaultStage = \App\Models\DealStage::where('name', 'New')->first() 
                ?? \App\Models\DealStage::first();
            $validated['deal_stage_id'] = $defaultStage ? $defaultStage->id : null;
        } elseif (!is_numeric($validated['deal_stage_id'])) {
            $stage = \App\Models\DealStage::where('name', $validated['deal_stage_id'])->first();
            $validated['deal_stage_id'] = $stage ? $stage->id : null;
        }

        if (empty($validated['owner_id']) && $request->user()) {
            $validated['owner_id'] = $request->user()->id;
        }

        $deal = Deal::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Deal created successfully',
            'data' => $deal->load(['stage', 'owner', 'lead'])
        ], 201);
    }

    /**
     * Update an existing deal.
     */
    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|numeric|min:0',
            'deal_stage_id' => 'nullable',
            'lead_id' => 'nullable',
            'owner_id' => 'nullable',
            'close_date' => 'nullable|date',
        ]);

        if (isset($validated['deal_stage_id']) && !is_numeric($validated['deal_stage_id'])) {
            $stage = \App\Models\DealStage::where('name', $validated['deal_stage_id'])->first();
            $validated['deal_stage_id'] = $stage ? $stage->id : null;
        }

        $deal->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Deal updated successfully',
            'data' => $deal->load(['stage', 'owner', 'lead'])
        ]);
    }

    /**
     * Delete a deal.
     */
    public function destroy(Deal $deal)
    {
        $deal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deal deleted successfully'
        ]);
    }
}
