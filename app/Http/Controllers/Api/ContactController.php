<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts with search, filtering, sorting, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Contact::with(['company', 'owner', 'lead', 'customFieldValues.field']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        // Filtering
        if ($companyId = $request->input('company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($ownerId = $request->input('owner_id')) {
            $query->where('owner_id', $ownerId);
        }

        if ($leadId = $request->input('lead_id')) {
            $query->where('lead_id', $leadId);
        }

        if ($request->has('is_primary')) {
            $query->where('is_primary', $request->boolean('is_primary'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = strtolower($request->input('sort_direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'first_name', 'last_name', 'email', 'job_title', 'created_at', 'updated_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);

        if ($request->boolean('all')) {
            $contacts = $query->get();
            return response()->json([
                'success' => true,
                'data' => ContactResource::collection($contacts),
            ]);
        }

        $contacts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ContactResource::collection($contacts),
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'last_page' => $contacts->lastPage(),
                'per_page' => $contacts->perPage(),
                'total' => $contacts->total(),
            ],
        ]);
    }

    /**
     * Store a newly created contact.
     */
    public function store(ContactRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['owner_id']) && Auth::check()) {
            $data['owner_id'] = Auth::id();
        }

        $customFields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);

        $contact = Contact::create($data);

        foreach ($customFields as $name => $val) {
            $contact->setCustomFieldValue($name, $val);
        }

        $contact->load(['company', 'owner', 'lead', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully',
            'data' => new ContactResource($contact),
        ], 201);
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact): JsonResponse
    {
        $contact->load(['company', 'owner', 'lead', 'deals', 'tasks', 'activities.user', 'calendarEvents', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'data' => new ContactResource($contact),
        ]);
    }

    /**
     * Update the specified contact.
     */
    public function update(ContactRequest $request, Contact $contact): JsonResponse
    {
        $data = $request->validated();
        $customFields = $data['custom_fields'] ?? [];
        unset($data['custom_fields']);

        $contact->update($data);

        foreach ($customFields as $name => $val) {
            $contact->setCustomFieldValue($name, $val);
        }

        $contact->load(['company', 'owner', 'lead', 'customFieldValues.field']);

        return response()->json([
            'success' => true,
            'message' => 'Contact updated successfully',
            'data' => new ContactResource($contact),
        ]);
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully',
        ]);
    }

    /**
     * Bulk delete contacts.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No contacts provided for deletion.',
            ], 422);
        }

        $count = Contact::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} contacts.",
        ]);
    }

    /**
     * Get metadata and filter/form options for contacts.
     */
    public function metadata(): JsonResponse
    {
        $total = Contact::count();
        $primary = Contact::where('is_primary', true)->count();
        $withCompany = Contact::whereNotNull('company_id')->count();
        $withDeals = Contact::has('deals')->count();

        $companies = \App\Models\Company::select('id', 'name')->orderBy('name')->get();
        $owners = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $leads = \App\Models\Lead::select('id', 'first_name', 'last_name', 'company_name')->latest()->take(50)->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'name' => trim("{$l->first_name} {$l->last_name}") ?: ($l->company_name ?: "Lead #{$l->id}"),
            ];
        });
        $departments = Contact::whereNotNull('department')->where('department', '!=', '')->distinct()->pluck('department');

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $total,
                'primary' => $primary,
                'with_company' => $withCompany,
                'with_deals' => $withDeals,
            ],
            'options' => [
                'companies' => $companies,
                'owners' => $owners,
                'leads' => $leads,
                'departments' => $departments,
            ],
        ]);
    }
}
