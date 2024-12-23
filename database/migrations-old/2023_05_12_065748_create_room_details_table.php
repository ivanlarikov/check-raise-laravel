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
        Schema::create('room_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_id')->index();
            $table->string('logo',255)->nullable();
            $table->string('street',255)->nullable();
            $table->string('town',255)->nullable();
            $table->string('canton',10)->nullable();
            $table->string('phone',20)->nullable();
            $table->string('phonecode',10)->nullable();
            $table->string('phonecountry',10)->nullable();
            $table->string('website',255)->nullable();
            $table->string('contact',255)->nullable();
            $table->timestamps();

            $table->foreign('room_id')
            ->references('id')
            ->on('rooms')
            ->onDelete('cascade')
            ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_details');
    }
};
