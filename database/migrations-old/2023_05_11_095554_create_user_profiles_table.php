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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('firstname',255)->nullable();
            $table->string('lastname',255)->nullable();
            $table->date('dob')->nullable();
            $table->string('street',255)->nullable();
            $table->string('nickname',255)->nullable();
            $table->string('language',10)->nullable();
            $table->string('city')->nullable();
            $table->string('zipcode',10)->nullable();
            $table->string('displayoption',10)->nullable();
            $table->string('phonecountry',10)->nullable();
            $table->string('phonecode',10)->nullable();
            $table->string('phonenumber',20)->nullable();
            $table->timestamps();

            $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade')
            ->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
