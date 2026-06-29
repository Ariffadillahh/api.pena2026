<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_waves', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('competition_id')
                ->constrained('competitions')
                ->cascadeOnDelete();

            $table->string('wave_name');

            $table->decimal('price', 12, 2);

            $table->date('start_date');
            $table->date('end_date');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_waves');
    }
};
