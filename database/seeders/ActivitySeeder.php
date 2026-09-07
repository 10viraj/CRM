<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $leads = Lead::take(20)->get();
        $deals = Deal::take(20)->get();
        $companies = Company::take(10)->get();
        $contacts = Contact::take(10)->get();

        $activityTypes = [
            ['type' => 'call', 'title' => 'Discovery Phone Call', 'desc' => 'Discussed enterprise requirements and timeline.'],
            ['type' => 'email', 'title' => 'Sent Proposal Document', 'desc' => 'Emailed quotation and specification PDF.'],
            ['type' => 'meeting', 'title' => 'Demo Presentation', 'desc' => 'Presented live product demo to stakeholders.'],
            ['type' => 'note', 'title' => 'Executive Meeting Notes', 'desc' => 'Customer requested custom webhook integrations.'],
            ['type' => 'status_change', 'title' => 'Stage Advanced', 'desc' => 'Deal moved forward into Proposal stage.'],
        ];

        // Seed activities for Leads
        foreach ($leads as $lead) {
            $act = $activityTypes[array_rand($activityTypes)];
            Activity::create([
                'user_id' => $user->id,
                'type' => $act['type'],
                'title' => $act['title'] . ' with ' . $lead->name,
                'description' => $act['desc'],
                'activity_date' => now()->subDays(rand(1, 20)),
                'duration_minutes' => rand(15, 60),
                'status' => 'completed',
                'subject_type' => Lead::class,
                'subject_id' => $lead->id,
            ]);
        }

        // Seed activities for Deals
        foreach ($deals as $deal) {
            $act = $activityTypes[array_rand($activityTypes)];
            Activity::create([
                'user_id' => $user->id,
                'type' => $act['type'],
                'title' => $act['title'] . ' for ' . $deal->name,
                'description' => $act['desc'],
                'activity_date' => now()->subDays(rand(1, 15)),
                'duration_minutes' => rand(15, 45),
                'status' => 'completed',
                'subject_type' => Deal::class,
                'subject_id' => $deal->id,
            ]);
        }

        // Seed activities for Contacts
        foreach ($contacts as $contact) {
            Activity::create([
                'user_id' => $user->id,
                'type' => 'call',
                'title' => 'Quarterly Check-in Call with ' . $contact->name,
                'description' => 'Checked customer satisfaction and onboarding feedback.',
                'activity_date' => now()->subDays(rand(1, 10)),
                'duration_minutes' => 20,
                'status' => 'completed',
                'subject_type' => Contact::class,
                'subject_id' => $contact->id,
            ]);
        }
    }
}
