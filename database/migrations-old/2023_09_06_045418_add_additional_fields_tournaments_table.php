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
        Schema::table('tournament_details', function (Blueprint $table) {
            //
            $table->tinyInteger('ischampionship')->default(0);
            $table->datetime('bounusdeadline')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_details', function (Blueprint $table) {
            //
        });
    }
};
