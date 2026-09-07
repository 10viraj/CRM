<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DealRequest;
use App\Http\Resources\DealResource;
use App\Models\Deal;
use App\Models\DealStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    /**
     * Display a listing of deals with search, filtering, sorting, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Deal::with(['stage', 'lead', 'company', 'contact', 'owner', 'customFieldValues.field']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filtering
        if ($stageId = $request->input('deal_stage_id') ?: $request->input('stage_id')) {
            $query->where('deal_stage_id', $stageId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($ownerId = $request->input('owner_id')) {
            $query->where('owner_id', $ownerId);
        }

        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($contactId = $request->input('contact_id')) {
            $query->where('contact_id', $contactId);
        }

        if ($leadId = $request->input('lead_id')) {
            $query->where('lead_id', $leadId);
        }

        if ($request->has('min_value')) {
            $query->where('value', '>=', $request->input('min_value'));
        }

        if ($request->has('max_value')) {
            $query->where('value', '<=', $request->input('max_value'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = strtolower($request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'name', 'value', 'probability', 'close_date', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        if ($request->boolean('all')) {
            $deals = $query->get();
            return response()->json([
                'success' => true,
                'data' => DealResource::collection($deals),
            ]);
        }

        $deals = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => DealResource::collection($deals),
            'meta' => [
                'current_page' => $deals->currentPage(),
                'last_page' => $deals->lastPage(),
                'per_page' => $deals->perPage(),
                'total' => $deals->total(),
            ],
        ]);
    }

    /**
     * Store a newly created deal.
     */
    public function store(DealRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['owner_id']) && Auth::check()) {
            $data['owner_id'] = Auth::id();
        }

        $customFields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);

        $deal = Deal::create($data);

        foreach ($customFields as $name => $val) {
            $deal->setCustomFieldValue($name, $val);
        }

        $deal->load(['stage', 'lead', 'company', 'contact', 'owner', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'message' => 'Deal created successfully',
            'data' => new DealResource($deal),
        ], 201);
    }

    /**
     * Display the specified deal.
     */
    public function show(Deal $deal): JsonResponse
    {
        $deal->load(['stage', 'lead', 'company', 'contact', 'owner', 'tasks', 'activities.user', 'calendarEvents', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'data' => new DealResource($deal),
        ]);
    }

    /**
     * Update the specified deal.
     */
    public function update(DealRequest $request, Deal $deal): JsonResponse
    {
        $data = $request->validated();
        $customFields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);

        $deal->update($data);

        foreach ($customFields as $name => $val) {
            $deal->setCustomFieldValue($name, $val);
        }

        $deal->load(['stage', 'lead', 'company', 'contact', 'owner', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'message' => 'Deal updated successfully',
            'data' => new DealResource($deal),
        ]);
    }

    /**
     * Update the stage / status of a deal.
     */
    public function updateStage(Request $request, Deal $deal): JsonResponse
    {
        $request->validate([
            'deal_stage_id' => 'required|exists:deal_stages,id',
            'status' => 'nullable|string|in:open,won,lost',
        ]);

        $stage = DealStage::find($request->input('deal_stage_id'));
        $status = $request->input('status');
        $oldStageName = $deal->stage?->name ?? 'None';

        if (!$status && $stage) {
            if (strtolower($stage->name) === 'won') {
                $status = 'won';
            } elseif (strtolower($stage->name) === 'lost') {
                $status = 'lost';
            } else {
                $status = 'open';
            }
        }

        $updateData = [
            'deal_stage_id' => $stage->id,
            'status' => $status,
        ];

        if ($status === 'won' && empty($deal->close_date)) {
            $updateData['close_date'] = now();
        }

        $deal->update($updateData);

        // Create Activity & Audit Log Entry
        \App\Models\Activity::create([
            'user_id' => Auth::id() ?? $deal->owner_id,
            'subject_type' => 'Deal',
            'subject_id' => $deal->id,
            'type' => 'Stage Changed',
            'title' => "Stage updated to {$stage->name}",
            'description' => "Deal '{$deal->name}' stage changed from '{$oldStageName}' to '{$stage->name}' (Status: " . strtoupper($status) . ").",
            'activity_date' => now(),
            'status' => 'completed',
        ]);

        $deal->load(['stage', 'lead', 'company', 'contact', 'owner', 'tasks', 'activities.user', 'calendarEvents', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'message' => 'Deal stage updated successfully',
            'data' => new DealResource($deal),
        ]);
    }

    /**
     * Remove the specified deal.
     */
    public function destroy(Deal $deal): JsonResponse
    {
        $deal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deal deleted successfully',
        ]);
    }

    /**
     * Bulk delete deals.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No deals provided for deletion.',
            ], 422);
        }

        $count = Deal::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} deals.",
        ]);
    }

    /**
     * Get metadata, aggregate stats, and options for deals.
     */
    public function metadata(): JsonResponse
    {
        $totalDeals = Deal::count();
        $openDeals = Deal::where('status', 'open')->count();
        $wonDeals = Deal::where('status', 'won')->count();
        $lostDeals = Deal::where('status', 'lost')->count();

        $totalPipelineValue = (float) Deal::where('status', 'open')->sum('value');
        $wonRevenue = (float) Deal::where('status', 'won')->sum('value');
        $winRate = ($wonDeals + $lostDeals) > 0 ? round(($wonDeals / ($wonDeals + $lostDeals)) * 100, 1) : 0;

        // All Stages with counts & sums for Kanban Pipeline
        $stages = DealStage::orderBy('order_index')->get()->map(function ($s) {
            $stageDeals = Deal::where('deal_stage_id', $s->id);
            return [
                'id' => $s->id,
                'name' => $s->name,
                'color' => $s->color,
                'order_index' => $s->order_index,
                'count' => $stageDeals->count(),
                'total_value' => (float) $stageDeals->sum('value'),
            ];
        });

        $companies = \App\Models\Company::select('id', 'name')->orderBy('name')->get();
        $contacts = \App\Models\Contact::select('id', 'first_name', 'last_name', 'company_id')->orderBy('first_name')->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => trim("{$c->first_name} {$c->last_name}"),
                'company_id' => $c->company_id,
            ];
        });
        $leads = \App\Models\Lead::select('id', 'first_name', 'last_name', 'company_name')->latest()->take(50)->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'name' => trim("{$l->first_name} {$l->last_name}") ?: ($l->company_name ?: "Lead #{$l->id}"),
            ];
        });
        $owners = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_deals' => $totalDeals,
                'open_deals' => $openDeals,
                'won_deals' => $wonDeals,
                'lost_deals' => $lostDeals,
                'total_pipeline_value' => $totalPipelineValue,
                'won_revenue' => $wonRevenue,
                'win_rate' => $winRate,
            ],
            'stages' => $stages,
            'options' => [
                'companies' => $companies,
                'contacts' => $contacts,
                'leads' => $leads,
                'owners' => $owners,
            ],
        ]);
    }
}
