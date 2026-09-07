<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $leads = Lead::take(5)->get();
        $deals = Deal::take(5)->get();

        foreach ($leads as $lead) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'created',
                'auditable_type' => Lead::class,
                'auditable_id' => $lead->id,
                'old_values' => null,
                'new_values' => ['name' => $lead->name, 'email' => $lead->email, 'score' => $lead->score],
                'url' => 'http://127.0.0.1:8000/leads',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subDays(rand(5, 30)),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'updated',
                'auditable_type' => Lead::class,
                'auditable_id' => $lead->id,
                'old_values' => ['score' => 20],
                'new_values' => ['score' => $lead->score],
                'url' => 'http://127.0.0.1:8000/leads/' . $lead->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subDays(rand(1, 4)),
            ]);
        }

        foreach ($deals as $deal) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'updated',
                'auditable_type' => Deal::class,
                'auditable_id' => $deal->id,
                'old_values' => ['status' => 'open'],
                'new_values' => ['status' => $deal->status],
                'url' => 'http://127.0.0.1:8000/deals/' . $deal->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subDays(rand(1, 10)),
            ]);
        }
    }
}
