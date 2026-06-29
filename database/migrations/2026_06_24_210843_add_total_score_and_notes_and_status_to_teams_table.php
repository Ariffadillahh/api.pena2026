<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->decimal('total_score', 8, 2)->default(0)->after('karya_uploaded');
            $table->text('notes')->nullable()->after('total_score');
        });

        DB::statement("
        ALTER TABLE teams
        MODIFY status ENUM(
            'draft',
            'menunggu_konfirmasi',
            'menunggu_penilaian',
            'dinilai',
            'lolos_top_10',
            'tidak_lolos',
            'JUARA 1',
            'JUARA 2',
            'JUARA 3'
        )
    ");
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            //
        });
    }
};
