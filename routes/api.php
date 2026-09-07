<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarEventController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CustomFieldController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\DealStageController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication & Lead Capture Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/leads', [LeadController::class, 'store']); // Public lead capture form

// Protected Authenticated CRM API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Current User Profile & Logout
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => new UserResource($request->user()->load(['roles', 'permissions'])),
        ]);
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Analytics & Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/reports', [ReportController::class, 'index']);

    // 1. Leads Module
    Route::get('/leads/metadata', [LeadController::class, 'metadata']);
    Route::delete('/leads/bulk-delete', [LeadController::class, 'bulkDelete']);
    Route::apiResource('leads', LeadController::class)->except(['store']);
    Route::post('/leads/auth-store', [LeadController::class, 'store']); // internal store

    // 2. Contacts Module
    Route::get('/contacts/metadata', [ContactController::class, 'metadata']);
    Route::delete('/contacts/bulk-delete', [ContactController::class, 'bulkDelete']);
    Route::apiResource('contacts', ContactController::class);

    // 3. Deals Module
    Route::get('/deals/metadata', [DealController::class, 'metadata']);
    Route::delete('/deals/bulk-delete', [DealController::class, 'bulkDelete']);
    Route::patch('/deals/{deal}/stage', [DealController::class, 'updateStage']);
    Route::apiResource('deals', DealController::class);

    // 4. Tasks Module
    Route::get('/tasks/metadata', [TaskController::class, 'metadata']);
    Route::delete('/tasks/bulk-delete', [TaskController::class, 'bulkDelete']);
    Route::post('/tasks/bulk-complete', [TaskController::class, 'bulkComplete']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::apiResource('tasks', TaskController::class);

    // 5. Calendar Events Module
    Route::get('/calendar-events/metadata', [CalendarEventController::class, 'metadata']);
    Route::delete('/calendar-events/bulk-delete', [CalendarEventController::class, 'bulkDelete']);
    Route::patch('/calendar-events/{calendar_event}/reschedule', [CalendarEventController::class, 'reschedule']);
    Route::apiResource('calendar-events', CalendarEventController::class);

    // 6. Users & Roles & Permissions
    Route::get('/permissions', [RoleController::class, 'permissions']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);

    // 7. Pipelines & Custom Fields
    Route::post('/deal-stages/reorder', [DealStageController::class, 'reorder']);
    Route::apiResource('deal-stages', DealStageController::class);
    Route::apiResource('custom-fields', CustomFieldController::class);

    // 8. Notifications Module
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // 9. Activities Module
    Route::apiResource('activities', ActivityController::class);

    // 10. Settings Module
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'store']);

    // Related records for frontend autocomplete / task / deal association
    Route::get('/related-options', function () {
        $leads = Lead::latest()->take(50)->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'name' => $l->name ?: ($l->company ?: 'Lead #' . $l->id),
                'company_name' => $l->company_name ?? '',
            ];
        });

        $deals = Deal::latest()->take(50)->get()->map(function ($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
                'title' => $d->name,
                'value' => $d->value,
            ];
        });

        $companies = Company::latest()->take(50)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
            ];
        });

        $contacts = \App\Models\Contact::latest()->take(50)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name ?: trim("{$c->first_name} {$c->last_name}"),
            ];
        });

        return response()->json([
            'success' => true,
            'leads' => $leads,
            'deals' => $deals,
            'companies' => $companies,
            'contacts' => $contacts,
        ]);
    });
});
