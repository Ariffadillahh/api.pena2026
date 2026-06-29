<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('competition_id')
                ->constrained('competitions')
                ->cascadeOnDelete();

            $table->text('requirement');

            $table->integer('order_number')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_requirements');
    }
};
