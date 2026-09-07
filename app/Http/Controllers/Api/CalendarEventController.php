<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarEventRequest;
use App\Http\Resources\CalendarEventResource;
use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarEventController extends Controller
{
    /**
     * Display a listing of calendar events.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CalendarEvent::with(['user', 'eventable']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filtering
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($type = $request->input('event_type')) {
            $query->where('event_type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($startDate = $request->input('start_date')) {
            $query->whereDate('start_time', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('end_time', '<=', $endDate);
        }

        if ($eventableType = $request->input('eventable_type')) {
            $query->where('eventable_type', $eventableType);
        }

        if ($eventableId = $request->input('eventable_id')) {
            $query->where('eventable_id', $eventableId);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'start_time');
        $sortDirection = strtolower($request->input('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['id', 'title', 'start_time', 'end_time', 'event_type', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('start_time', 'asc');
        }

        // Pagination or full list (for calendar views)
        if ($request->boolean('all') || $request->input('format') === 'calendar') {
            $events = $query->get();
            return response()->json([
                'success' => true,
                'data' => CalendarEventResource::collection($events),
            ]);
        }

        $perPage = (int) $request->input('per_page', 15);
        $perPage = min(max($perPage, 1), 100);
        $events = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CalendarEventResource::collection($events),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    /**
     * Store a newly created calendar event.
     */
    public function store(CalendarEventRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (empty($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $event = CalendarEvent::create($data);
        $event->load(['user', 'eventable']);

        return response()->json([
            'success' => true,
            'message' => 'Calendar event created successfully',
            'data' => new CalendarEventResource($event),
        ], 201);
    }

    /**
     * Display the specified calendar event.
     */
    public function show(CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->load(['user', 'eventable']);

        return response()->json([
            'success' => true,
            'data' => new CalendarEventResource($calendarEvent),
        ]);
    }

    /**
     * Update the specified calendar event.
     */
    public function update(CalendarEventRequest $request, CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->update($request->validated());
        $calendarEvent->load(['user', 'eventable']);

        return response()->json([
            'success' => true,
            'message' => 'Calendar event updated successfully',
            'data' => new CalendarEventResource($calendarEvent),
        ]);
    }

    /**
     * Reschedule the specified calendar event (quick start/end update).
     */
    public function reschedule(Request $request, CalendarEvent $calendarEvent): JsonResponse
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
        ]);

        $calendarEvent->update([
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
        ]);

        $calendarEvent->load(['user', 'eventable']);

        return response()->json([
            'success' => true,
            'message' => 'Event rescheduled successfully',
            'data' => new CalendarEventResource($calendarEvent),
        ]);
    }

    /**
     * Remove the specified calendar event.
     */
    public function destroy(CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->delete();

        return response()->json([
            'success' => true,
            'message' => 'Calendar event deleted successfully',
        ]);
    }

    /**
     * Bulk delete calendar events.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No events provided for deletion.',
            ], 422);
        }

        $count = CalendarEvent::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$count} events.",
        ]);
    }

    /**
     * Get calendar metadata & options for dropdowns.
     */
    public function metadata(): JsonResponse
    {
        $users = \App\Models\User::select('id', 'name', 'email')->orderBy('name')->get();
        $leads = \App\Models\Lead::latest()->take(50)->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'name' => trim("{$l->first_name} {$l->last_name}") ?: ($l->company_name ?: "Lead #{$l->id}"),
            ];
        });
        $contacts = \App\Models\Contact::latest()->take(50)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => trim("{$c->first_name} {$c->last_name}") ?: "Contact #{$c->id}",
            ];
        });
        $deals = \App\Models\Deal::latest()->take(50)->get()->map(function ($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
            ];
        });
        $tasks = \App\Models\Task::latest()->take(50)->get()->map(function ($t) {
            return [
                'id' => $t->id,
                'title' => $t->title,
            ];
        });

        $totalEvents = CalendarEvent::count();
        $upcomingEvents = CalendarEvent::where('start_time', '>=', now())->count();
        $todayEvents = CalendarEvent::whereDate('start_time', now()->toDateString())->count();

        return response()->json([
            'success' => true,
            'stats' => [
                'total_events' => $totalEvents,
                'upcoming_events' => $upcomingEvents,
                'today_events' => $todayEvents,
            ],
            'options' => [
                'users' => $users,
                'leads' => $leads,
                'contacts' => $contacts,
                'deals' => $deals,
                'tasks' => $tasks,
            ],
        ]);
    }
}
