<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $notifications = [
            [
                'title' => 'New High-Score Lead Assigned',
                'message' => 'Lead "Acme Corp CTO" has been assigned to you with score 95.',
                'link' => '/leads',
                'type' => 'App\Notifications\LeadAssignedNotification',
                'read' => false,
            ],
            [
                'title' => 'Deal Stage Moved to Won',
                'message' => 'Deal "Enterprise Cloud License" was closed won ($125,000).',
                'link' => '/deals',
                'type' => 'App\Notifications\DealWonNotification',
                'read' => true,
            ],
            [
                'title' => 'Task Due Today',
                'message' => 'Task "Follow up on quotation approval" is due by 5:00 PM.',
                'link' => '/tasks',
                'type' => 'App\Notifications\TaskReminderNotification',
                'read' => false,
            ],
            [
                'title' => 'Upcoming Calendar Event',
                'message' => 'Meeting "Product Demo with Acme" starts in 30 minutes.',
                'link' => '/calendar',
                'type' => 'App\Notifications\EventReminderNotification',
                'read' => false,
            ],
        ];

        foreach ($notifications as $notif) {
            Notification::create([
                'id' => (string) Str::uuid(),
                'type' => $notif['type'],
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => $notif['title'],
                    'message' => $notif['message'],
                    'link' => $notif['link'],
                ],
                'read_at' => $notif['read'] ? now()->subHours(rand(1, 24)) : null,
                'created_at' => now()->subHours(rand(1, 48)),
                'updated_at' => now(),
            ]);
        }
    }
}
