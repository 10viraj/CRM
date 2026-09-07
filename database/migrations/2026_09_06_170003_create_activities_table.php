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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type'); // call, email, meeting, note, task, status_change, sms
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('activity_date')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('status')->default('completed'); // completed, pending, scheduled
            $table->nullableMorphs('subject'); // Lead, Deal, Contact, Company, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
