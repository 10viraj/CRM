<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Task;
use Carbon\Carbon;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Real Sales Team Users
        $usersData = [
            ['name' => 'Admin User', 'email' => 'admin@example.com'],
            ['name' => 'Sarah Jenkins', 'email' => 'sarah.jenkins@crm.com'],
            ['name' => 'Alex Morgan', 'email' => 'alex.morgan@crm.com'],
            ['name' => 'David Kumar', 'email' => 'david.kumar@crm.com'],
            ['name' => 'Emma Watson', 'email' => 'emma.watson@crm.com'],
            ['name' => 'Michael Chen', 'email' => 'michael.chen@crm.com'],
        ];

        $users = [];
        foreach ($usersData as $u) {
            $users[] = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        // 2. Real Companies
        $companiesData = [
            ['name' => 'Acme Global Corporation', 'industry' => 'Enterprise Software', 'city' => 'San Francisco', 'country' => 'USA'],
            ['name' => 'Stark Innovations', 'industry' => 'Advanced Robotics & AI', 'city' => 'New York', 'country' => 'USA'],
            ['name' => 'Nexus Cloud Infrastructure', 'industry' => 'Cloud & DevOps', 'city' => 'Seattle', 'country' => 'USA'],
            ['name' => 'Cyberdyne AI Solutions', 'industry' => 'Artificial Intelligence', 'city' => 'Austin', 'country' => 'USA'],
            ['name' => 'Vanguard Health Systems', 'industry' => 'Healthcare Technology', 'city' => 'Boston', 'country' => 'USA'],
            ['name' => 'Horizon Financial Group', 'industry' => 'FinTech & Banking', 'city' => 'Chicago', 'country' => 'USA'],
            ['name' => 'Pied Piper Compression', 'industry' => 'Data Infrastructure', 'city' => 'Palo Alto', 'country' => 'USA'],
            ['name' => 'Initech Systems', 'industry' => 'Enterprise Consulting', 'city' => 'Dallas', 'country' => 'USA'],
            ['name' => 'Globex Logistics Corp', 'industry' => 'Global Logistics & Supply', 'city' => 'Atlanta', 'country' => 'USA'],
            ['name' => 'Summit Peak Media', 'industry' => 'Digital Marketing & AdTech', 'city' => 'Los Angeles', 'country' => 'USA'],
            ['name' => 'BlueWave BioTech', 'industry' => 'Pharmaceuticals', 'city' => 'San Diego', 'country' => 'USA'],
            ['name' => 'Quantum Dynamic Robotics', 'industry' => 'Industrial Automation', 'city' => 'Detroit', 'country' => 'USA'],
            ['name' => 'Sterling Commerce Ltd', 'industry' => 'E-Commerce Platforms', 'city' => 'London', 'country' => 'UK'],
            ['name' => 'Apex Energy Solutions', 'industry' => 'Renewable Energy', 'city' => 'Denver', 'country' => 'USA'],
            ['name' => 'Crestline Capital Management', 'industry' => 'Asset Management', 'city' => 'Miami', 'country' => 'USA'],
        ];

        $companies = [];
        foreach ($companiesData as $c) {
            $companies[] = Company::firstOrCreate(
                ['name' => $c['name']],
                [
                    'industry' => $c['industry'],
                    'city' => $c['city'],
                    'country' => $c['country'],
                    'email' => strtolower(str_replace([' ', '&', 'Ltd', 'Corp'], '', $c['name'])) . '@example.com',
                    'phone' => '+1 (555) ' . rand(200, 899) . '-' . rand(1000, 9999),
                    'website' => 'https://www.' . strtolower(str_replace([' ', '&', 'Ltd', 'Corp'], '', $c['name'])) . '.com',
                ]
            );
        }

        // 3. Ensure Deal Stages
        $stages = [
            'New' => DealStage::firstOrCreate(['name' => 'New'], ['color' => 'blue', 'order_index' => 1]),
            'Qualified' => DealStage::firstOrCreate(['name' => 'Qualified'], ['color' => 'indigo', 'order_index' => 2]),
            'Proposal' => DealStage::firstOrCreate(['name' => 'Proposal'], ['color' => 'purple', 'order_index' => 3]),
            'Negotiation' => DealStage::firstOrCreate(['name' => 'Negotiation'], ['color' => 'orange', 'order_index' => 4]),
            'Won' => DealStage::firstOrCreate(['name' => 'Won'], ['color' => 'green', 'order_index' => 5]),
            'Lost' => DealStage::firstOrCreate(['name' => 'Lost'], ['color' => 'red', 'order_index' => 6]),
        ];

        // 4. Ensure Lead Sources and Statuses
        $leadSources = ['Website', 'Referral', 'LinkedIn Outreach', 'Conference', 'Direct Inbound', 'Partner'];
        $srcModels = [];
        foreach ($leadSources as $src) {
            $srcModels[] = LeadSource::firstOrCreate(['name' => $src]);
        }

        $leadStatuses = [
            'New' => LeadStatus::firstOrCreate(['name' => 'New'], ['color' => 'blue']),
            'Contacted' => LeadStatus::firstOrCreate(['name' => 'Contacted'], ['color' => 'yellow']),
            'Qualified' => LeadStatus::firstOrCreate(['name' => 'Qualified'], ['color' => 'indigo']),
            'Proposal Sent' => LeadStatus::firstOrCreate(['name' => 'Proposal Sent'], ['color' => 'purple']),
            'Won' => LeadStatus::firstOrCreate(['name' => 'Won'], ['color' => 'green']),
        ];

        // 5. Seed Real Leads
        $leadsData = [
            ['first' => 'Thomas', 'last' => 'Anderson', 'comp' => 'Nexus Cloud Infrastructure', 'email' => 't.anderson@nexuscloud.io', 'phone' => '(415) 892-3401', 'score' => 95],
            ['first' => 'Sarah', 'last' => 'Connor', 'comp' => 'Cyberdyne AI Solutions', 'email' => 's.connor@cyberdyneai.com', 'phone' => '(512) 441-9023', 'score' => 88],
            ['first' => 'Tony', 'last' => 'Stark', 'comp' => 'Stark Innovations', 'email' => 'tony@starkinnovations.com', 'phone' => '(212) 993-1100', 'score' => 98],
            ['first' => 'Bruce', 'last' => 'Wayne', 'comp' => 'Acme Global Corporation', 'email' => 'bruce@acmeglobal.com', 'phone' => '(312) 554-7890', 'score' => 92],
            ['first' => 'Natasha', 'last' => 'Romanoff', 'comp' => 'Horizon Financial Group', 'email' => 'n.romanoff@horizonfin.com', 'phone' => '(312) 771-4455', 'score' => 84],
            ['first' => 'Harvey', 'last' => 'Specter', 'comp' => 'Crestline Capital Management', 'email' => 'h.specter@crestlinecap.com', 'phone' => '(305) 882-9901', 'score' => 96],
            ['first' => 'Donna', 'last' => 'Paulsen', 'comp' => 'Initech Systems', 'email' => 'donna@initechsys.com', 'phone' => '(214) 630-1122', 'score' => 90],
            ['first' => 'Peter', 'last' => 'Parker', 'comp' => 'Summit Peak Media', 'email' => 'p.parker@summitpeak.com', 'phone' => '(213) 448-9011', 'score' => 78],
            ['first' => 'Walter', 'last' => 'White', 'comp' => 'BlueWave BioTech', 'email' => 'w.white@bluewavebio.com', 'phone' => '(858) 991-3420', 'score' => 85],
            ['first' => 'Richard', 'last' => 'Hendricks', 'comp' => 'Pied Piper Compression', 'email' => 'richard@piedpiper.io', 'phone' => '(650) 332-9088', 'score' => 94],
            ['first' => 'Eleanor', 'last' => 'Vance', 'comp' => 'Vanguard Health Systems', 'email' => 'e.vance@vanguardhealth.org', 'phone' => '(617) 552-8819', 'score' => 89],
            ['first' => 'Marcus', 'last' => 'Brody', 'comp' => 'Globex Logistics Corp', 'email' => 'm.brody@globexlogistics.com', 'phone' => '(404) 773-1940', 'score' => 82],
            ['first' => 'Diana', 'last' => 'Prince', 'comp' => 'Sterling Commerce Ltd', 'email' => 'd.prince@sterlingcommerce.co.uk', 'phone' => '+44 20 7946 0912', 'score' => 91],
            ['first' => 'Clark', 'last' => 'Kent', 'comp' => 'Apex Energy Solutions', 'email' => 'c.kent@apexenergy.com', 'phone' => '(303) 441-2900', 'score' => 86],
            ['first' => 'Arthur', 'last' => 'Shelby', 'comp' => 'Quantum Dynamic Robotics', 'email' => 'a.shelby@quantumrobotics.io', 'phone' => '(313) 882-7711', 'score' => 80],
        ];

        $leads = [];
        foreach ($leadsData as $i => $l) {
            $leads[] = Lead::updateOrCreate(
                ['email' => $l['email']],
                [
                    'first_name' => $l['first'],
                    'last_name' => $l['last'],
                    'company' => $l['comp'],
                    'phone' => $l['phone'],
                    'score' => $l['score'],
                    'lead_source_id' => $srcModels[$i % count($srcModels)]->id,
                    'lead_status_id' => $leadStatuses['Qualified']->id,
                    'owner_id' => $users[$i % count($users)]->id,
                    'notes' => 'Key decision maker for enterprise evaluation.',
                ]
            );
        }

        // 6. Delete old generic dummy "Open Deal X" / "Won Deal X" deals if they exist
        Deal::where('name', 'like', 'Open Deal %')
            ->orWhere('name', 'like', 'Won Deal %')
            ->delete();

        // 7. Seed Real Business Deals
        $realDeals = [
            [
                'name' => 'Enterprise Multi-Cloud Infrastructure Migration',
                'value' => 145000.00,
                'stage' => 'Proposal',
                'lead_idx' => 0, // Thomas Anderson (Nexus Cloud)
                'owner_idx' => 1, // Sarah Jenkins
                'close_days' => 24,
                'created_days_ago' => 18,
            ],
            [
                'name' => 'AI-Powered Automated Customer Analytics Engine',
                'value' => 112000.00,
                'stage' => 'Negotiation',
                'lead_idx' => 1, // Sarah Connor (Cyberdyne)
                'owner_idx' => 2, // Alex Morgan
                'close_days' => 14,
                'created_days_ago' => 35,
            ],
            [
                'name' => 'Advanced Robotics Automation & Telemetry Suite',
                'value' => 240000.00,
                'stage' => 'Qualified',
                'lead_idx' => 2, // Tony Stark (Stark Innovations)
                'owner_idx' => 0, // Admin User
                'close_days' => 45,
                'created_days_ago' => 12,
            ],
            [
                'name' => 'Annual SaaS Subscription Enterprise Renewal',
                'value' => 84500.00,
                'stage' => 'Won',
                'lead_idx' => 3, // Bruce Wayne (Acme Global)
                'owner_idx' => 3, // David Kumar
                'close_days' => -5,
                'created_days_ago' => 60,
            ],
            [
                'name' => 'FinTech Core Banking & Security Compliance Suite',
                'value' => 175000.00,
                'stage' => 'Proposal',
                'lead_idx' => 4, // Natasha Romanoff (Horizon Financial)
                'owner_idx' => 4, // Emma Watson
                'close_days' => 19,
                'created_days_ago' => 22,
            ],
            [
                'name' => 'Capital Asset Portfolio Management System',
                'value' => 130000.00,
                'stage' => 'Negotiation',
                'lead_idx' => 5, // Harvey Specter (Crestline)
                'owner_idx' => 1, // Sarah Jenkins
                'close_days' => 8,
                'created_days_ago' => 40,
            ],
            [
                'name' => 'Custom CRM API Gateway & Legacy ERP Integration',
                'value' => 48500.00,
                'stage' => 'New',
                'lead_idx' => 6, // Donna Paulsen (Initech)
                'owner_idx' => 5, // Michael Chen
                'close_days' => 30,
                'created_days_ago' => 4,
            ],
            [
                'name' => 'Omnichannel Marketing AdTech Platform Setup',
                'value' => 54000.00,
                'stage' => 'Qualified',
                'lead_idx' => 7, // Peter Parker (Summit Peak Media)
                'owner_idx' => 2, // Alex Morgan
                'close_days' => 32,
                'created_days_ago' => 14,
            ],
            [
                'name' => 'Biotech Clinical Research Data Protection Protocol',
                'value' => 98000.00,
                'stage' => 'Won',
                'lead_idx' => 8, // Walter White (BlueWave BioTech)
                'owner_idx' => 3, // David Kumar
                'close_days' => -12,
                'created_days_ago' => 55,
            ],
            [
                'name' => 'Distributed Lossless Data Compression Engine',
                'value' => 190000.00,
                'stage' => 'Won',
                'lead_idx' => 9, // Richard Hendricks (Pied Piper)
                'owner_idx' => 0, // Admin User
                'close_days' => -20,
                'created_days_ago' => 70,
            ],
            [
                'name' => 'HIPAA-Compliant Patient Portal & EHR Integration',
                'value' => 115000.00,
                'stage' => 'Proposal',
                'lead_idx' => 10, // Eleanor Vance (Vanguard Health)
                'owner_idx' => 4, // Emma Watson
                'close_days' => 21,
                'created_days_ago' => 20,
            ],
            [
                'name' => 'Global Supply Chain Fleet Telematics System',
                'value' => 160000.00,
                'stage' => 'Negotiation',
                'lead_idx' => 11, // Marcus Brody (Globex Logistics)
                'owner_idx' => 5, // Michael Chen
                'close_days' => 11,
                'created_days_ago' => 30,
            ],
            [
                'name' => 'Multi-Region B2B E-Commerce Marketplace Engine',
                'value' => 125000.00,
                'stage' => 'New',
                'lead_idx' => 12, // Diana Prince (Sterling Commerce)
                'owner_idx' => 1, // Sarah Jenkins
                'close_days' => 40,
                'created_days_ago' => 2,
            ],
            [
                'name' => 'Smart Grid Renewable Energy Analytics Dashboard',
                'value' => 88000.00,
                'stage' => 'Qualified',
                'lead_idx' => 13, // Clark Kent (Apex Energy)
                'owner_idx' => 2, // Alex Morgan
                'close_days' => 28,
                'created_days_ago' => 16,
            ],
            [
                'name' => 'Zero-Trust Cybersecurity Network Architecture Upgrade',
                'value' => 155000.00,
                'stage' => 'Won',
                'lead_idx' => 14, // Arthur Shelby (Quantum Dynamic)
                'owner_idx' => 0, // Admin User
                'close_days' => -8,
                'created_days_ago' => 50,
            ],
        ];

        foreach ($realDeals as $dealData) {
            $stageModel = $stages[$dealData['stage']] ?? $stages['New'];
            $leadModel = $leads[$dealData['lead_idx']] ?? $leads[0];
            $ownerModel = $users[$dealData['owner_idx']] ?? $users[0];

            $closeDate = $dealData['close_days'] >= 0 
                ? Carbon::now()->addDays($dealData['close_days'])->format('Y-m-d')
                : Carbon::now()->subDays(abs($dealData['close_days']))->format('Y-m-d');

            $createdAt = Carbon::now()->subDays($dealData['created_days_ago']);

            Deal::create([
                'name' => $dealData['name'],
                'value' => $dealData['value'],
                'deal_stage_id' => $stageModel->id,
                'lead_id' => $leadModel->id,
                'owner_id' => $ownerModel->id,
                'close_date' => $closeDate,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ]);
        }
    }
}
