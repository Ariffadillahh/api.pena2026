<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('score_board')->nullable()->after('status');
        });

        Schema::table('juri_assignments', function (Blueprint $table) {
            $table->longText('signature')->nullable()->after('competition_id')->comment('Base64 image atau path ttd juri');
        });
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('score_board');
        });
        Schema::table('jury_assignments', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};
