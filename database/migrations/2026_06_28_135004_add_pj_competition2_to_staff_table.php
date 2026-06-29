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
        Schema::table('staff', function (Blueprint $table) {
            $table->uuid('pj_competition_id_2')->nullable()->after('pj_category');
            $table->string('pj_category_2')->nullable()->after('pj_competition_id_2');

            $table->foreign('pj_competition_id_2')->references('id')->on('competitions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['pj_competition_id_2', 'pj_category_2']);
        });
    }
};
