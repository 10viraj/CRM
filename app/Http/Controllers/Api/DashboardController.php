<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dynamic dashboard KPIs, charts, and activity feeds from real database records.
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Determine Date Range Filter
        $range = $request->input('range', 'this_month');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');

        [$startDate, $endDate, $prevStartDate, $prevEndDate, $rangeLabel] = $this->resolveDateRanges($range, $customStart, $customEnd);

        // 2. Real KPIs for Current & Previous Period
        $leadsQuery = Lead::query();
        $prevLeadsQuery = Lead::query();

        $dealsQuery = Deal::query();
        $prevDealsQuery = Deal::query();

        $tasksQuery = Task::query();

        if ($startDate && $endDate) {
            $leadsQuery->whereBetween('created_at', [$startDate, $endDate]);
            $prevLeadsQuery->whereBetween('created_at', [$prevStartDate, $prevEndDate]);

            $dealsQuery->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate])
                  ->orWhereBetween('close_date', [$startDate, $endDate]);
            });
            $prevDealsQuery->where(function ($q) use ($prevStartDate, $prevEndDate) {
                $q->whereBetween('created_at', [$prevStartDate, $prevEndDate])
                  ->orWhereBetween('close_date', [$prevStartDate, $prevEndDate]);
            });
        }

        // Total Leads
        $totalLeads = $leadsQuery->count();
        $prevTotalLeads = $prevLeadsQuery->count();
        $leadsChange = $this->calculatePercentageChange($totalLeads, $prevTotalLeads);

        // Converted Leads
        $convertedLeadsCount = Lead::whereHas('status', function ($sq) {
            $sq->where(DB::raw('LOWER(name)'), 'like', '%convert%')
              ->orWhere(DB::raw('LOWER(name)'), 'like', '%qualif%')
              ->orWhere(DB::raw('LOWER(name)'), 'like', '%won%');
        })->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        // Open Deals
        $openDeals = Deal::where(function ($q) {
            $q->where('status', 'open')
              ->orWhereNull('status')
              ->whereDoesntHave('stage', function ($sq) {
                  $sq->whereIn(DB::raw('LOWER(name)'), ['won', 'lost']);
              });
        })->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        })->count();

        $prevOpenDeals = Deal::where(function ($q) {
            $q->where('status', 'open')
              ->orWhereNull('status')
              ->whereDoesntHave('stage', function ($sq) {
                  $sq->whereIn(DB::raw('LOWER(name)'), ['won', 'lost']);
              });
        })->when($prevStartDate && $prevEndDate, function ($q) use ($prevStartDate, $prevEndDate) {
            $q->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
        })->count();
        $openDealsChange = $this->calculatePercentageChange($openDeals, $prevOpenDeals);

        // Won Deals
        $wonDealsQuery = Deal::where(function ($q) {
            $q->where('status', 'won')
              ->orWhereHas('stage', function ($sq) {
                  $sq->where(DB::raw('LOWER(name)'), 'won');
              });
        });

        $wonDeals = (clone $wonDealsQuery)->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->where(function ($sq) use ($startDate, $endDate) {
                $sq->whereBetween('close_date', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate]);
            });
        })->count();

        $prevWonDeals = (clone $wonDealsQuery)->when($prevStartDate && $prevEndDate, function ($q) use ($prevStartDate, $prevEndDate) {
            $q->where(function ($sq) use ($prevStartDate, $prevEndDate) {
                $sq->whereBetween('close_date', [$prevStartDate, $prevEndDate])
                  ->orWhereBetween('updated_at', [$prevStartDate, $prevEndDate]);
            });
        })->count();
        $wonDealsChange = $this->calculatePercentageChange($wonDeals, $prevWonDeals);

        // Revenue (Sum of won deals value)
        $revenue = (float) (clone $wonDealsQuery)->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            $q->where(function ($sq) use ($startDate, $endDate) {
                $sq->whereBetween('close_date', [$startDate, $endDate])
                  ->orWhereBetween('updated_at', [$startDate, $endDate]);
            });
        })->sum('value');

        $prevRevenue = (float) (clone $wonDealsQuery)->when($prevStartDate && $prevEndDate, function ($q) use ($prevStartDate, $prevEndDate) {
            $q->where(function ($sq) use ($prevStartDate, $prevEndDate) {
                $sq->whereBetween('close_date', [$prevStartDate, $prevEndDate])
                  ->orWhereBetween('updated_at', [$prevStartDate, $prevEndDate]);
            });
        })->sum('value');
        $revenueChange = $this->calculatePercentageChange($revenue, $prevRevenue);

        // Conversion Rate
        $totalDealsCount = Deal::count();
        $totalWonDealsCount = Deal::where('status', 'won')->orWhereHas('stage', function ($q) {
            $q->where(DB::raw('LOWER(name)'), 'won');
        })->count();

        $allLeadsCount = Lead::count();
        $allConvertedLeads = Lead::whereHas('status', function ($q) {
            $q->where(DB::raw('LOWER(name)'), 'like', '%convert%')
              ->orWhere(DB::raw('LOWER(name)'), 'like', '%qualif%')
              ->orWhere(DB::raw('LOWER(name)'), 'like', '%won%');
        })->count();

        $leadConversionRate = $allLeadsCount > 0 ? round(($allConvertedLeads / $allLeadsCount) * 100, 1) : 0;
        $dealWinRate = $totalDealsCount > 0 ? round(($totalWonDealsCount / $totalDealsCount) * 100, 1) : 0;
        $conversionRate = $leadConversionRate ?: $dealWinRate;

        // Pending Tasks
        $pendingTasks = Task::whereIn('status', ['Pending', 'In Progress'])->count();
        $overdueTasks = Task::where('status', '!=', 'Completed')
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        // 3. Dynamic Chart 1: Leads Overview & Converted Leads Over Time
        $leadsOverview = $this->buildLeadsTimeline($startDate, $endDate);

        // 4. Dynamic Chart 2: Deals by Stage (Real Stage counts & percentage from DB)
        $stages = DealStage::orderBy('order_index')->get();
        $dealsByStage = [];
        $totalDealsAllStages = 0;

        foreach ($stages as $stg) {
            $stgDealsCount = Deal::where('deal_stage_id', $stg->id)->count();
            $stgDealsValue = (float) Deal::where('deal_stage_id', $stg->id)->sum('value');
            $totalDealsAllStages += $stgDealsCount;

            $dealsByStage[] = [
                'id' => $stg->id,
                'name' => $stg->name,
                'color' => $stg->color,
                'count' => $stgDealsCount,
                'value' => $stgDealsValue,
            ];
        }

        foreach ($dealsByStage as &$stg) {
            $stg['percentage'] = $totalDealsAllStages > 0 ? round(($stg['count'] / $totalDealsAllStages) * 100, 1) : 0;
        }

        // 5. Dynamic Chart 3: Revenue Trend (Monthly aggregated won revenue)
        $revenueTrend = $this->buildRevenueTrend();

        // 6. Dynamic Chart 4: Sales Performance by Team Member
        $salesPerformance = User::withCount(['deals as total_deals_count', 'deals as won_deals_count' => function ($q) {
            $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                $sq->where(DB::raw('LOWER(name)'), 'won');
            });
        }])->get()->map(function ($user) {
            $wonRevenue = (float) Deal::where('owner_id', $user->id)
                ->where(function ($q) {
                    $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                        $sq->where(DB::raw('LOWER(name)'), 'won');
                    });
                })->sum('value');

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'total_deals' => $user->total_deals_count,
                'won_deals' => $user->won_deals_count,
                'won_revenue' => $wonRevenue,
                'win_rate' => $user->total_deals_count > 0 ? round(($user->won_deals_count / $user->total_deals_count) * 100) : 0,
            ];
        })->sortByDesc('won_revenue')->values()->take(5);

        // 7. Dynamic Feed: Recent Activities (Real DB activity entries)
        $activities = Activity::with(['user', 'subject'])->latest()->take(8)->get()->map(function ($act) {
            $icon = 'activity';
            $color = 'blue';
            $typeLower = strtolower($act->type ?? '');

            if (str_contains($typeLower, 'lead')) {
                $icon = 'lead';
                $color = 'blue';
            } elseif (str_contains($typeLower, 'deal') || str_contains($typeLower, 'stage')) {
                $icon = 'deal';
                $color = 'amber';
            } elseif (str_contains($typeLower, 'task')) {
                $icon = 'task';
                $color = 'emerald';
            } elseif (str_contains($typeLower, 'contact')) {
                $icon = 'contact';
                $color = 'purple';
            } elseif (str_contains($typeLower, 'call') || str_contains($typeLower, 'meeting')) {
                $icon = 'meeting';
                $color = 'indigo';
            }

            return [
                'id' => $act->id,
                'icon' => $icon,
                'color' => $color,
                'type' => $act->type,
                'title' => $act->title ?? $act->type,
                'text' => $act->description ?: ($act->title ?? 'Activity recorded'),
                'user_name' => $act->user?->name ?? 'System',
                'time' => $act->created_at ? $act->created_at->diffForHumans() : 'Just now',
                'created_at' => $act->created_at?->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'range' => $range,
                'range_label' => $rangeLabel,
                'kpis' => [
                    'total_leads' => [
                        'value' => $totalLeads,
                        'change' => $leadsChange['formatted'],
                        'is_positive' => $leadsChange['is_positive'],
                    ],
                    'open_deals' => [
                        'value' => $openDeals,
                        'change' => $openDealsChange['formatted'],
                        'is_positive' => $openDealsChange['is_positive'],
                    ],
                    'won_deals' => [
                        'value' => $wonDeals,
                        'change' => $wonDealsChange['formatted'],
                        'is_positive' => $wonDealsChange['is_positive'],
                    ],
                    'revenue' => [
                        'value' => $revenue,
                        'change' => $revenueChange['formatted'],
                        'is_positive' => $revenueChange['is_positive'],
                    ],
                    'conversion_rate' => [
                        'value' => $conversionRate,
                        'label' => 'Lead to Win rate',
                    ],
                    'pending_tasks' => [
                        'value' => $pendingTasks,
                        'overdue' => $overdueTasks,
                    ],
                ],
                'leads_overview' => $leadsOverview,
                'deals_by_stage' => $dealsByStage,
                'revenue_trend' => $revenueTrend,
                'sales_performance' => ($request->user() && $request->user()->isAdmin()) ? $salesPerformance : [],
                'recent_activities' => $activities,
            ],
        ]);
    }

    /**
     * Resolve date ranges for filtering and period comparisons.
     */
    private function resolveDateRanges(string $range, ?string $customStart, ?string $customEnd): array
    {
        $now = Carbon::now();

        if ($customStart && $customEnd) {
            $start = Carbon::parse($customStart)->startOfDay();
            $end = Carbon::parse($customEnd)->endOfDay();
            $diffDays = $end->diffInDays($start);
            $prevStart = (clone $start)->subDays($diffDays + 1);
            $prevEnd = (clone $start)->subSecond();
            $label = "{$start->format('M d, Y')} - {$end->format('M d, Y')}";

            return [$start, $end, $prevStart, $prevEnd, $label];
        }

        switch ($range) {
            case 'today':
                $start = (clone $now)->startOfDay();
                $end = (clone $now)->endOfDay();
                $prevStart = (clone $start)->subDay();
                $prevEnd = (clone $end)->subDay();
                $label = 'Today (' . $now->format('M d, Y') . ')';
                break;

            case 'this_week':
                $start = (clone $now)->startOfWeek();
                $end = (clone $now)->endOfWeek();
                $prevStart = (clone $start)->subWeek();
                $prevEnd = (clone $end)->subWeek();
                $label = 'This Week (' . $start->format('M d') . ' - ' . $end->format('M d') . ')';
                break;

            case 'last_30_days':
                $start = (clone $now)->subDays(30)->startOfDay();
                $end = (clone $now)->endOfDay();
                $prevStart = (clone $start)->subDays(30);
                $prevEnd = (clone $start)->subSecond();
                $label = 'Last 30 Days';
                break;

            case 'this_quarter':
                $start = (clone $now)->startOfQuarter();
                $end = (clone $now)->endOfQuarter();
                $prevStart = (clone $start)->subQuarter();
                $prevEnd = (clone $end)->subQuarter();
                $label = 'This Quarter (' . $start->format('M') . ' - ' . $end->format('M Y') . ')';
                break;

            case 'this_year':
                $start = (clone $now)->startOfYear();
                $end = (clone $now)->endOfYear();
                $prevStart = (clone $start)->subYear();
                $prevEnd = (clone $end)->subYear();
                $label = 'This Year (' . $now->format('Y') . ')';
                break;

            case 'all':
                $start = null;
                $end = null;
                $prevStart = null;
                $prevEnd = null;
                $label = 'All Time';
                break;

            case 'this_month':
            default:
                $start = (clone $now)->startOfMonth();
                $end = (clone $now)->endOfMonth();
                $prevStart = (clone $start)->subMonth();
                $prevEnd = (clone $end)->subMonth();
                $label = 'This Month (' . $now->format('F Y') . ')';
                break;
        }

        return [$start, $end, $prevStart, $prevEnd, $label];
    }

    /**
     * Calculate percentage change with formatted badge indicator.
     */
    private function calculatePercentageChange(float|int $current, float|int $previous): array
    {
        if ($previous == 0) {
            $change = $current > 0 ? 100 : 0;
        } else {
            $change = round((($current - $previous) / $previous) * 100, 1);
        }

        $isPositive = $change >= 0;
        $sign = $isPositive ? '+' : '';
        $formatted = "{$sign}{$change}%";

        return [
            'change' => $change,
            'is_positive' => $isPositive,
            'formatted' => $formatted,
        ];
    }

    /**
     * Build real timeline data for Leads Overview chart.
     */
    private function buildLeadsTimeline(?Carbon $startDate, ?Carbon $endDate): array
    {
        $labels = [];
        $newLeads = [];
        $convertedLeads = [];

        // Generate 6 intervals based on current time or range
        $end = $endDate ?? Carbon::now();
        $start = $startDate ?? (clone $end)->subDays(35);
        $totalDays = max(1, $end->diffInDays($start));
        $intervalDays = max(1, (int) round($totalDays / 5));

        for ($i = 5; $i >= 0; $i--) {
            $pointEnd = (clone $end)->subDays($i * $intervalDays);
            $pointStart = (clone $pointEnd)->subDays($intervalDays);

            $label = $pointEnd->format('M d');
            $labels[] = $label;

            $newCount = Lead::whereBetween('created_at', [$pointStart, $pointEnd])->count();
            $convCount = Lead::whereHas('status', function ($q) {
                $q->where(DB::raw('LOWER(name)'), 'like', '%convert%')
                  ->orWhere(DB::raw('LOWER(name)'), 'like', '%qualif%')
                  ->orWhere(DB::raw('LOWER(name)'), 'like', '%won%');
            })->whereBetween('created_at', [$pointStart, $pointEnd])->count();

            $newLeads[] = $newCount;
            $convertedLeads[] = $convCount;
        }

        return [
            'labels' => $labels,
            'new_leads' => $newLeads,
            'converted_leads' => $convertedLeads,
        ];
    }

    /**
     * Build real monthly revenue trend for the last 6 months.
     */
    private function buildRevenueTrend(): array
    {
        $labels = [];
        $values = [];
        $dealCounts = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = (clone $month)->startOfMonth();
            $monthEnd = (clone $month)->endOfMonth();

            $labels[] = $month->format('M Y');

            $monthWonDeals = Deal::where(function ($q) {
                $q->where('status', 'won')->orWhereHas('stage', function ($sq) {
                    $sq->where(DB::raw('LOWER(name)'), 'won');
                });
            })->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('close_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('updated_at', [$monthStart, $monthEnd]);
            });

            $values[] = (float) (clone $monthWonDeals)->sum('value');
            $dealCounts[] = (clone $monthWonDeals)->count();
        }

        return [
            'labels' => $labels,
            'revenue' => $values,
            'won_deals' => $dealCounts,
        ];
    }
}
