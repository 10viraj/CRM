<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::withCount(['contacts', 'deals', 'leads']);

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('industry', 'like', "%{$searchTerm}%")
                  ->orWhere('city', 'like', "%{$searchTerm}%")
                  ->orWhere('country', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('industry')) {
            $query->where('industry', $request->industry);
        }

        $companies = $query->latest()->paginate(15)->withQueryString();
        $industries = Company::whereNotNull('industry')->distinct()->pluck('industry');

        return view('companies.index', compact('companies', 'industries'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $company = Company::create($validated);

        return redirect()->route('companies.show', $company)->with('success', 'Company profile created successfully.');
    }

    public function show(Company $company)
    {
        $company->load([
            'contacts',
            'deals.stage',
            'leads',
            'activities' => function ($q) {
                $q->latest()->take(10);
            }
        ]);

        $wonDealsCount = $company->deals->where('status', 'won')->count();
        $totalPipelineValue = $company->deals->sum('value');

        return view('companies.show', compact('company', 'wonDealsCount', 'totalPipelineValue'));
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $company->update($validated);

        return redirect()->route('companies.show', $company)->with('success', 'Company details updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }
}
