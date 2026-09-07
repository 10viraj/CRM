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
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('company')->constrained('companies')->nullOnDelete();
            }
        });

        Schema::table('deals', function (Blueprint $table) {
            if (!Schema::hasColumn('deals', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('lead_id')->constrained('companies')->nullOnDelete();
            }
            if (!Schema::hasColumn('deals', 'contact_id')) {
                $table->foreignId('contact_id')->nullable()->after('company_id')->constrained('contacts')->nullOnDelete();
            }
            if (!Schema::hasColumn('deals', 'probability')) {
                $table->integer('probability')->default(100)->after('value');
            }
            if (!Schema::hasColumn('deals', 'status')) {
                $table->string('status')->default('open')->after('deal_stage_id');
            }
            if (!Schema::hasColumn('deals', 'notes')) {
                $table->text('notes')->nullable()->after('close_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['contact_id']);
            $table->dropColumn(['company_id', 'contact_id', 'probability', 'status', 'notes']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn(['company_id']);
        });
    }
};
