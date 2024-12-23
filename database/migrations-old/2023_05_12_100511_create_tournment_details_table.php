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
        Schema::create('tournament_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id')->index();
            $table->string("type",10)->nullable();
            $table->tinyInteger('isshorthanded')->default(0);
            $table->string("dealertype",15)->nullable();
            $table->integer('buyin')->nullable();
            $table->integer('bounty')->nullable();
            $table->integer('rake')->nullable();
            $table->integer('maxreentries')->nullable();


            $table->integer('startingstack')->nullable();
            $table->string('level_duration')->nullable();
            $table->integer('maxplayers')->nullable();
            $table->integer('reservedplayers')->nullable();

            $table->dateTime("startday")->nullable();
            $table->dateTime("lastday")->nullable();

            $table->string('lateregformat',25)->nullable();
            $table->time('lateregtime')->nullable();
            $table->integer('latereground')->nullable();

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
        Schema::dropIfExists('tournment_details');
    }
};
