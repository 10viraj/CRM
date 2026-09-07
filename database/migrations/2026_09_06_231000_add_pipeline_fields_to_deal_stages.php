<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deal_stages', function (Blueprint $table) {
            if (!Schema::hasColumn('deal_stages', 'probability')) {
                $table->integer('probability')->nullable()->default(10);
            }
            if (!Schema::hasColumn('deal_stages', 'is_won')) {
                $table->boolean('is_won')->default(false);
            }
            if (!Schema::hasColumn('deal_stages', 'is_lost')) {
                $table->boolean('is_lost')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deal_stages', function (Blueprint $table) {
            $table->dropColumn(['probability', 'is_won', 'is_lost']);
        });
    }
};
