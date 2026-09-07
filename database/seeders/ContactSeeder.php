<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        $user = User::first();
        $leads = Lead::all();

        if ($companies->isEmpty()) {
            return;
        }

        foreach ($companies as $company) {
            // Create a primary contact
            Contact::create([
                'company_id' => $company->id,
                'owner_id' => $user ? $user->id : null,
                'lead_id' => $leads->isNotEmpty() ? $leads->random()->id : null,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'mobile' => fake()->phoneNumber(),
                'job_title' => 'Chief Technology Officer',
                'department' => 'Executive',
                'city' => $company->city,
                'state' => $company->state,
                'country' => $company->country,
                'is_primary' => true,
                'notes' => 'Primary decision maker.',
            ]);

            // Create a secondary contact
            Contact::create([
                'company_id' => $company->id,
                'owner_id' => $user ? $user->id : null,
                'lead_id' => null,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'mobile' => fake()->phoneNumber(),
                'job_title' => 'Procurement Manager',
                'department' => 'Operations',
                'city' => $company->city,
                'state' => $company->state,
                'country' => $company->country,
                'is_primary' => false,
                'notes' => 'Handles vendor negotiations and contracts.',
            ]);
        }
    }
}
