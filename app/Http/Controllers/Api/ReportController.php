<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\DealStage;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // 1. Sales & Deals Metrics
        $totalDeals = Deal::count();
        $wonDeals = Deal::whereHas('stage', function($q) {
            $q->where(DB::raw('LOWER(name)'), 'won');
        })->count();
        $lostDeals = Deal::whereHas('stage', function($q) {
            $q->where(DB::raw('LOWER(name)'), 'lost');
        })->count();
        $openDeals = Deal::whereHas('stage', function($q) {
            $q->whereNotIn(DB::raw('LOWER(name)'), ['won', 'lost']);
        })->count();
        
        $wonRevenue = (float) Deal::whereHas('stage', function($q) {
            $q->where(DB::raw('LOWER(name)'), 'won');
        })->sum('value');

        $pipelineValue = (float) Deal::whereHas('stage', function($q) {
            $q->whereNotIn(DB::raw('LOWER(name)'), ['won', 'lost']);
        })->sum('value');

        $winRate = ($wonDeals + $lostDeals) > 0 
            ? round(($wonDeals / ($wonDeals + $lostDeals)) * 100, 1) 
            : 0;

        $avgDealSize = $totalDeals > 0 
            ? round(Deal::sum('value') / $totalDeals, 2) 
            : 0;

        // Deals by Stage
        $stages = DealStage::withCount('deals')->get();
        $dealsByStage = $stages->map(function($stage) use ($totalDeals) {
            return [
                'name' => $stage->name,
                'color' => $stage->color ?? 'blue',
                'count' => $stage->deals_count,
                'percentage' => $totalDeals > 0 ? round(($stage->deals_count / $totalDeals) * 100) : 0,
            ];
        });

        // 2. Leads Metrics
        $totalLeads = Lead::count();
        $qualifiedLeads = Lead::whereHas('status', function($q) {
            $q->whereIn(DB::raw('LOWER(name)'), ['qualified', 'won', 'proposal sent']);
        })->count();

        $leadConversionRate = $totalLeads > 0 
            ? round(($wonDeals / $totalLeads) * 100, 1) 
            : 0;

        $leadsBySource = LeadSource::withCount('leads')->get()->map(function($src) use ($totalLeads) {
            return [
                'name' => $src->name,
                'count' => $src->leads_count,
                'percentage' => $totalLeads > 0 ? round(($src->leads_count / $totalLeads) * 100) : 0,
            ];
        });

        $leadsByStatus = LeadStatus::withCount('leads')->get()->map(function($st) use ($totalLeads) {
            return [
                'name' => $st->name,
                'color' => $st->color ?? 'blue',
                'count' => $st->leads_count,
                'percentage' => $totalLeads > 0 ? round(($st->leads_count / $totalLeads) * 100) : 0,
            ];
        });

        // 3. Task Productivity Metrics
        $totalTasks = Task::count();
        $completedTasks = Task::where('status', 'Completed')->count();
        $pendingTasks = Task::where('status', 'Pending')->count();
        $inProgressTasks = Task::where('status', 'In Progress')->count();
        $overdueTasks = Task::where('status', '!=', 'Completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->count();

        $taskCompletionRate = $totalTasks > 0 
            ? round(($completedTasks / $totalTasks) * 100, 1) 
            : 0;

        // 4. Monthly Trend (Last 6 Months)
        $months = [];
        $revenueTrend = [];
        $dealsCountTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $monthLabel = $date->format('M Y');
            $months[] = $monthLabel;

            $monthWonRevenue = (float) Deal::whereHas('stage', function($q) {
                $q->where(DB::raw('LOWER(name)'), 'won');
            })
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->sum('value');

            $monthDealsCount = Deal::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $revenueTrend[] = $monthWonRevenue;
            $dealsCountTrend[] = $monthDealsCount;
        }

        // 5. Team Rep Performance
        $teamPerformance = User::withCount(['deals', 'tasks'])->take(6)->get()->map(function($user) {
            $wonRev = (float) Deal::where('owner_id', $user->id)
                ->whereHas('stage', function($q) { $q->where(DB::raw('LOWER(name)'), 'won'); })
                ->sum('value');
            $completedTasks = Task::where('assign_to_id', $user->id)
                ->where('status', 'Completed')
                ->count();
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'deals_count' => $user->deals_count,
                'won_revenue' => $wonRev,
                'completed_tasks' => $completedTasks,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'sales' => [
                    'total_deals' => $totalDeals,
                    'won_deals' => $wonDeals,
                    'lost_deals' => $lostDeals,
                    'open_deals' => $openDeals,
                    'won_revenue' => $wonRevenue,
                    'pipeline_value' => $pipelineValue,
                    'win_rate' => $winRate,
                    'avg_deal_size' => $avgDealSize,
                    'by_stage' => $dealsByStage,
                ],
                'leads' => [
                    'total_leads' => $totalLeads,
                    'qualified_leads' => $qualifiedLeads,
                    'conversion_rate' => $leadConversionRate,
                    'by_source' => $leadsBySource,
                    'by_status' => $leadsByStatus,
                ],
                'tasks' => [
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'pending_tasks' => $pendingTasks,
                    'in_progress_tasks' => $inProgressTasks,
                    'overdue_tasks' => $overdueTasks,
                    'completion_rate' => $taskCompletionRate,
                ],
                'trends' => [
                    'labels' => $months,
                    'revenue' => $revenueTrend,
                    'deals_count' => $dealsCountTrend,
                ],
                'team' => $teamPerformance,
            ]
        ]);
    }
}
