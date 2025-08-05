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
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('age_group'); // Changed to integer since age_group is a number input
            $table->foreignId('org_type')->constrained('organisation_types')->onDelete('cascade'); // Added for org_type
            $table->foreignId('org')->constrained('organisations')->onDelete('cascade'); // Added for org
            $table->string('country');
            $table->string('city');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('time_allowed');
            $table->string('coach_name');
            $table->string('image')->nullable();
            $table->longText('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('genz', ['motherfits', 'fatherfits']); // Added for genz field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
