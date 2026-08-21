<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->text('score_board')->nullable()->change();
            $table->text('notes')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->string('score_board')->nullable()->change();
            $table->string('notes')->nullable()->change();
        });
    }
};
