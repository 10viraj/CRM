<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Deal;
use App\Models\DealStage;

class DashboardController extends Controller
{
    public function index()
    {
        $totalLeads = Lead::count();
        
        $openDeals = Deal::whereHas('stage', function($q) {
            $q->whereNotIn(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), ['won', 'lost']);
        })->count();
        
        $wonDeals = Deal::whereHas('stage', function($q) {
            $q->where(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), 'won');
        })->count();
        
        $revenue = Deal::whereHas('stage', function($q) {
            $q->where(\Illuminate\Support\Facades\DB::raw('LOWER(name)'), 'won');
        })->sum('value');

        $stages = DealStage::withCount('deals')->get();
        $dealsByStage = [];
        $totalDealsForStage = 0;
        foreach($stages as $stage) {
            $dealsByStage[] = [
                'name' => $stage->name,
                'count' => $stage->deals_count
            ];
            $totalDealsForStage += $stage->deals_count;
        }

        // Calculate percentages
        foreach($dealsByStage as &$stg) {
            $stg['percentage'] = $totalDealsForStage > 0 ? round(($stg['count'] / $totalDealsForStage) * 100) : 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'kpis' => [
                    'total_leads' => [ 'value' => $totalLeads, 'change' => '+12.5%' ],
                    'open_deals' => [ 'value' => $openDeals, 'change' => '+8.4%' ],
                    'won_deals' => [ 'value' => $wonDeals, 'change' => '+15.3%' ],
                    'revenue' => [ 'value' => $revenue, 'change' => '+22.6%' ]
                ],
                'leads_overview' => [
                    'labels' => ['May 12', 'May 19', 'May 26', 'Jun 02', 'Jun 09'],
                    'new_leads' => [120, 100, 130, 90, 140],
                    'converted_leads' => [40, 35, 70, 60, 100]
                ],
                'deals_by_stage' => $dealsByStage,
                'recent_activities' => [
                    ['icon' => 'lead', 'text' => 'New lead "Acme Corp" added', 'time' => '2 mins ago', 'color' => 'blue'],
                    ['icon' => 'deal', 'text' => 'Deal "Website Project" moved to Proposal', 'time' => '15 mins ago', 'color' => 'yellow'],
                    ['icon' => 'task', 'text' => 'Task "Follow up with John" completed', 'time' => '1 hour ago', 'color' => 'green'],
                    ['icon' => 'contact', 'text' => 'New contact "Sarah Johnson" added', 'time' => '2 hours ago', 'color' => 'purple']
                ]
            ]
        ]);
    }
}
