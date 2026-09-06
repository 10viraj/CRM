<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['source', 'status', 'owner']);

        // Filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_id')) {
            $query->where('lead_status_id', $request->status_id);
        }

        if ($request->filled('source_id')) {
            $query->where('lead_source_id', $request->source_id);
        }

        // Sorting
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $query->orderBy($sort, $direction);

        $leads = $query->paginate(15)->withQueryString();
        
        $statuses = LeadStatus::all();
        $sources = LeadSource::all();

        return view('leads.index', compact('leads', 'statuses', 'sources'));
    }

    public function create()
    {
        $statuses = LeadStatus::all();
        $sources = LeadSource::all();
        return view('leads.create', compact('statuses', 'sources'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:leads,email',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'lead_source_id' => 'required|exists:lead_sources,id',
            'lead_status_id' => 'required|exists:lead_statuses,id',
            'score' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['owner_id'] = Auth::id();

        $lead = Lead::create($validated);

        // Create an initial activity
        $lead->activities()->create([
            'user_id' => Auth::id(),
            'type' => 'status_change',
            'description' => 'Lead manually created.',
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
    }

    public function show(Lead $lead)
    {
        $lead->load(['source', 'status', 'owner', 'activities.user']);
        // Order activities newest first
        $activities = $lead->activities()->latest()->get();
        return view('leads.show', compact('lead', 'activities'));
    }

    public function edit(Lead $lead)
    {
        $statuses = LeadStatus::all();
        $sources = LeadSource::all();
        return view('leads.edit', compact('lead', 'statuses', 'sources'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:leads,email,' . $lead->id,
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'lead_source_id' => 'required|exists:lead_sources,id',
            'lead_status_id' => 'required|exists:lead_statuses,id',
            'score' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:leads,id'
        ]);

        Lead::whereIn('id', $request->ids)->delete();

        return redirect()->route('leads.index')->with('success', count($request->ids) . ' leads deleted successfully.');
    }
}
