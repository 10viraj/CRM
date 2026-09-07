<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class CalendarEventSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $leads = Lead::take(5)->get();
        $deals = Deal::take(5)->get();
        $companies = Company::take(5)->get();

        $events = [
            [
                'title' => 'Product Demo with Acme Executive Team',
                'description' => 'Walkthrough of CRM enterprise features and integration points.',
                'event_type' => 'demo',
                'location' => 'Zoom Meeting',
                'eventable' => $deals->first(),
            ],
            [
                'title' => 'Contract Review & Negotiation Call',
                'description' => 'Finalize SLA and payment terms.',
                'event_type' => 'call',
                'location' => 'Phone',
                'eventable' => $leads->first(),
            ],
            [
                'title' => 'Quarterly Strategic Alignment',
                'description' => 'Review Q3 goals and software roadmap with key partner.',
                'event_type' => 'meeting',
                'location' => 'Main Boardroom',
                'eventable' => $companies->first(),
            ],
            [
                'title' => 'Follow-up on Proposal Submission',
                'description' => 'Review questions on pricing tier 3.',
                'event_type' => 'call',
                'location' => 'Google Meet',
                'eventable' => $leads->last(),
            ],
            [
                'title' => 'Annual CRM Onboarding Webinar',
                'description' => 'Live training for customer sales team.',
                'event_type' => 'webinar',
                'location' => 'Webinar Stage',
                'eventable' => null,
            ],
        ];

        foreach ($events as $index => $event) {
            $startTime = now()->addDays($index * 2)->setTime(10 + $index, 0);
            $endTime = (clone $startTime)->addHour();

            CalendarEvent::create([
                'user_id' => $user->id,
                'title' => $event['title'],
                'description' => $event['description'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_all_day' => false,
                'location' => $event['location'],
                'event_type' => $event['event_type'],
                'status' => 'scheduled',
                'eventable_type' => $event['eventable'] ? get_class($event['eventable']) : null,
                'eventable_id' => $event['eventable'] ? $event['eventable']->id : null,
            ]);
        }
    }
}
