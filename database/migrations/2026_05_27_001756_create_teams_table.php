<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignUuid('competition_id')->constrained()->cascadeOnDelete();
            $table->uuid('wave_id')->nullable();

            $table->foreign('wave_id')
                ->references('id')
                ->on('registration_waves')
                ->onDelete('set null');

            $table->string('name');
            $table->string('institution');
            $table->string('payment_method')->nullable();

            $table->enum('payment_status', [
                'menunggu_verifikasi',
                'valid',
                'ditolak'
            ])->default('menunggu_verifikasi');

            $table->enum('status', [
                'draft',
                'menunggu_penilaian',
                'dinilai',
                'lolos_top_10',
                'tidak_lolos'
            ])->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
