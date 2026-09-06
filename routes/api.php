<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/leads', [LeadController::class, 'store']); // Public lead capture form

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Dashboard Module
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Reports & Analytics Module
    Route::get('/reports', [ReportController::class, 'index']);

    // Leads Module
    Route::get('/leads', [LeadController::class, 'index']);
    Route::put('/leads/{lead}', [LeadController::class, 'update']);
    Route::delete('/leads/bulk-delete', [LeadController::class, 'bulkDelete']);
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);

    // Deals Module
    Route::apiResource('deals', DealController::class);

    // Tasks Module
    Route::apiResource('tasks', TaskController::class);

    // Users List for Assignment
    Route::get('/users', function () {
        return response()->json([
            'success' => true,
            'data' => \App\Models\User::all(['id', 'name', 'email'])
        ]);
    });

    // Related Records for Task association
    Route::get('/related-options', function () {
        // Auto-seed companies from leads or defaults if empty
        if (\App\Models\Company::count() === 0) {
            $leadCompanies = \App\Models\Lead::whereNotNull('company_name')
                ->where('company_name', '!=', '')
                ->distinct()
                ->pluck('company_name');
            
            if ($leadCompanies->isEmpty()) {
                $leadCompanies = collect(['Acme Corporation', 'Globex Industries', 'Initech Systems', 'Umbrella Tech', 'Stark Enterprises']);
            }

            foreach ($leadCompanies->take(15) as $cName) {
                \App\Models\Company::firstOrCreate(['name' => $cName], [
                    'industry' => 'Technology',
                    'email' => strtolower(str_replace(' ', '', $cName)) . '@example.com',
                ]);
            }
        }

        $leads = \App\Models\Lead::latest()->take(50)->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'name' => trim(($l->first_name ?? '') . ' ' . ($l->last_name ?? '')) ?: ($l->company ?: 'Lead #' . $l->id),
                'company_name' => $l->company ?? $l->company_name ?? '',
            ];
        });

        $deals = \App\Models\Deal::latest()->take(50)->get()->map(function ($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
                'title' => $d->name,
                'value' => $d->value,
            ];
        });

        $companies = \App\Models\Company::latest()->take(50)->get()->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
            ];
        });

        return response()->json([
            'success' => true,
            'leads' => $leads,
            'deals' => $deals,
            'companies' => $companies,
        ]);
    });
});

