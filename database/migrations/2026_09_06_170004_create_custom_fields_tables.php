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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // e.g. App\Models\Lead, App\Models\Deal, etc.
            $table->string('name'); // slug / identifier
            $table->string('label'); // UI display label
            $table->string('field_type')->default('text'); // text, number, select, date, boolean, textarea
            $table->json('options')->nullable(); // For select / dropdown options
            $table->boolean('is_required')->default(false);
            $table->text('default_value')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained('custom_fields')->cascadeOnDelete();
            $table->morphs('custom_fieldable'); // Lead, Deal, Contact, Company, etc.
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
