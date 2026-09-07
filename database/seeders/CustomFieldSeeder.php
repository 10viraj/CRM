<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\Deal;
use App\Models\Lead;
use Illuminate\Database\Seeder;

class CustomFieldSeeder extends Seeder
{
    public function run(): void
    {
        // Custom fields for Lead
        $leadSourceChannel = CustomField::firstOrCreate(
            ['model_type' => Lead::class, 'name' => 'acquisition_channel'],
            [
                'label' => 'Acquisition Channel',
                'field_type' => 'select',
                'options' => ['Direct', 'Google Ads', 'LinkedIn', 'Partner', 'Organic Search'],
                'is_required' => false,
                'default_value' => 'Organic Search',
                'order_index' => 1,
            ]
        );

        $leadBudget = CustomField::firstOrCreate(
            ['model_type' => Lead::class, 'name' => 'budget_range'],
            [
                'label' => 'Estimated Budget',
                'field_type' => 'text',
                'options' => null,
                'is_required' => false,
                'default_value' => '$10,000 - $50,000',
                'order_index' => 2,
            ]
        );

        // Custom fields for Deal
        $dealRegion = CustomField::firstOrCreate(
            ['model_type' => Deal::class, 'name' => 'sales_region'],
            [
                'label' => 'Sales Region',
                'field_type' => 'select',
                'options' => ['North America', 'EMEA', 'APAC', 'LATAM'],
                'is_required' => false,
                'default_value' => 'North America',
                'order_index' => 1,
            ]
        );

        // Custom fields for Company
        $companyEmployees = CustomField::firstOrCreate(
            ['model_type' => Company::class, 'name' => 'employee_count'],
            [
                'label' => 'Employee Count',
                'field_type' => 'number',
                'options' => null,
                'is_required' => false,
                'default_value' => '50',
                'order_index' => 1,
            ]
        );

        // Populate sample values on models
        $leads = Lead::take(10)->get();
        foreach ($leads as $lead) {
            $lead->setCustomFieldValue('acquisition_channel', 'Google Ads');
            $lead->setCustomFieldValue('budget_range', '$25,000 - $75,000');
        }

        $deals = Deal::take(10)->get();
        foreach ($deals as $deal) {
            $deal->setCustomFieldValue('sales_region', 'North America');
        }

        $companies = Company::take(5)->get();
        foreach ($companies as $company) {
            $company->setCustomFieldValue('employee_count', '250');
        }
    }
}
