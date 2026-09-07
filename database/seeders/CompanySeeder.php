<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Acme Corporation',
                'email' => 'contact@acmecorp.com',
                'phone' => '+1 (555) 111-2233',
                'website' => 'https://acmecorp.com',
                'industry' => 'Technology',
                'address' => '100 Innovation Way',
                'city' => 'San Francisco',
                'state' => 'CA',
                'zip' => '94105',
                'country' => 'USA',
            ],
            [
                'name' => 'Stark Global Industries',
                'email' => 'info@starkglobal.com',
                'phone' => '+1 (555) 222-3344',
                'website' => 'https://starkglobal.com',
                'industry' => 'Manufacturing',
                'address' => '10880 Wilshire Blvd',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip' => '90024',
                'country' => 'USA',
            ],
            [
                'name' => 'Wayne Enterprises',
                'email' => 'contact@wayneenterprises.com',
                'phone' => '+1 (555) 333-4455',
                'website' => 'https://wayneenterprises.com',
                'industry' => 'Finance',
                'address' => '1007 Mountain Drive',
                'city' => 'Gotham',
                'state' => 'NY',
                'zip' => '10001',
                'country' => 'USA',
            ],
            [
                'name' => 'Cyberdyne Systems',
                'email' => 'hello@cyberdyne.io',
                'phone' => '+1 (555) 444-5566',
                'website' => 'https://cyberdyne.io',
                'industry' => 'Technology',
                'address' => '18144 El Camino Real',
                'city' => 'Sunnyvale',
                'state' => 'CA',
                'zip' => '94087',
                'country' => 'USA',
            ],
            [
                'name' => 'Umbrella Health Corp',
                'email' => 'support@umbrellahealth.org',
                'phone' => '+1 (555) 555-6677',
                'website' => 'https://umbrellahealth.org',
                'industry' => 'Healthcare',
                'address' => '42 BioPark Parkway',
                'city' => 'Raleigh',
                'state' => 'NC',
                'zip' => '27601',
                'country' => 'USA',
            ],
        ];

        foreach ($companies as $comp) {
            Company::firstOrCreate(['name' => $comp['name']], $comp);
        }

        Company::factory()->count(10)->create();
    }
}
