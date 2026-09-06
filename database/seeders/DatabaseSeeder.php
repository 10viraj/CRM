<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\LeadActivity;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\FollowUp;
use App\Models\User;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\ActivityLog;
use App\Models\Attachment;




use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure the user exists
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );

        // 2. Seed Lead Sources
        $sources = ['Website', 'Referral', 'Cold Call', 'Conference', 'Social Media', 'Email Campaign'];
        foreach ($sources as $source) {
            LeadSource::firstOrCreate(['name' => $source]);
        }
        $sourceIds = LeadSource::pluck('id')->toArray();

        // 3. Seed Lead Statuses
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

        
        // Seed Deal Stages
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
            $model = DealStage::firstOrCreate(['name' => $stage['name']], ['color' => $stage['color'], 'order_index' => $stage['order_index']]);
            $stageMap[$stage['name']] = $model->id;
        }

        // 4. Generate exactly 142 leads
        for ($i = 0; $i < 142; $i++) {
            $lead = Lead::create([
                'first_name' => 'Lead First ' . $i,
                'last_name'  => 'Last ' . $i,
                'company'    => 'Company ' . rand(1, 100),
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

            // Add some activities
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'type' => 'status_change',
                'description' => 'Lead created and assigned to ' . $user->name,
                'created_at' => $lead->created_at,
                'updated_at' => $lead->created_at,
            ]);

            if (rand(0, 1)) {
                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $user->id,
                    'type' => 'email',
                    'description' => 'Sent welcome email to ' . $lead->email,
                    'created_at' => $lead->created_at->addHours(1),
                    'updated_at' => $lead->created_at->addHours(1),
                ]);
            }
        }

        // 5. Generate deals to total 4.2M revenue and exactly 38 won deals
        $totalTarget = 4200000;
        $avgDealValue = $totalTarget / 38;

        for ($i = 0; $i < 38; $i++) {
            $value = $avgDealValue + rand(-20000, 20000);
            Deal::insert([
                'name' => 'Won Deal ' . $i,
                'value' => $value,
                'deal_stage_id' => $stageMap['Won'],
                'lead_id' => rand(1, 142),
                'owner_id' => $user->id,
                'close_date' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(31, 60)),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 12; $i++) {
            Deal::insert([
                'name' => 'Open Deal ' . $i,
                'value' => rand(10000, 100000),
                'deal_stage_id' => $stageMap[['New', 'Qualified', 'Proposal', 'Negotiation'][rand(0, 3)]],
                'lead_id' => rand(1, 142),
                'owner_id' => $user->id,
                'close_date' => now()->addDays(rand(10, 60)),
                'created_at' => now()->subDays(rand(1, 15)),
                'updated_at' => now(),
            ]);
        }

        
        // 7. Seed Product Categories and Products
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
            Product::create([
                'product_category_id' => $catIds[array_rand($catIds)],
                'name' => $prod['name'],
                'description' => 'Description for ' . $prod['name'],
                'price' => $prod['price'],
                'sku' => 'SKU-' . (1000 + $i),
                'status' => 'active'
            ]);
        }
        $productIds = Product::pluck('id')->toArray();

        // 8. Generate Quotations
        for ($i = 1; $i <= 15; $i++) {
            $quote = Quotation::create([
                'quote_number' => 'QT-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'lead_id' => rand(1, 142),
                'date' => now()->subDays(rand(1, 30)),
                'expiry_date' => now()->addDays(rand(10, 30)),
                'status' => ['draft', 'sent', 'accepted', 'rejected', 'expired'][rand(0, 4)],
                'notes' => 'Looking forward to doing business with you.',
                'subtotal' => 0, // calculated below
                'tax' => 0,
                'discount' => 0,
                'grand_total' => 0
            ]);

            $subtotal = 0;
            // Add 1-3 items
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
            
            $tax = $subtotal * 0.10; // 10% tax
            $grand = $subtotal + $tax;
            $quote->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => $grand
            ]);
        }

        
        // 9. Generate Invoices and Payments
        for ($i = 1; $i <= 20; $i++) {
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'lead_id' => rand(1, 142),
                'date' => now()->subDays(rand(10, 60)),
                'due_date' => now()->addDays(rand(-10, 30)),
                'status' => 'draft', // updated below
                'notes' => 'Thank you for your business.',
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'grand_total' => 0,
                'amount_paid' => 0,
                'amount_due' => 0
            ]);

            $subtotal = 0;
            // Add 1-4 items
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
            
            $tax = $subtotal * 0.10; // 10% tax
            $grand = $subtotal + $tax;
            
            // Randomly pay some invoices
            $amountPaid = 0;
            $status = ['draft', 'sent', 'paid', 'overdue'][rand(0, 3)];
            
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
                // Partial payment
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

        
        // 10. Seed Default Settings
        $defaultSettings = [
            // General
            ['key' => 'app_name', 'value' => 'SmartCRM', 'group' => 'general'],
            ['key' => 'timezone', 'value' => 'UTC', 'group' => 'general'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'group' => 'general'],
            // Company Info
            ['key' => 'company_name', 'value' => 'SmartCRM Inc.', 'group' => 'company'],
            ['key' => 'company_email', 'value' => 'contact@smartcrm.com', 'group' => 'company'],
            ['key' => 'company_phone', 'value' => '+1 (555) 123-4567', 'group' => 'company'],
            ['key' => 'company_address', 'value' => '123 Business Avenue, Suite 100, San Francisco, CA 94107', 'group' => 'company'],
            // CRM
            ['key' => 'currency', 'value' => 'USD', 'group' => 'crm'],
            ['key' => 'tax_rate', 'value' => '10', 'group' => 'crm'],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }

        
        // 11. Seed Activity Logs
        for ($i = 0; $i < 30; $i++) {
            $modules = ['lead', 'deal', 'company', 'invoice', 'setting'];
            $actions = ['created', 'updated', 'deleted', 'viewed', 'completed'];
            $module = $modules[array_rand($modules)];
            $action = $actions[array_rand($actions)];
            
            ActivityLog::create([
                'user_id' => 1,
                'action' => $action,
                'module' => $module,
                'description' => "Admin {$action} a {$module} record.",
                'ip_address' => '192.168.1.' . rand(1, 255),
                'created_at' => now()->subHours(rand(1, 100))
            ]);
        }
        
        // 12. Seed Attachments
        for ($i = 0; $i < 10; $i++) {
            Attachment::create([
                'user_id' => 1,
                'attachable_type' => 'App\Models\Lead',
                'attachable_id' => rand(1, 142),
                'file_name' => 'document_' . rand(100, 999) . '.pdf',
                'file_type' => 'application/pdf',
                'file_size' => rand(1024, 5000000), // 1KB to 5MB
                'file_path' => 'attachments/dummy_' . rand(100, 999) . '.pdf',
                'created_at' => now()->subDays(rand(1, 30))
            ]);
        }

        // 6. Generate exactly 12 pending follow-ups
        for ($i = 0; $i < 12; $i++) {
            FollowUp::insert([
                'lead_id' => rand(1, 142),
                'type' => 'Call',
                'status' => 'Pending',
                'scheduled_at' => now()->addDays(rand(1, 5)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        for ($i = 0; $i < 5; $i++) {
            FollowUp::insert([
                'lead_id' => rand(1, 142),
                'type' => 'Email',
                'status' => 'Completed',
                'scheduled_at' => now()->subDays(rand(1, 5)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
