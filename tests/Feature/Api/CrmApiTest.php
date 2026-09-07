<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\CalendarEvent;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrmApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $this->adminUser = User::factory()->create([
            'email' => 'admin@testcrm.com',
        ]);
        $this->adminUser->assignRole($role);
    }

    /* =========================================================================
     | Authentication & Unauthorized Checks
     | ========================================================================= */

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/leads');
        $response->assertStatus(401);
    }

    public function test_login_returns_token_and_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@crm.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@crm.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    /* =========================================================================
     | 1. Leads CRUD, Search, Filter, Pagination, Validation
     | ========================================================================= */

    public function test_leads_crud_and_filtering(): void
    {
        Sanctum::actingAs($this->adminUser);

        $status1 = LeadStatus::create(['name' => 'New', 'color' => 'blue']);
        $status2 = LeadStatus::create(['name' => 'Won', 'color' => 'green']);
        $source = LeadSource::create(['name' => 'Website']);

        $lead1 = Lead::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Wonderland',
            'email' => 'alice@wonderland.com',
            'lead_status_id' => $status1->id,
            'lead_source_id' => $source->id,
            'score' => 85,
        ]);

        $lead2 = Lead::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Builder',
            'email' => 'bob@builder.com',
            'lead_status_id' => $status2->id,
            'lead_source_id' => $source->id,
            'score' => 40,
        ]);

        // List & Pagination
        $listResponse = $this->getJson('/api/leads?per_page=1');
        $listResponse->assertStatus(200)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 1);

        // Search
        $searchResponse = $this->getJson('/api/leads?search=Alice');
        $searchResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.first_name', 'Alice');

        // Filter by Status
        $filterResponse = $this->getJson("/api/leads?status_id={$status2->id}");
        $filterResponse->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'bob@builder.com');

        // Store (Create)
        $storeResponse = $this->postJson('/api/leads', [
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'email' => 'charlie@peanuts.com',
            'company' => 'Peanuts Inc',
            'score' => 70,
        ]);
        $storeResponse->assertStatus(201)
            ->assertJsonPath('data.email', 'charlie@peanuts.com');
        $this->assertDatabaseHas('leads', ['email' => 'charlie@peanuts.com']);

        // Validation Error (422)
        $invalidResponse = $this->postJson('/api/leads', [
            'first_name' => '',
            'email' => 'not-an-email',
        ]);
        $invalidResponse->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);

        // Show
        $showResponse = $this->getJson("/api/leads/{$lead1->id}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.id', $lead1->id);

        // Update
        $updateResponse = $this->putJson("/api/leads/{$lead1->id}", [
            'first_name' => 'Alice Updated',
            'last_name' => 'Wonderland',
            'email' => 'alice.updated@wonderland.com',
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Alice Updated');
        $this->assertDatabaseHas('leads', ['email' => 'alice.updated@wonderland.com']);

        // Delete
        $deleteResponse = $this->deleteJson("/api/leads/{$lead1->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('leads', ['id' => $lead1->id]);

        // Bulk Delete
        $bulkDeleteResponse = $this->deleteJson('/api/leads/bulk-delete', [
            'ids' => [$lead2->id],
        ]);
        $bulkDeleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('leads', ['id' => $lead2->id]);
    }

    /* =========================================================================
     | 2. Contacts CRUD, Search, Filter
     | ========================================================================= */

    public function test_contacts_crud_and_filtering(): void
    {
        Sanctum::actingAs($this->adminUser);

        $company = Company::factory()->create(['name' => 'Stark Tech']);

        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'email' => 'tony@stark.com',
            'job_title' => 'CEO',
            'is_primary' => true,
        ]);

        // Index
        $response = $this->getJson('/api/contacts?search=Tony');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Tony Stark')
            ->assertJsonPath('data.0.company.name', 'Stark Tech');

        // Store
        $createResponse = $this->postJson('/api/contacts', [
            'company_id' => $company->id,
            'first_name' => 'Pepper',
            'last_name' => 'Potts',
            'email' => 'pepper@stark.com',
            'job_title' => 'COO',
            'is_primary' => false,
        ]);
        $createResponse->assertStatus(201)
            ->assertJsonPath('data.first_name', 'Pepper');

        // Show
        $showResponse = $this->getJson("/api/contacts/{$contact->id}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.email', 'tony@stark.com');

        // Update
        $updateResponse = $this->putJson("/api/contacts/{$contact->id}", [
            'first_name' => 'Tony',
            'last_name' => 'Stark',
            'job_title' => 'Chief Executive',
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.job_title', 'Chief Executive');

        // Delete
        $deleteResponse = $this->deleteJson("/api/contacts/{$contact->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    /* =========================================================================
     | 3. Deals CRUD, Stage Transition, Filtering
     | ========================================================================= */

    public function test_deals_crud_and_stage_update(): void
    {
        Sanctum::actingAs($this->adminUser);

        $stageNew = DealStage::factory()->create(['name' => 'New', 'order_index' => 1]);
        $stageWon = DealStage::factory()->create(['name' => 'Won', 'order_index' => 5]);

        $deal = Deal::factory()->create([
            'name' => 'Cloud Migration Contract',
            'value' => 50000,
            'deal_stage_id' => $stageNew->id,
            'status' => 'open',
        ]);

        // Index & Filter
        $response = $this->getJson("/api/deals?stage_id={$stageNew->id}");
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.value', 50000);

        // Store
        $createResponse = $this->postJson('/api/deals', [
            'name' => 'AI Integration Suite',
            'value' => 120000,
            'deal_stage_id' => $stageNew->id,
            'probability' => 70,
            'status' => 'open',
        ]);
        $createResponse->assertStatus(201)
            ->assertJsonPath('data.name', 'AI Integration Suite');
        $createdDealId = $createResponse->json('data.id');

        // Test metadata endpoint
        $metaResponse = $this->getJson('/api/deals/metadata');
        $metaResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'stats' => ['total_deals', 'total_pipeline_value', 'won_revenue', 'win_rate'],
                'stages',
                'options' => ['companies', 'contacts', 'leads', 'owners']
            ]);

        // Update Stage from New/Proposal to Won
        $stageResponse = $this->patchJson("/api/deals/{$deal->id}/stage", [
            'deal_stage_id' => $stageWon->id,
        ]);
        $stageResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'won')
            ->assertJsonPath('data.stage.name', 'Won');

        // Assert database updated and activity logged
        $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'deal_stage_id' => $stageWon->id,
            'status' => 'won',
        ]);
        $this->assertDatabaseHas('activities', [
            'subject_type' => 'Deal',
            'subject_id' => $deal->id,
            'type' => 'Stage Changed',
        ]);

        // Bulk delete
        $bulkResponse = $this->deleteJson('/api/deals/bulk-delete', [
            'ids' => [$deal->id, $createdDealId],
        ]);
        $bulkResponse->assertStatus(200);
        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
        $this->assertDatabaseMissing('deals', ['id' => $createdDealId]);
    }

    /* =========================================================================
     | 4. Tasks CRUD & Quick Status Update
     | ========================================================================= */

    public function test_tasks_crud_and_status_update(): void
    {
        Sanctum::actingAs($this->adminUser);

        $task = Task::factory()->create([
            'title' => 'Prepare client SLA contract',
            'priority' => 'Urgent',
            'status' => 'Pending',
        ]);

        // Index with status=All & filter checks
        $allResponse = $this->getJson('/api/tasks?status=All');
        $allResponse->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Store
        $createResponse = $this->postJson('/api/tasks', [
            'title' => 'Conduct quarterly product demo',
            'priority' => 'High',
            'type' => 'Meeting',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
        ]);
        $createResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'Conduct quarterly product demo');

        // Update Status
        $statusResponse = $this->patchJson("/api/tasks/{$task->id}/status", [
            'status' => 'Completed',
        ]);
        $statusResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'Completed');
        $this->assertNotNull($statusResponse->json('data.completed_at'));

        // Tasks metadata
        $metaResponse = $this->getJson('/api/tasks/metadata');
        $metaResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'stats' => ['total', 'pending', 'in_progress', 'completed', 'overdue'],
                'options' => ['users', 'leads', 'contacts', 'deals']
            ]);

        // Tasks bulk complete & bulk delete
        $task2 = Task::factory()->create(['status' => 'Pending']);
        $bulkCompleteRes = $this->postJson('/api/tasks/bulk-complete', ['ids' => [$task2->id]]);
        $bulkCompleteRes->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['id' => $task2->id, 'status' => 'Completed']);

        $bulkDeleteRes = $this->deleteJson('/api/tasks/bulk-delete', ['ids' => [$task2->id]]);
        $bulkDeleteRes->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);

        // Delete
        $deleteResponse = $this->deleteJson("/api/tasks/{$task->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /* =========================================================================
     | 5. Calendar Events CRUD & Filtering
     | ========================================================================= */

    public function test_calendar_events_crud(): void
    {
        Sanctum::actingAs($this->adminUser);

        $event = CalendarEvent::factory()->create([
            'user_id' => $this->adminUser->id,
            'title' => 'Executive Steering Committee',
            'start_time' => now()->addDay()->setTime(14, 0),
            'end_time' => now()->addDay()->setTime(15, 0),
            'event_type' => 'meeting',
            'status' => 'scheduled',
        ]);

        // Index
        $response = $this->getJson('/api/calendar-events?event_type=meeting');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Executive Steering Committee');

        // Calendar metadata
        $metaResponse = $this->getJson('/api/calendar-events/metadata');
        $metaResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'stats' => ['total_events', 'upcoming_events', 'today_events'],
                'options' => ['users', 'leads', 'contacts', 'deals', 'tasks']
            ]);

        // Store
        $createResponse = $this->postJson('/api/calendar-events', [
            'title' => 'Technical Architecture Review',
            'start_time' => now()->addDays(2)->toISOString(),
            'end_time' => now()->addDays(2)->addHour()->toISOString(),
            'event_type' => 'demo',
            'location' => 'Virtual Room 4',
        ]);
        $createResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'Technical Architecture Review');

        // Reschedule
        $newStart = now()->addDays(4)->setTime(11, 0)->toISOString();
        $newEnd = now()->addDays(4)->setTime(12, 0)->toISOString();
        $rescheduleRes = $this->patchJson("/api/calendar-events/{$event->id}/reschedule", [
            'start_time' => $newStart,
            'end_time' => $newEnd,
        ]);
        $rescheduleRes->assertStatus(200);

        // Update
        $updateResponse = $this->putJson("/api/calendar-events/{$event->id}", [
            'title' => 'Executive Steering Committee - Rescheduled',
            'start_time' => now()->addDays(3)->setTime(10, 0)->toISOString(),
            'end_time' => now()->addDays(3)->setTime(11, 0)->toISOString(),
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.title', 'Executive Steering Committee - Rescheduled');

        // Delete
        $deleteResponse = $this->deleteJson("/api/calendar-events/{$event->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('calendar_events', ['id' => $event->id]);
    }

    /* =========================================================================
     | 6. Users & Roles Management
     | ========================================================================= */

    public function test_users_and_roles_api(): void
    {
        Sanctum::actingAs($this->adminUser);

        $repRole = Role::firstOrCreate(['name' => 'Sales Representative', 'guard_name' => 'web']);

        // Roles list
        $rolesResponse = $this->getJson('/api/roles');
        $rolesResponse->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);

        // Create User
        $createResponse = $this->postJson('/api/users', [
            'name' => 'John Salesman',
            'email' => 'john.sales@testcrm.com',
            'password' => 'securepassword123',
            'roles' => ['Sales Representative'],
        ]);
        $createResponse->assertStatus(201)
            ->assertJsonPath('data.email', 'john.sales@testcrm.com')
            ->assertJsonPath('data.roles.0', 'Sales Representative');

        $createdId = $createResponse->json('data.id');

        // Show User
        $showResponse = $this->getJson("/api/users/{$createdId}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'John Salesman');

        // Update User
        $updateResponse = $this->putJson("/api/users/{$createdId}", [
            'name' => 'John Senior Salesman',
            'email' => 'john.sales@testcrm.com',
        ]);
        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.name', 'John Senior Salesman');

        // Delete User
        $deleteResponse = $this->deleteJson("/api/users/{$createdId}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $createdId]);
    }

    /* =========================================================================
     | 7. Notifications API
     | ========================================================================= */

    public function test_notifications_api(): void
    {
        Sanctum::actingAs($this->adminUser);

        $notif1 = Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\DealWon',
            'notifiable_type' => $this->adminUser->getMorphClass(),
            'notifiable_id' => $this->adminUser->id,
            'data' => ['title' => 'Deal closed!'],
            'read_at' => null,
        ]);

        $notif2 = Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\TaskReminder',
            'notifiable_type' => $this->adminUser->getMorphClass(),
            'notifiable_id' => $this->adminUser->id,
            'data' => ['title' => 'Task reminder'],
            'read_at' => null,
        ]);

        // List
        $listResponse = $this->getJson('/api/notifications');
        $listResponse->assertStatus(200)
            ->assertJsonPath('unread_count', 2);

        // Mark Single as Read
        $readResponse = $this->patchJson("/api/notifications/{$notif1->id}/read");
        $readResponse->assertStatus(200)
            ->assertJsonPath('data.is_read', true);

        // Mark All as Read
        $allReadResponse = $this->postJson('/api/notifications/mark-all-read');
        $allReadResponse->assertStatus(200);

        $this->assertEquals(0, $this->adminUser->unreadNotifications()->count());

        // Delete
        $deleteResponse = $this->deleteJson("/api/notifications/{$notif1->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('notifications', ['id' => $notif1->id]);
    }

    /* =========================================================================
     | 8. Activities API
     | ========================================================================= */

    public function test_activities_api(): void
    {
        Sanctum::actingAs($this->adminUser);

        $activity = Activity::factory()->create([
            'user_id' => $this->adminUser->id,
            'type' => 'call',
            'title' => 'Introductory Discovery Call',
            'duration_minutes' => 30,
            'status' => 'completed',
        ]);

        // Index
        $response = $this->getJson('/api/activities?type=call');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Introductory Discovery Call');

        // Store
        $createResponse = $this->postJson('/api/activities', [
            'type' => 'email',
            'title' => 'Follow up on contract amendments',
            'description' => 'Sent version 2.1 with updated clause 4.',
            'duration_minutes' => 15,
            'status' => 'completed',
        ]);
        $createResponse->assertStatus(201)
            ->assertJsonPath('data.type', 'email');

        // Show
        $showResponse = $this->getJson("/api/activities/{$activity->id}");
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.duration_minutes', 30);

        // Delete
        $deleteResponse = $this->deleteJson("/api/activities/{$activity->id}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('activities', ['id' => $activity->id]);
    }

    /* =========================================================================
     | 9. Settings API
     | ========================================================================= */

    public function test_settings_api(): void
    {
        Sanctum::actingAs($this->adminUser);

        Setting::set('company_name', 'CRM Enterprises', 'company');
        Setting::set('app_timezone', 'America/New_York', 'general');

        // Index
        $response = $this->getJson('/api/settings');
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'grouped'])
            ->assertJsonPath('grouped.company.company_name', 'CRM Enterprises');

        // Store / Bulk Update
        $updateResponse = $this->postJson('/api/settings', [
            'settings' => [
                ['key' => 'company_name', 'value' => 'CRM Enterprises Global', 'group' => 'company'],
                ['key' => 'currency_symbol', 'value' => 'EUR', 'group' => 'crm'],
            ]
        ]);
        $updateResponse->assertStatus(200);

        $this->assertEquals('CRM Enterprises Global', Setting::get('company_name'));
        $this->assertEquals('EUR', Setting::get('currency_symbol'));
    }

    /* =========================================================================
     | 10. Dynamic Real-Database Dashboard Analytics
     | ========================================================================= */

    public function test_dynamic_dashboard_kpis_and_date_filtering(): void
    {
        Sanctum::actingAs($this->adminUser);

        // Create initial leads, deals, tasks, activities
        $stageWon = DealStage::firstOrCreate(['name' => 'Won'], ['color' => '#10b981', 'order_index' => 6]);
        $stageNew = DealStage::firstOrCreate(['name' => 'New'], ['color' => '#3b82f6', 'order_index' => 1]);

        $statusConverted = LeadStatus::firstOrCreate(['name' => 'Converted']);
        $lead = Lead::factory()->create(['lead_status_id' => $statusConverted->id]);
        $dealOpen = Deal::factory()->create(['deal_stage_id' => $stageNew->id, 'status' => 'open', 'value' => 50000]);
        $dealWon = Deal::factory()->create(['deal_stage_id' => $stageWon->id, 'status' => 'won', 'value' => 150000, 'owner_id' => $this->adminUser->id]);
        $task = Task::factory()->create(['status' => 'Pending']);
        $activity = Activity::create([
            'user_id' => $this->adminUser->id,
            'subject_type' => 'Deal',
            'subject_id' => $dealWon->id,
            'type' => 'deal',
            'title' => 'Contract Closed Won',
            'description' => 'Deal closed successfully',
            'activity_date' => now(),
            'status' => 'completed',
        ]);

        // Default query (this_month)
        $response = $this->getJson('/api/dashboard?range=all');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'range',
                    'range_label',
                    'kpis' => [
                        'total_leads' => ['value', 'change'],
                        'open_deals' => ['value', 'change'],
                        'won_deals' => ['value', 'change'],
                        'revenue' => ['value', 'change'],
                        'conversion_rate' => ['value', 'label'],
                        'pending_tasks' => ['value', 'overdue'],
                    ],
                    'leads_overview' => ['labels', 'new_leads', 'converted_leads'],
                    'deals_by_stage',
                    'revenue_trend' => ['labels', 'revenue', 'won_deals'],
                    'sales_performance',
                    'recent_activities',
                ]
            ]);

        $kpis = $response->json('data.kpis');
        $this->assertGreaterThanOrEqual(1, $kpis['total_leads']['value']);
        $this->assertGreaterThanOrEqual(1, $kpis['open_deals']['value']);
        $this->assertGreaterThanOrEqual(1, $kpis['won_deals']['value']);
        $this->assertGreaterThanOrEqual(150000, $kpis['revenue']['value']);
        $this->assertGreaterThanOrEqual(1, $kpis['pending_tasks']['value']);

        // Date range filtering
        $filterResponse = $this->getJson('/api/dashboard?range=today');
        $filterResponse->assertStatus(200)
            ->assertJsonPath('data.range', 'today');
    }

    /* =========================================================================
     | 11. Dynamic Multi-Dimensional Reports API
     | ========================================================================= */

    public function test_dynamic_reports_and_filters(): void
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/reports?range=all');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'filters',
                    'options' => ['users', 'roles', 'sources', 'stages', 'statuses'],
                    'deals' => ['total_deals', 'won_deals', 'won_revenue', 'pipeline_value', 'win_rate', 'by_stage'],
                    'leads' => ['total_leads', 'converted_leads', 'conversion_rate', 'by_status', 'by_source'],
                    'tasks' => ['total_tasks', 'completed_tasks', 'completion_rate', 'by_priority'],
                    'trends' => ['labels', 'revenue', 'deals_count'],
                    'sales_performance',
                ]
            ]);

        // Filter by user and status
        $userFilterRes = $this->getJson("/api/reports?user_id={$this->adminUser->id}&status=Won");
        $userFilterRes->assertStatus(200)
            ->assertJsonPath('data.filters.user_id', (string) $this->adminUser->id)
            ->assertJsonPath('data.filters.status', 'Won');
    }

    /* =========================================================================
     | 12. Settings API (System, Company, Email, Integrations)
     | ========================================================================= */

    public function test_settings_retrieval_and_persistence(): void
    {
        Sanctum::actingAs($this->adminUser);

        $getRes = $this->getJson('/api/settings');
        $getRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['system', 'company', 'email', 'integrations']
            ]);

        $postRes = $this->postJson('/api/settings', [
            'system' => [
                'company_name' => 'Acme Enterprise CRM',
                'system_currency' => 'EUR',
                'timezone' => 'Europe/London',
            ],
            'email' => [
                'mail_host' => 'smtp.mailtrap.io',
                'mail_port' => '2525',
                'mail_username' => 'testuser',
                'mail_from_address' => 'noreply@acmecrm.com',
            ],
            'integrations' => [
                'slack_webhook_url' => 'https://hooks.slack.com/services/test/test',
                'zapier_webhook_url' => 'https://hooks.zapier.com/hooks/catch/test',
            ]
        ]);

        $postRes->assertStatus(200)
            ->assertJson(['success' => true]);

        $checkRes = $this->getJson('/api/settings');
        $this->assertEquals('Acme Enterprise CRM', $checkRes->json('data.system.company_name'));
        $this->assertEquals('EUR', $checkRes->json('data.system.system_currency'));
        $this->assertEquals('noreply@acmecrm.com', $checkRes->json('data.email.mail_from_address'));
    }

    /* =========================================================================
     | 13. Users Management API
     | ========================================================================= */

    public function test_users_crud_and_role_assignment(): void
    {
        Sanctum::actingAs($this->adminUser);

        // List users
        $listRes = $this->getJson('/api/users');
        $listRes->assertStatus(200)->assertJsonStructure(['success', 'data']);

        // Create user
        $createRes = $this->postJson('/api/users', [
            'name' => 'Sarah Connor',
            'email' => 'sarah.connor@testcrm.com',
            'password' => 'Resistance123!',
            'role' => 'Manager',
        ]);
        $createRes->assertStatus(201)
            ->assertJsonPath('data.name', 'Sarah Connor')
            ->assertJsonPath('data.role', 'Manager');

        $userId = $createRes->json('data.id');

        // Update user
        $updateRes = $this->putJson("/api/users/{$userId}", [
            'name' => 'Sarah Connor-Reese',
            'role' => 'Sales Representative',
        ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('data.name', 'Sarah Connor-Reese')
            ->assertJsonPath('data.role', 'Sales Representative');

        // Delete user
        $deleteRes = $this->deleteJson("/api/users/{$userId}");
        $deleteRes->assertStatus(200);

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    /* =========================================================================
     | 14. Roles and Permissions API
     | ========================================================================= */

    public function test_roles_and_permissions_endpoints(): void
    {
        Sanctum::actingAs($this->adminUser);

        // Get permissions grouped
        $permRes = $this->getJson('/api/permissions');
        $permRes->assertStatus(200)->assertJsonStructure(['success', 'data']);

        // Get roles
        $rolesRes = $this->getJson('/api/roles');
        $rolesRes->assertStatus(200)->assertJsonStructure(['success', 'data']);

        // Create custom role
        $createRoleRes = $this->postJson('/api/roles', [
            'name' => 'Marketing Specialist',
            'permissions' => ['view-leads', 'create-leads', 'edit-leads'],
        ]);
        $createRoleRes->assertStatus(201)
            ->assertJsonPath('data.name', 'Marketing Specialist');

        $roleId = $createRoleRes->json('data.id');

        // Update role
        $updateRoleRes = $this->putJson("/api/roles/{$roleId}", [
            'name' => 'Senior Marketing Specialist',
            'permissions' => ['view-leads', 'create-leads', 'edit-leads', 'delete-leads'],
        ]);
        $updateRoleRes->assertStatus(200)
            ->assertJsonPath('data.name', 'Senior Marketing Specialist');
    }

    /* =========================================================================
     | 15. Pipelines & Deal Stages API
     | ========================================================================= */

    public function test_deal_stages_crud_and_reordering(): void
    {
        Sanctum::actingAs($this->adminUser);

        // List stages
        $stagesRes = $this->getJson('/api/deal-stages');
        $stagesRes->assertStatus(200)->assertJsonStructure(['success', 'data']);

        // Create stage
        $createStageRes = $this->postJson('/api/deal-stages', [
            'name' => 'Technical Review',
            'color' => '#8b5cf6',
            'probability' => 45,
            'is_won' => false,
            'is_lost' => false,
        ]);
        $createStageRes->assertStatus(201)
            ->assertJsonPath('data.name', 'Technical Review');

        $stageId = $createStageRes->json('data.id');

        // Update stage
        $updateStageRes = $this->putJson("/api/deal-stages/{$stageId}", [
            'name' => 'Technical Validation',
            'probability' => 50,
        ]);
        $updateStageRes->assertStatus(200)
            ->assertJsonPath('data.name', 'Technical Validation')
            ->assertJsonPath('data.probability', 50);

        // Reorder stages
        $allStages = DealStage::orderBy('display_order')->get();
        $orders = $allStages->map(fn($s, $idx) => ['id' => $s->id, 'display_order' => $idx + 1])->toArray();

        $reorderRes = $this->postJson('/api/deal-stages/reorder', ['orders' => $orders]);
        $reorderRes->assertStatus(200);

        // Delete stage without deals
        $delStageRes = $this->deleteJson("/api/deal-stages/{$stageId}");
        $delStageRes->assertStatus(200);
        $this->assertDatabaseMissing('deal_stages', ['id' => $stageId]);
    }

    /* =========================================================================
     | 16. Custom Fields API
     | ========================================================================= */

    public function test_custom_fields_crud(): void
    {
        Sanctum::actingAs($this->adminUser);

        // Create custom field
        $createFieldRes = $this->postJson('/api/custom-fields', [
            'model_type' => 'App\\Models\\Lead',
            'name' => 'annual_budget',
            'label' => 'Annual Budget ($)',
            'field_type' => 'number',
            'is_required' => false,
        ]);
        $createFieldRes->assertStatus(201)
            ->assertJsonPath('data.name', 'annual_budget')
            ->assertJsonPath('data.field_type', 'number');

        $fieldId = $createFieldRes->json('data.id');

        // List custom fields
        $listFieldsRes = $this->getJson('/api/custom-fields?model_type=App\\Models\\Lead');
        $listFieldsRes->assertStatus(200)
            ->assertJsonFragment(['name' => 'annual_budget']);

        // Update custom field
        $updateFieldRes = $this->putJson("/api/custom-fields/{$fieldId}", [
            'label' => 'Estimated Annual Budget ($)',
            'is_required' => true,
        ]);
        $updateFieldRes->assertStatus(200)
            ->assertJsonPath('data.label', 'Estimated Annual Budget ($)')
            ->assertJsonPath('data.is_required', true);

        // Delete custom field
        $delFieldRes = $this->deleteJson("/api/custom-fields/{$fieldId}");
        $delFieldRes->assertStatus(200);
        $this->assertDatabaseMissing('custom_fields', ['id' => $fieldId]);
    }

    /* =========================================================================
     | 17. Backend Permission Enforcement (Admin vs Manager vs SalesRep vs Viewer)
     | ========================================================================= */

    public function test_backend_permission_enforcement_for_roles(): void
    {
        // 1. Viewer Role
        $viewerRole = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewerUser = User::factory()->create(['email' => 'viewer@testcrm.com']);
        $viewerUser->assignRole($viewerRole);

        Sanctum::actingAs($viewerUser);

        // Viewer CAN view leads
        $viewerGetLeads = $this->getJson('/api/leads');
        $viewerGetLeads->assertStatus(200);

        // Viewer CANNOT create a lead (must be forbidden 403)
        $viewerCreateLead = $this->postJson('/api/leads', [
            'first_name' => 'Hacker',
            'last_name' => 'Attempt',
            'email' => 'hacker@forbidden.com',
            'title' => 'Dev',
        ]);
        $viewerCreateLead->assertStatus(403);

        // Viewer CANNOT manage settings (must be forbidden 403)
        $viewerSettings = $this->postJson('/api/settings', [
            'system' => ['company_name' => 'Hacked Name']
        ]);
        $viewerSettings->assertStatus(403);

        // 2. Sales Representative Role
        $salesRepRole = Role::firstOrCreate(['name' => 'Sales Representative', 'guard_name' => 'web']);
        $salesRep = User::factory()->create(['email' => 'salesrep@testcrm.com']);
        $salesRep->assignRole($salesRepRole);

        Sanctum::actingAs($salesRep);

        // Sales Rep CAN create leads
        $repCreateLead = $this->postJson('/api/leads', [
            'first_name' => 'Prospect',
            'last_name' => 'Legit',
            'email' => 'prospect.legit@company.com',
            'title' => 'CTO',
        ]);
        $repCreateLead->assertStatus(201);

        // Sales Rep CANNOT modify settings (must be forbidden 403)
        $repSettings = $this->postJson('/api/settings', [
            'system' => ['company_name' => 'Rep Attempt']
        ]);
        $repSettings->assertStatus(403);
    }
}

