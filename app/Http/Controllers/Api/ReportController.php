<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ReportController extends Controller
{
    /**
     * Get dynamic, multi-dimensional CRM reports from the database with flexible filtering.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Resolve Filter Parameters
        $range = $request->input('range', 'all');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');
        $userId = $request->input('user_id');
        $roleId = $request->input('role_id');
        $sourceId = $request->input('source_id');
        $stageId = $request->input('stage_id');
        $statusFilter = $request->input('status');

        [$startDate, $endDate, $rangeLabel] = $this->resolveDateRange($range, $customStart, $customEnd);

        // Filter user IDs if team/role is selected
        $targetUserIds = null;
        if ($roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $targetUserIds = User::role($role->name)->pluck('id')->toArray();
            }
        }
        if ($userId) {
            $targetUserIds = [$userId];
        }

        // Base Queries
        $dealsQuery = Deal::query();
        $leadsQuery = Lead::query();
        $tasksQuery = Task::query();

        // Apply Date Filters
        if ($startDate && $endDate) {
            $dealsQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('close_date', [$startDate, $endDate]);
            });
            $leadsQuery->whereBetween('created_at', [$startDate, $endDate]);
            $tasksQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Apply User / Team Filters
        if ($targetUserIds !== null) {
            $dealsQuery->whereIn('owner_id', $targetUserIds);
            $leadsQuery->whereIn('owner_id', $targetUserIds);
            $tasksQuery->where(function ($q) use ($targetUserIds) {
                $q->whereIn('assign_to_id', $targetUserIds)
                  ->orWhereIn('creator_id', $targetUserIds);
            });
        }

        // Apply Source Filter (Leads & Deals linked to leads)
        if ($sourceId) {
            $leadsQuery->where('lead_source_id', $sourceId);
            $dealsQuery->whereHas('lead', function ($q) use ($sourceId) {
                $q->where('lead_source_id', $sourceId);
            });
        }

        // Apply Stage Filter
        if ($stageId) {
            $dealsQuery->where('deal_stage_id', $stageId);
        }

        // Apply Status Filter
        if ($statusFilter && strtolower($statusFilter) !== 'all') {
            $statusLower = strtolower($statusFilter);
            if (in_array($statusLower, ['open', 'won', 'lost'])) {
                $dealsQuery->where('status', $statusLower);
            }
            if (in_array($statusLower, ['pending', 'in progress', 'completed', 'cancelled'])) {
                $tasksQuery->where('status', 'like', $statusFilter);
            }
        }

        // ==========================================
        // 1. DEALS & PIPELINE REPORT
        // ==========================================
        $totalDeals = (clone $dealsQuery)->count();
        $wonDealsCount = (clone $dealsQuery)->where(function ($q) {
            $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                $sq->where(DB::raw('LOWER(name)'), 'won');
            });
        })->count();

        $lostDealsCount = (clone $dealsQuery)->where(function ($q) {
            $q->where('status', 'lost')->orWhereHas('stage', function ($sq) {
                $sq->where(DB::raw('LOWER(name)'), 'lost');
            });
        })->count();

        $openDealsCount = (clone $dealsQuery)->where(function ($q) {
            $q->where('status', 'open')->orWhereNull('status')
              ->whereDoesntHave('stage', function ($sq) {
                  $sq->whereIn(DB::raw('LOWER(name)'), ['won', 'lost']);
              });
        })->count();

        $wonRevenue = (float) (clone $dealsQuery)->where(function ($q) {
            $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                $sq->where(DB::raw('LOWER(name)'), 'won');
            });
        })->sum('value');

        $pipelineValue = (float) (clone $dealsQuery)->where(function ($q) {
            $q->where('status', 'open')->orWhereNull('status')
              ->whereDoesntHave('stage', function ($sq) {
                  $sq->whereIn(DB::raw('LOWER(name)'), ['won', 'lost']);
              });
        })->sum('value');

        $totalDealsValue = (float) (clone $dealsQuery)->sum('value');
        $avgDealSize = $totalDeals > 0 ? round($totalDealsValue / $totalDeals, 2) : 0;
        $winRate = ($wonDealsCount + $lostDealsCount) > 0 ? round(($wonDealsCount / ($wonDealsCount + $lostDealsCount)) * 100, 1) : 0;

        // Deals by Stage
        $stages = DealStage::orderBy('order_index')->get();
        $dealsByStage = [];
        foreach ($stages as $stg) {
            $stageDealsQ = (clone $dealsQuery)->where('deal_stage_id', $stg->id);
            $sCount = $stageDealsQ->count();
            $sValue = (float) $stageDealsQ->sum('value');

            $dealsByStage[] = [
                'id' => $stg->id,
                'name' => $stg->name,
                'color' => $stg->color,
                'count' => $sCount,
                'value' => $sValue,
                'percentage' => $totalDeals > 0 ? round(($sCount / $totalDeals) * 100, 1) : 0,
            ];
        }

        // ==========================================
        // 2. LEADS REPORT
        // ==========================================
        $totalLeads = (clone $leadsQuery)->count();
        $convertedLeads = (clone $leadsQuery)->whereHas('status', function ($q) {
            $q->where(DB::raw('LOWER(name)'), 'like', '%convert%')
              ->orWhere(DB::raw('LOWER(name)'), 'like', '%qualif%')
              ->orWhere(DB::raw('LOWER(name)'), 'like', '%won%');
        })->count();

        $leadConversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Leads by Status
        $statuses = LeadStatus::all();
        $leadsByStatus = [];
        foreach ($statuses as $st) {
            $stCount = (clone $leadsQuery)->where('lead_status_id', $st->id)->count();
            $leadsByStatus[] = [
                'id' => $st->id,
                'name' => $st->name,
                'color' => $st->color,
                'count' => $stCount,
                'percentage' => $totalLeads > 0 ? round(($stCount / $totalLeads) * 100, 1) : 0,
            ];
        }

        // ==========================================
        // 3. LEAD SOURCES REPORT
        // ==========================================
        $sources = LeadSource::all();
        $leadSourcesReport = [];
        foreach ($sources as $src) {
            $srcLeadsQ = (clone $leadsQuery)->where('lead_source_id', $src->id);
            $srcCount = $srcLeadsQ->count();

            // Revenue generated from this lead source
            $srcRevenue = (float) Deal::whereHas('lead', function ($q) use ($src) {
                $q->where('lead_source_id', $src->id);
            })->where(function ($q) {
                $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                    $sq->where(DB::raw('LOWER(name)'), 'won');
                });
            })->sum('value');

            $srcWonDeals = Deal::whereHas('lead', function ($q) use ($src) {
                $q->where('lead_source_id', $src->id);
            })->where(function ($q) {
                $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                    $sq->where(DB::raw('LOWER(name)'), 'won');
                });
            })->count();

            $leadSourcesReport[] = [
                'id' => $src->id,
                'name' => $src->name,
                'count' => $srcCount,
                'revenue' => $srcRevenue,
                'won_deals' => $srcWonDeals,
                'percentage' => $totalLeads > 0 ? round(($srcCount / $totalLeads) * 100, 1) : 0,
            ];
        }

        // ==========================================
        // 4. TASKS PRODUCTIVITY REPORT
        // ==========================================
        $totalTasks = (clone $tasksQuery)->count();
        $completedTasks = (clone $tasksQuery)->where('status', 'Completed')->count();
        $pendingTasks = (clone $tasksQuery)->where('status', 'Pending')->count();
        $inProgressTasks = (clone $tasksQuery)->where('status', 'In Progress')->count();
        $overdueTasks = (clone $tasksQuery)->where('status', '!=', 'Completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        $taskCompletionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

        $tasksByPriority = [
            ['priority' => 'Urgent', 'count' => (clone $tasksQuery)->where('priority', 'Urgent')->count(), 'color' => '#ef4444'],
            ['priority' => 'High', 'count' => (clone $tasksQuery)->where('priority', 'High')->count(), 'color' => '#f59e0b'],
            ['priority' => 'Medium', 'count' => (clone $tasksQuery)->where('priority', 'Medium')->count(), 'color' => '#3b82f6'],
            ['priority' => 'Low', 'count' => (clone $tasksQuery)->where('priority', 'Low')->count(), 'color' => '#64748b'],
        ];

        // ==========================================
        // 5. MONTHLY REVENUE & DEALS TREND
        // ==========================================
        $monthlyLabels = [];
        $monthlyRevenue = [];
        $monthlyDeals = [];

        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $mStart = (clone $m)->startOfMonth();
            $mEnd = (clone $m)->endOfMonth();

            $monthlyLabels[] = $m->format('M Y');

            $mWonRev = (float) Deal::where(function ($q) {
                $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                    $sq->where(DB::raw('LOWER(name)'), 'won');
                });
            })->where(function ($q) use ($mStart, $mEnd) {
                $q->whereBetween('close_date', [$mStart, $mEnd])
                  ->orWhereBetween('updated_at', [$mStart, $mEnd]);
            })->sum('value');

            $mDealCount = Deal::whereBetween('created_at', [$mStart, $mEnd])->count();

            $monthlyRevenue[] = $mWonRev;
            $monthlyDeals[] = $mDealCount;
        }

        // ==========================================
        // 6. SALES TEAM PERFORMANCE
        // ==========================================
        $salesPerformance = User::withCount(['deals', 'tasks'])->get()->map(function ($u) {
            $userWonRev = (float) Deal::where('owner_id', $u->id)
                ->where(function ($q) {
                    $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                        $sq->where(DB::raw('LOWER(name)'), 'won');
                    });
                })->sum('value');

            $userWonDeals = Deal::where('owner_id', $u->id)
                ->where(function ($q) {
                    $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                        $sq->where(DB::raw('LOWER(name)'), 'won');
                    });
                })->count();

            $userCompletedTasks = Task::where('assign_to_id', $u->id)
                ->where('status', 'Completed')
                ->count();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'total_deals' => $u->deals_count,
                'won_deals' => $userWonDeals,
                'won_revenue' => $userWonRev,
                'win_rate' => $u->deals_count > 0 ? round(($userWonDeals / $u->deals_count) * 100) : 0,
                'completed_tasks' => $userCompletedTasks,
            ];
        })->sortByDesc('won_revenue')->values();

        // ==========================================
        // 7. FILTER OPTIONS
        // ==========================================
        $filterOptions = [
            'users' => User::select('id', 'name', 'email')->orderBy('name')->get(),
            'roles' => Role::select('id', 'name')->get(),
            'sources' => LeadSource::select('id', 'name')->get(),
            'stages' => DealStage::select('id', 'name')->orderBy('order_index')->get(),
            'statuses' => ['All', 'Open', 'Won', 'Lost', 'Pending', 'In Progress', 'Completed'],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'filters' => [
                    'range' => $range,
                    'range_label' => $rangeLabel,
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'source_id' => $sourceId,
                    'stage_id' => $stageId,
                    'status' => $statusFilter,
                ],
                'options' => $filterOptions,
                'deals' => [
                    'total_deals' => $totalDeals,
                    'won_deals' => $wonDealsCount,
                    'lost_deals' => $lostDealsCount,
                    'open_deals' => $openDealsCount,
                    'won_revenue' => $wonRevenue,
                    'pipeline_value' => $pipelineValue,
                    'total_deals_value' => $totalDealsValue,
                    'avg_deal_size' => $avgDealSize,
                    'win_rate' => $winRate,
                    'by_stage' => $dealsByStage,
                ],
                'leads' => [
                    'total_leads' => $totalLeads,
                    'converted_leads' => $convertedLeads,
                    'conversion_rate' => $leadConversionRate,
                    'by_status' => $leadsByStatus,
                    'by_source' => $leadSourcesReport,
                ],
                'tasks' => [
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'pending_tasks' => $pendingTasks,
                    'in_progress_tasks' => $inProgressTasks,
                    'overdue_tasks' => $overdueTasks,
                    'completion_rate' => $taskCompletionRate,
                    'by_priority' => $tasksByPriority,
                ],
                'trends' => [
                    'labels' => $monthlyLabels,
                    'revenue' => $monthlyRevenue,
                    'deals_count' => $monthlyDeals,
                ],
                'sales_performance' => $salesPerformance,
            ],
        ]);
    }

    /**
     * Helper to resolve standard date intervals.
     */
    private function resolveDateRange(string $range, ?string $customStart, ?string $customEnd): array
    {
        $now = Carbon::now();

        if ($customStart && $customEnd) {
            $start = Carbon::parse($customStart)->startOfDay();
            $end = Carbon::parse($customEnd)->endOfDay();
            $label = "{$start->format('M d, Y')} - {$end->format('M d, Y')}";
            return [$start, $end, $label];
        }

        switch ($range) {
            case 'today':
                return [(clone $now)->startOfDay(), (clone $now)->endOfDay(), 'Today'];
            case 'this_week':
                return [(clone $now)->startOfWeek(), (clone $now)->endOfWeek(), 'This Week'];
            case 'this_month':
                return [(clone $now)->startOfMonth(), (clone $now)->endOfMonth(), 'This Month'];
            case 'last_30_days':
                return [(clone $now)->subDays(30)->startOfDay(), (clone $now)->endOfDay(), 'Last 30 Days'];
            case 'this_quarter':
                return [(clone $now)->startOfQuarter(), (clone $now)->endOfQuarter(), 'This Quarter'];
            case 'this_year':
                return [(clone $now)->startOfYear(), (clone $now)->endOfYear(), 'This Year'];
            case 'all':
            default:
                return [null, null, 'All Time'];
        }
    }
}
