<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

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
            'Company' => \App\Models\Company::class,
            'Lead' => \App\Models\Lead::class,
            'Deal' => \App\Models\Deal::class,
            'App\\Models\\Company' => \App\Models\Company::class,
            'App\\Models\\Lead' => \App\Models\Lead::class,
            'App\\Models\\Deal' => \App\Models\Deal::class,
        ]);
    }
}

