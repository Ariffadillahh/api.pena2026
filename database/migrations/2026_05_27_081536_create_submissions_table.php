<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained('teams')->cascadeOnDelete();

            $table->string('file_path')->nullable();
            $table->string('original_filename')->nullable();
            $table->string('gdrive_link')->nullable();

            $table->timestamps();
        });

        if (!Schema::hasColumn('teams', 'karya_uploaded')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->boolean('karya_uploaded')->default(false)->after('status');
            });
        }
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('karya_uploaded');
        });
        Schema::dropIfExists('submissions');
    }
};
