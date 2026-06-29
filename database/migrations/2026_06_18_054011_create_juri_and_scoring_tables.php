<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('juri_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('assessment_criteria', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('name');
            $table->integer('weight');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignUuid('juri_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('criteria_id')->constrained('assessment_criteria')->cascadeOnDelete();
            $table->integer('score');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'juri_id', 'criteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
        Schema::dropIfExists('assessment_criteria');
        Schema::dropIfExists('juri_assignments');
    }
};
