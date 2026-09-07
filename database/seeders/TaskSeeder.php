<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $leads = Lead::take(10)->get();
        $deals = Deal::take(10)->get();

        $tasks = [
            ['title' => 'Send introductory email and product deck', 'type' => 'Email', 'priority' => 'High', 'status' => 'Pending'],
            ['title' => 'Schedule discovery call with CTO', 'type' => 'Call', 'priority' => 'Urgent', 'status' => 'Pending'],
            ['title' => 'Prepare custom proposal & ROI calculation', 'type' => 'Review', 'priority' => 'High', 'status' => 'In Progress'],
            ['title' => 'Follow up on legal agreement comments', 'type' => 'Follow-up', 'priority' => 'Medium', 'status' => 'Pending'],
            ['title' => 'Executive review meeting prep', 'type' => 'Meeting', 'priority' => 'Low', 'status' => 'Completed'],
        ];

        foreach ($leads as $i => $lead) {
            $t = $tasks[$i % count($tasks)];
            Task::create([
                'title' => $t['title'] . ' (' . $lead->name . ')',
                'description' => 'Follow up action required for lead ' . $lead->email,
                'type' => $t['type'],
                'priority' => $t['priority'],
                'status' => $t['status'],
                'due_date' => now()->addDays(rand(1, 14)),
                'assign_to_id' => $user->id,
                'creator_id' => $user->id,
                'related_to_type' => Lead::class,
                'related_to_id' => $lead->id,
                'completed_at' => $t['status'] === 'Completed' ? now()->subDay() : null,
            ]);
        }

        foreach ($deals as $i => $deal) {
            $t = $tasks[($i + 2) % count($tasks)];
            Task::create([
                'title' => $t['title'] . ' for deal [' . $deal->name . ']',
                'description' => 'Deal progression task.',
                'type' => $t['type'],
                'priority' => $t['priority'],
                'status' => $t['status'],
                'due_date' => now()->addDays(rand(1, 10)),
                'assign_to_id' => $user->id,
                'creator_id' => $user->id,
                'related_to_type' => Deal::class,
                'related_to_id' => $deal->id,
                'completed_at' => $t['status'] === 'Completed' ? now()->subDay() : null,
            ]);
        }
    }
}
