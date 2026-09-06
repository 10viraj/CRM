<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Company;
use App\Models\Deal;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query) {
            return redirect()->back();
        }

        $leads = Lead::where('first_name', 'like', "%{$query}%")
                     ->orWhere('last_name', 'like', "%{$query}%")
                     ->orWhere('email', 'like', "%{$query}%")
                     ->limit(5)->get();

        $companies = Company::where('name', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%")
                            ->limit(5)->get();

        $deals = Deal::where('name', 'like', "%{$query}%")
                     ->limit(5)->get();

        return view('search.results', compact('leads', 'companies', 'deals', 'query'));
    }
}
