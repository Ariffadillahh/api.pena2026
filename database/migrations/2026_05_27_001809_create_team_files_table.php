<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained()->cascadeOnDelete();

            $table->string('file_path');
            $table->string('original_name'); 

            $table->enum('type', [
                'bukti_pembayaran', 
                'bukti_follow',
                'bukti_twibbon',
                'surat_pernyataan',
                'karya_tulis',
                'poster',
                'video_presentasi'
            ]);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_files');
    }
};
