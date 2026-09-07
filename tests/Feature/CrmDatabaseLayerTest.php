<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\CalendarEvent;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CrmDatabaseLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_roles_and_permissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'edit-deals', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create([
            'email' => 'manager@crm.test',
        ]);
        $user->assignRole($role);

        $this->assertTrue($user->hasRole('Manager'));
        $this->assertTrue($user->hasPermissionTo('edit-deals'));
    }

    public function test_company_creation_and_relationships(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Test Corp']);
        $contact = Contact::factory()->create(['company_id' => $company->id]);
        $lead = Lead::factory()->create(['company_id' => $company->id]);
        $deal = Deal::factory()->create(['company_id' => $company->id]);

        $this->assertCount(1, $company->contacts);
        $this->assertCount(1, $company->leads);
        $this->assertCount(1, $company->deals);
        $this->assertEquals('Acme Test Corp', $contact->company->name);
    }

    public function test_contact_relationships(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $lead = Lead::factory()->create();

        $contact = Contact::factory()->create([
            'company_id' => $company->id,
            'owner_id' => $user->id,
            'lead_id' => $lead->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_primary' => true,
        ]);

        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals($company->id, $contact->company->id);
        $this->assertEquals($user->id, $contact->owner->id);
        $this->assertEquals($lead->id, $contact->lead->id);
        $this->assertTrue($contact->is_primary);
    }

    public function test_lead_and_deal_relationships(): void
    {
        $user = User::factory()->create();
        $source = LeadSource::create(['name' => 'Web Search']);
        $status = LeadStatus::create(['name' => 'Qualified', 'color' => 'indigo']);
        $stage = DealStage::factory()->create(['name' => 'Negotiation', 'order_index' => 3]);
        $company = Company::factory()->create();

        $lead = Lead::factory()->create([
            'lead_source_id' => $source->id,
            'lead_status_id' => $status->id,
            'company_id' => $company->id,
            'owner_id' => $user->id,
        ]);

        $deal = Deal::factory()->create([
            'lead_id' => $lead->id,
            'deal_stage_id' => $stage->id,
            'company_id' => $company->id,
            'owner_id' => $user->id,
            'value' => 75000,
            'probability' => 80,
            'status' => 'open',
        ]);

        $this->assertEquals('Web Search', $lead->source->name);
        $this->assertEquals('Qualified', $lead->status->name);
        $this->assertEquals($company->id, $lead->companyModel->id);
        $this->assertCount(1, $lead->deals);

        $this->assertEquals('Negotiation', $deal->stage->name);
        $this->assertEquals($lead->id, $deal->lead->id);
        $this->assertEquals($company->id, $deal->company->id);
        $this->assertEquals(75000, $deal->value);
    }

    public function test_task_creation_and_polymorphic_relations(): void
    {
        $user = User::factory()->create();
        $lead = Lead::factory()->create();

        $task = Task::factory()->create([
            'title' => 'Follow up call',
            'assign_to_id' => $user->id,
            'creator_id' => $user->id,
            'related_to_type' => $lead->getMorphClass(),
            'related_to_id' => $lead->id,
            'status' => 'Pending',
        ]);

        $this->assertEquals($user->id, $task->assignee->id);
        $this->assertEquals($user->id, $task->creator->id);
        $this->assertInstanceOf(Lead::class, $task->relatedTo);
        $this->assertEquals($lead->id, $task->relatedTo->id);
        $this->assertCount(1, $lead->fresh()->tasks);
    }

    public function test_calendar_events_polymorphic_relations(): void
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create();

        $event = CalendarEvent::factory()->create([
            'user_id' => $user->id,
            'title' => 'Deal Closing Review',
            'eventable_type' => $deal->getMorphClass(),
            'eventable_id' => $deal->id,
        ]);

        $this->assertEquals($user->id, $event->user->id);
        $this->assertInstanceOf(Deal::class, $event->eventable);
        $this->assertEquals($deal->id, $event->eventable->id);
        $this->assertCount(1, $deal->fresh()->calendarEvents);
    }

    public function test_unified_activities_polymorphic_relations(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create();

        $activity = Activity::factory()->create([
            'user_id' => $user->id,
            'type' => 'call',
            'title' => 'Quarterly Check-in',
            'subject_type' => $contact->getMorphClass(),
            'subject_id' => $contact->id,
        ]);

        $this->assertEquals($user->id, $activity->user->id);
        $this->assertInstanceOf(Contact::class, $activity->subject);
        $this->assertEquals($contact->id, $activity->subject->id);
        $this->assertCount(1, $contact->fresh()->activities);
    }

    public function test_custom_fields_and_trait(): void
    {
        $field = CustomField::create([
            'model_type' => (new Lead)->getMorphClass(),
            'name' => 'annual_revenue',
            'label' => 'Annual Revenue',
            'field_type' => 'text',
            'default_value' => '$1M - $5M',
        ]);

        $lead = Lead::factory()->create();

        // Check default
        $this->assertEquals('$1M - $5M', $lead->getCustomFieldValue('annual_revenue'));

        // Set value
        $lead->setCustomFieldValue('annual_revenue', '$10M+');
        $this->assertEquals('$10M+', $lead->getCustomFieldValue('annual_revenue'));
    }

    public function test_settings_helper_methods(): void
    {
        Setting::set('support_email', 'help@crm.test', 'support');

        $this->assertEquals('help@crm.test', Setting::get('support_email'));
        $this->assertEquals('default@crm.test', Setting::get('non_existent', 'default@crm.test'));

        $group = Setting::getGroup('support');
        $this->assertArrayHasKey('support_email', $group);
        $this->assertEquals('help@crm.test', $group['support_email']);
    }

    public function test_audit_logs(): void
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create(['value' => 10000]);

        $log = AuditLog::create([
            'user_id' => $user->id,
            'action' => 'updated',
            'auditable_type' => $deal->getMorphClass(),
            'auditable_id' => $deal->id,
            'old_values' => ['value' => 10000],
            'new_values' => ['value' => 20000],
            'url' => 'http://localhost/deals',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertEquals($user->id, $log->user->id);
        $this->assertEquals(20000, $log->new_values['value']);
    }

    public function test_notifications_model(): void
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'id' => (string) Str::uuid(),
            'type' => 'App\Notifications\DealWon',
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => ['message' => 'Deal closed!'],
            'read_at' => null,
        ]);

        $this->assertCount(1, $user->unreadNotifications);
        $this->assertEquals('Deal closed!', $user->unreadNotifications->first()->data['message']);
    }
}
