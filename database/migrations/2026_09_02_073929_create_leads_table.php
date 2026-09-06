<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->foreignId('lead_source_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_status_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('score')->default(0);
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
