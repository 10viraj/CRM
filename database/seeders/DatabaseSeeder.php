<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\CalendarEvent;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\FollowUp;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions
        $this->call(RolePermissionSeeder::class);

        // 2. Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && !$user->hasRole('Admin')) {
            $user->assignRole($adminRole);
        }

        // Additional sales reps
        $salesRep = User::firstOrCreate(
            ['email' => 'sarah.sales@example.com'],
            [
                'name' => 'Sarah Connor',
                'password' => Hash::make('password'),
            ]
        );
        $repRole = Role::where('name', 'Sales Representative')->first();
        if ($repRole && !$salesRep->hasRole('Sales Representative')) {
            $salesRep->assignRole($repRole);
        }

        // 3. Companies
        $this->call(CompanySeeder::class);
        $companies = Company::all();

        // 4. Lead Sources
        $sources = ['Website', 'Referral', 'Cold Call', 'Conference', 'Social Media', 'Email Campaign'];
        foreach ($sources as $source) {
            LeadSource::firstOrCreate(['name' => $source]);
        }
        $sourceIds = LeadSource::pluck('id')->toArray();

        // 5. Lead Statuses
        $statuses = [
            ['name' => 'New', 'color' => 'blue'],
            ['name' => 'Contacted', 'color' => 'yellow'],
            ['name' => 'Qualified', 'color' => 'indigo'],
            ['name' => 'Proposal Sent', 'color' => 'purple'],
            ['name' => 'Negotiation', 'color' => 'orange'],
            ['name' => 'Won', 'color' => 'green'],
            ['name' => 'Lost', 'color' => 'red']
        ];
        foreach ($statuses as $status) {
            LeadStatus::firstOrCreate(['name' => $status['name']], ['color' => $status['color']]);
        }
        $statusIds = LeadStatus::pluck('id')->toArray();

        // 6. Deal Stages
        $dealStages = [
            ['name' => 'New', 'color' => 'blue', 'order_index' => 1],
            ['name' => 'Qualified', 'color' => 'indigo', 'order_index' => 2],
            ['name' => 'Proposal', 'color' => 'purple', 'order_index' => 3],
            ['name' => 'Negotiation', 'color' => 'orange', 'order_index' => 4],
            ['name' => 'Won', 'color' => 'green', 'order_index' => 5],
            ['name' => 'Lost', 'color' => 'red', 'order_index' => 6]
        ];
        $stageMap = [];
        foreach ($dealStages as $stage) {
            $model = DealStage::firstOrCreate(
                ['name' => $stage['name']],
                ['color' => $stage['color'], 'order_index' => $stage['order_index']]
            );
            $stageMap[$stage['name']] = $model->id;
        }

        // 7. Generate Leads
        for ($i = 0; $i < 50; $i++) {
            $matchedCompany = $companies->isNotEmpty() ? $companies->random() : null;
            $lead = Lead::create([
                'first_name' => 'Lead First ' . $i,
                'last_name'  => 'Last ' . $i,
                'company'    => $matchedCompany ? $matchedCompany->name : 'Company ' . rand(1, 100),
                'company_id' => $matchedCompany ? $matchedCompany->id : null,
                'email'      => 'lead' . $i . '@example.com',
                'phone'      => '(555) ' . rand(100, 999) . '-' . rand(1000, 9999),
                'lead_source_id' => $sourceIds[array_rand($sourceIds)],
                'lead_status_id' => $statusIds[array_rand($statusIds)],
                'score'      => rand(10, 100),
                'owner_id'   => $user->id,
                'notes'      => 'Initial note for lead ' . $i,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'type' => 'status_change',
                'description' => 'Lead created and assigned to ' . $user->name,
                'created_at' => $lead->created_at,
                'updated_at' => $lead->created_at,
            ]);
        }
        $leads = Lead::all();

        // 8. Contacts
        $this->call(ContactSeeder::class);
        $contacts = Contact::all();

        // 9. Deals
        $totalTarget = 4200000;
        $wonCount = 38;
        $avgDealValue = $totalTarget / $wonCount;

        for ($i = 0; $i < $wonCount; $i++) {
            $value = $avgDealValue + rand(-20000, 20000);
            $randomLead = $leads->isNotEmpty() ? $leads->random() : null;
            $matchedCompany = $randomLead && $randomLead->company_id ? $randomLead->companyModel : ($companies->isNotEmpty() ? $companies->random() : null);
            $matchedContact = $contacts->isNotEmpty() ? $contacts->random() : null;

            Deal::create([
                'name' => 'Won Enterprise Deal ' . $i,
                'value' => $value,
                'probability' => 100,
                'deal_stage_id' => $stageMap['Won'],
                'status' => 'won',
                'lead_id' => $randomLead ? $randomLead->id : null,
                'company_id' => $matchedCompany ? $matchedCompany->id : null,
                'contact_id' => $matchedContact ? $matchedContact->id : null,
                'owner_id' => $user->id,
                'close_date' => now()->subDays(rand(1, 30)),
                'notes' => 'Contract signed and active.',
                'created_at' => now()->subDays(rand(31, 60)),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            $randomLead = $leads->isNotEmpty() ? $leads->random() : null;
            $matchedCompany = $randomLead && $randomLead->company_id ? $randomLead->companyModel : ($companies->isNotEmpty() ? $companies->random() : null);
            $matchedContact = $contacts->isNotEmpty() ? $contacts->random() : null;
            $openStage = ['New', 'Qualified', 'Proposal', 'Negotiation'][rand(0, 3)];

            Deal::create([
                'name' => 'Open Deal ' . $i,
                'value' => rand(10000, 100000),
                'probability' => rand(20, 80),
                'deal_stage_id' => $stageMap[$openStage],
                'status' => 'open',
                'lead_id' => $randomLead ? $randomLead->id : null,
                'company_id' => $matchedCompany ? $matchedCompany->id : null,
                'contact_id' => $matchedContact ? $matchedContact->id : null,
                'owner_id' => $user->id,
                'close_date' => now()->addDays(rand(10, 60)),
                'notes' => 'Active opportunity in pipeline.',
                'created_at' => now()->subDays(rand(1, 15)),
                'updated_at' => now(),
            ]);
        }

        // 10. Product Categories & Products
        $categories = ['Software Licenses', 'Consulting Services', 'Hardware', 'Support Plans'];
        foreach ($categories as $cat) {
            ProductCategory::firstOrCreate(['name' => $cat]);
        }
        $catIds = ProductCategory::pluck('id')->toArray();

        $products = [
            ['name' => 'Enterprise CRM License', 'price' => 1500.00],
            ['name' => 'Implementation Consulting', 'price' => 200.00],
            ['name' => 'Priority Support 24/7', 'price' => 500.00],
            ['name' => 'Data Migration Service', 'price' => 1200.00],
            ['name' => 'Custom API Integration', 'price' => 2500.00],
        ];

        foreach ($products as $i => $prod) {
            Product::firstOrCreate(
                ['sku' => 'SKU-' . (1000 + $i)],
                [
                    'product_category_id' => $catIds[array_rand($catIds)],
                    'name' => $prod['name'],
                    'description' => 'Description for ' . $prod['name'],
                    'price' => $prod['price'],
                    'status' => 'active'
                ]
            );
        }
        $productIds = Product::pluck('id')->toArray();

        // 11. Quotations
        for ($i = 1; $i <= 15; $i++) {
            $randomLead = $leads->isNotEmpty() ? $leads->random() : null;
            $quote = Quotation::create([
                'quote_number' => 'QT-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'lead_id' => $randomLead ? $randomLead->id : null,
                'date' => now()->subDays(rand(1, 30)),
                'expiry_date' => now()->addDays(rand(10, 30)),
                'status' => ['draft', 'sent', 'accepted', 'rejected', 'expired'][rand(0, 4)],
                'notes' => 'Looking forward to doing business with you.',
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'grand_total' => 0
            ]);

            $subtotal = 0;
            for ($j = 0; $j < rand(1, 3); $j++) {
                $product = Product::find($productIds[array_rand($productIds)]);
                $qty = rand(1, 5);
                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;

                QuotationItem::create([
                    'quotation_id' => $quote->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'tax' => 0,
                    'discount' => 0,
                    'line_total' => $lineTotal
                ]);
            }

            $tax = $subtotal * 0.10;
            $quote->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => $subtotal + $tax
            ]);
        }

        // 12. Invoices & Payments
        for ($i = 1; $i <= 20; $i++) {
            $randomLead = $leads->isNotEmpty() ? $leads->random() : null;
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'lead_id' => $randomLead ? $randomLead->id : null,
                'date' => now()->subDays(rand(10, 60)),
                'due_date' => now()->addDays(rand(-10, 30)),
                'status' => 'draft',
                'notes' => 'Thank you for your business.',
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'grand_total' => 0,
                'amount_paid' => 0,
                'amount_due' => 0
            ]);

            $subtotal = 0;
            for ($j = 0; $j < rand(1, 4); $j++) {
                $product = Product::find($productIds[array_rand($productIds)]);
                $qty = rand(1, 10);
                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'tax' => 0,
                    'discount' => 0,
                    'line_total' => $lineTotal
                ]);
            }

            $tax = $subtotal * 0.10;
            $grand = $subtotal + $tax;
            $status = ['draft', 'sent', 'paid', 'overdue'][rand(0, 3)];
            $amountPaid = 0;

            if ($status === 'paid') {
                $amountPaid = $grand;
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $amountPaid,
                    'payment_date' => now()->subDays(rand(1, 5)),
                    'payment_method' => ['Credit Card', 'Bank Transfer', 'PayPal'][rand(0, 2)],
                    'reference_number' => 'REF-' . rand(10000, 99999),
                    'notes' => 'Payment received in full.'
                ]);
            } elseif ($status === 'sent' && rand(0, 1)) {
                $amountPaid = $grand * 0.5;
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'amount' => $amountPaid,
                    'payment_date' => now()->subDays(rand(1, 5)),
                    'payment_method' => 'Bank Transfer',
                    'reference_number' => 'REF-' . rand(10000, 99999),
                    'notes' => 'Partial payment received.'
                ]);
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => $grand,
                'amount_paid' => $amountPaid,
                'amount_due' => $grand - $amountPaid,
                'status' => $status
            ]);
        }

        // 13. Settings
        $defaultSettings = [
            ['key' => 'app_name', 'value' => 'SmartCRM', 'group' => 'general'],
            ['key' => 'timezone', 'value' => 'UTC', 'group' => 'general'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'group' => 'general'],
            ['key' => 'company_name', 'value' => 'SmartCRM Inc.', 'group' => 'company'],
            ['key' => 'company_email', 'value' => 'contact@smartcrm.com', 'group' => 'company'],
            ['key' => 'company_phone', 'value' => '+1 (555) 123-4567', 'group' => 'company'],
            ['key' => 'company_address', 'value' => '123 Business Avenue, Suite 100, San Francisco, CA 94107', 'group' => 'company'],
            ['key' => 'currency', 'value' => 'USD', 'group' => 'crm'],
            ['key' => 'tax_rate', 'value' => '10', 'group' => 'crm'],
        ];
        foreach ($defaultSettings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }

        // 14. Tasks
        $this->call(TaskSeeder::class);

        // 15. Calendar Events
        $this->call(CalendarEventSeeder::class);

        // 16. Unified Activities
        $this->call(ActivitySeeder::class);

        // 17. Custom Fields
        $this->call(CustomFieldSeeder::class);

        // 18. Audit Logs
        $this->call(AuditLogSeeder::class);

        // 19. Notifications
        $this->call(NotificationSeeder::class);

        // 20. FollowUps & Attachments
        for ($i = 0; $i < 12; $i++) {
            $randomLead = $leads->isNotEmpty() ? $leads->random() : null;
            if ($randomLead) {
                FollowUp::create([
                    'lead_id' => $randomLead->id,
                    'type' => 'Call',
                    'status' => 'Pending',
                    'scheduled_at' => now()->addDays(rand(1, 5)),
                ]);
            }
        }

        for ($i = 0; $i < 10; $i++) {
            $randomLead = $leads->isNotEmpty() ? $leads->random() : null;
            if ($randomLead) {
                Attachment::create([
                    'user_id' => $user->id,
                    'attachable_type' => Lead::class,
                    'attachable_id' => $randomLead->id,
                    'file_name' => 'document_' . rand(100, 999) . '.pdf',
                    'file_type' => 'application/pdf',
                    'file_size' => rand(1024, 5000000),
                    'file_path' => 'attachments/dummy_' . rand(100, 999) . '.pdf',
                    'created_at' => now()->subDays(rand(1, 30))
                ]);
            }
        }
    }
}
