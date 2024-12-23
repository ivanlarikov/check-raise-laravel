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
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('credits')->default(0)->nullable();
            $table->integer('buyuinlimit')->default(200)->nullable();
            $table->integer('maxnumberoftournament')->default(20)->nullable();
            $table->integer('maxnumberofpremium')->default(2)->nullable();
            $table->integer('latearrivaldelay')->default(3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            //
        });
    }
};
