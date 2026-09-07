<?php

use App\Models\DealStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $stages = [
            ['name' => 'New', 'color' => 'blue', 'order_index' => 1],
            ['name' => 'Contacted', 'color' => 'cyan', 'order_index' => 2],
            ['name' => 'Qualified', 'color' => 'indigo', 'order_index' => 3],
            ['name' => 'Proposal', 'color' => 'purple', 'order_index' => 4],
            ['name' => 'Negotiation', 'color' => 'orange', 'order_index' => 5],
            ['name' => 'Won', 'color' => 'green', 'order_index' => 6],
            ['name' => 'Lost', 'color' => 'red', 'order_index' => 7],
        ];

        foreach ($stages as $stage) {
            DealStage::updateOrCreate(
                ['name' => $stage['name']],
                ['color' => $stage['color'], 'order_index' => $stage['order_index']]
            );
        }
    }

    public function down(): void
    {
        // Keep stages
    }
};
