<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('team_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();

            $table->boolean('day_1_status')->default(false);
            $table->timestamp('day_1_scanned_at')->nullable();

            $table->boolean('day_2_status')->default(false);
            $table->timestamp('day_2_scanned_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('team_attendances');
    }
};
