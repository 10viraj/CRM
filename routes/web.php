<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DealController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    
    // Users Management Module
    Route::resource('/users', \App\Http\Controllers\UserController::class);

    // Leads Module
    Route::delete('/leads/bulk-delete', [App\Http\Controllers\LeadController::class, 'bulkDelete'])->name('leads.bulkDelete');
    Route::resource('/leads', App\Http\Controllers\LeadController::class);

    // Deals Module
    Route::patch('/deals/{deal}/stage', [App\Http\Controllers\DealController::class, 'updateStage'])->name('deals.updateStage');
    Route::resource('/deals', App\Http\Controllers\DealController::class);

    // Products Module
    Route::resource('/products', App\Http\Controllers\ProductController::class);

    // Quotations Module
    Route::resource('/quotations', App\Http\Controllers\QuotationController::class);
    
    // Invoices Module
    Route::resource('/invoices', App\Http\Controllers\InvoiceController::class);
    
    // Payments Module
    Route::post('/invoices/{invoice}/payments', [App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments', [App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
    
    // Reports Module
    Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    
    // Settings Module
    Route::get('/settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\SettingController::class, 'store'])->name('settings.store');
    
    // Global Modules
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
    Route::get('/activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/attachments', [App\Http\Controllers\AttachmentController::class, 'index'])->name('attachments.index');
    Route::delete('/attachments/{attachment}', [App\Http\Controllers\AttachmentController::class, 'destroy'])->name('attachments.destroy');
    Route::get('/attachments/{attachment}/download', [App\Http\Controllers\AttachmentController::class, 'download'])->name('attachments.download');
    
    // Companies Module
    Route::resource('/companies', CompanyController::class);
    
    // Catch-all for all other placeholder modules
    Route::get('/module/{name}', function($name) {
        $title = ucwords(str_replace('-', ' ', $name));
        return view('placeholder', ['title' => $title]);
    })->name('module.placeholder');
});
