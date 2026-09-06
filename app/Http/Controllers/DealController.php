<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealStage;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index()
    {
        // Load all deal stages in order
        $stages = DealStage::orderBy('order_index')->get();
        
        // Load deals grouped by stage for the Kanban board
        $deals = Deal::with(['lead', 'owner'])->get()->groupBy('deal_stage_id');

        return view('deals.index', compact('stages', 'deals'));
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'deal_stage_id' => 'required|exists:deal_stages,id'
        ]);

        $deal->update([
            'deal_stage_id' => $validated['deal_stage_id']
        ]);

        return response()->json(['success' => true, 'message' => 'Stage updated successfully.']);
    }

    public function show(Deal $deal)
    {
        $deal->load(['lead', 'owner', 'stage']);
        $stages = DealStage::orderBy('order_index')->get();
        return view('deals.show', compact('deal', 'stages'));
    }
}
