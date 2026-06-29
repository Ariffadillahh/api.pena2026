<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        DB::statement("ALTER TABLE teams MODIFY status ENUM('draft', 'menunggu_konfirmasi', 'menunggu_penilaian', 'dinilai', 'lolos_top_10', 'tidak_lolos') DEFAULT 'draft'");
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            //
        });
    }
};
