<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    /**
     * Display a listing of leads with search, filtering, sorting, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::with(['status', 'source', 'owner', 'companyModel', 'customFieldValues.field']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Filtering
        if ($statusId = $request->input('lead_status_id') ?: $request->input('status_id')) {
            $query->where('lead_status_id', $statusId);
        }

        if ($sourceId = $request->input('lead_source_id') ?: $request->input('source_id')) {
            $query->where('lead_source_id', $sourceId);
        }

        if ($ownerId = $request->input('owner_id')) {
            $query->where('owner_id', $ownerId);
        }

        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($request->has('min_score')) {
            $query->where('score', '>=', $request->input('min_score'));
        }

        if ($request->has('max_score')) {
            $query->where('score', '<=', $request->input('max_score'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = strtolower($request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'first_name', 'last_name', 'email', 'company', 'score', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        if ($request->boolean('all')) {
            $leads = $query->get();
            return response()->json([
                'success' => true,
                'data' => LeadResource::collection($leads),
            ]);
        }

        $leads = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => LeadResource::collection($leads),
            'meta' => [
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'per_page' => $leads->perPage(),
                'total' => $leads->total(),
            ],
        ]);
    }

    /**
     * Store a newly created lead.
     */
    public function store(LeadRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['owner_id']) && Auth::check()) {
            $data['owner_id'] = Auth::id();
        }

        $customFields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);

        $lead = Lead::create($data);

        // Save custom fields
        foreach ($customFields as $name => $val) {
            $lead->setCustomFieldValue($name, $val);
        }

        $lead->load(['status', 'source', 'owner', 'companyModel', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully',
            'data' => new LeadResource($lead),
        ], 201);
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead): JsonResponse
    {
        $lead->load(['status', 'source', 'owner', 'companyModel', 'contacts', 'deals', 'tasks', 'activities', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'data' => new LeadResource($lead),
        ]);
    }

    /**
     * Update the specified lead.
     */
    public function update(LeadRequest $request, Lead $lead): JsonResponse
    {
        $data = $request->validated();
        $customFields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);

        $lead->update($data);

        foreach ($customFields as $name => $val) {
            $lead->setCustomFieldValue($name, $val);
        }

        $lead->load(['status', 'source', 'owner', 'companyModel', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => new LeadResource($lead),
        ]);
    }

    /**
     * Remove the specified lead.
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully',
        ]);
    }

    /**
     * Bulk delete leads.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:leads,id',
        ]);

        $count = Lead::whereIn('id', $request->input('ids'))->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} leads deleted successfully",
        ]);
    }

    /**
     * Get lead form and filter metadata (statuses, sources, users, companies).
     */
    public function metadata(): JsonResponse
    {
        $statuses = \App\Models\LeadStatus::all(['id', 'name', 'color']);
        $sources = \App\Models\LeadSource::all(['id', 'name']);
        $users = \App\Models\User::all(['id', 'name', 'email']);
        $companies = \App\Models\Company::all(['id', 'name']);

        return response()->json([
            'success' => true,
            'statuses' => $statuses,
            'sources' => $sources,
            'users' => $users,
            'companies' => $companies,
        ]);
    }
}

