<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'User' => \App\Models\User::class,
            'Company' => \App\Models\Company::class,
            'Lead' => \App\Models\Lead::class,
            'Contact' => \App\Models\Contact::class,
            'Deal' => \App\Models\Deal::class,
            'DealStage' => \App\Models\DealStage::class,
            'Task' => \App\Models\Task::class,
            'CalendarEvent' => \App\Models\CalendarEvent::class,
            'Activity' => \App\Models\Activity::class,
            'Quotation' => \App\Models\Quotation::class,
            'Invoice' => \App\Models\Invoice::class,
            'Payment' => \App\Models\Payment::class,
            'Setting' => \App\Models\Setting::class,
            'AuditLog' => \App\Models\AuditLog::class,
            'CustomField' => \App\Models\CustomField::class,
            'CustomFieldValue' => \App\Models\CustomFieldValue::class,
        ]);
    }
}
