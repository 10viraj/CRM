<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    /**
     * Get a list of all leads for the API.
     */
    public function index()
    {
        $leads = Lead::with(['status', 'source', 'owner'])->latest()->get();
        return response()->json([
            'success' => true,
            'data' => $leads
        ]);
    }

    /**
     * Store a newly created lead from the API.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:leads',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Create the lead
        $lead = Lead::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'notes' => 'Submitted via Customer Portal: ' . $request->notes,
            // Assuming 1 is "New" or similar default status
            'lead_status_id' => 1, 
            'lead_source_id' => 1,
            'owner_id' => 1, // Assign to Super Admin or default user
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully',
            'data' => $lead
        ], 201);
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:leads,email,' . $lead->id,
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $lead->update($request->only(['first_name', 'last_name', 'email', 'phone', 'company_name', 'notes']));

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data' => $lead
        ]);
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully'
        ]);
    }

    /**
     * Remove multiple leads from storage.
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:leads,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        Lead::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' leads deleted successfully'
        ]);
    }
}
