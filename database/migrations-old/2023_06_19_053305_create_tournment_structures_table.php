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
        Schema::create('tournament_structures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id')->index();
            $table->integer('order')->nullable();
            $table->integer('sb')->nullable();
            $table->integer('bb')->nullable();
            $table->integer('ante')->nullable();
            $table->integer('duration')->nullable();
            $table->tinyInteger('isbreak')->default(0);
            $table->string('breaktitle')->nullable();
            $table->timestamps();

            $table->foreign('tournament_id')
            ->references('id')
            ->on('tournaments')
            ->onDelete('cascade')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_structures');
    }
};
