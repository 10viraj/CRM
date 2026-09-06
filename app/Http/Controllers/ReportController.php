<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\FollowUp;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Sales Report Data
        $salesData = [
            'total_sales' => Deal::count(),
            'won_deals' => Deal::whereHas('stage', function($q) { $q->where('name', 'Won'); })->count(),
            'lost_deals' => Deal::whereHas('stage', function($q) { $q->where('name', 'Lost'); })->count(),
            'revenue' => Deal::whereHas('stage', function($q) { $q->where('name', 'Won'); })->sum('value')
        ];

        // Lead Report Data
        $totalLeads = Lead::count();
        $wonLeads = Lead::whereHas('deals', function($q) {
            $q->whereHas('stage', function($q) { $q->where('name', 'Won'); });
        })->distinct()->count();
        
        $leadData = [
            'total_leads' => $totalLeads,
            'conversion_rate' => $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0,
            'by_source' => Lead::select('lead_source_id', DB::raw('count(*) as count'))
                               ->with('source')
                               ->groupBy('lead_source_id')
                               ->get(),
            'by_status' => Lead::select('lead_status_id', DB::raw('count(*) as count'))
                               ->with('status')
                               ->groupBy('lead_status_id')
                               ->get()
        ];

        // Follow-Up Report Data
        $followUpData = [
            'pending' => FollowUp::where('status', 'pending')->count(),
            'completed' => FollowUp::where('status', 'completed')->count(),
            'overdue' => FollowUp::where('status', 'pending')->where('due_date', '<', now())->count()
        ];

        // Revenue Chart Data (Last 6 Months by default)
        $months = collect();
        $revenueValues = collect();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $months->push($date->format('M Y'));
            
            // Calculate revenue from paid invoices for that month
            $revenue = Invoice::where('status', 'paid')
                ->whereYear('date', $date->year)
                ->whereMonth('date', $date->month)
                ->sum('amount_paid');
                
            $revenueValues->push($revenue);
        }

        $chartData = [
            'labels' => $months->toArray(),
            'data' => $revenueValues->toArray()
        ];

        return view('reports.index', compact('salesData', 'leadData', 'followUpData', 'chartData'));
    }
}
