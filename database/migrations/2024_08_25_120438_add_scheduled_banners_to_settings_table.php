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
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('next_banner_top_enabled')->default(false);
            $table->string('next_banner_top_image')->nullable();
            $table->string('next_banner_top_link')->nullable();
            $table->dateTime('next_banner_top_start_date')->nullable()->default(null);

            $table->boolean('next_banner_bottom_enabled')->default(false);
            $table->string('next_banner_bottom_image')->nullable();
            $table->string('next_banner_bottom_link')->nullable();
            $table->dateTime('next_banner_bottom_start_date')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('next_banner_top_enabled');
            $table->dropColumn('next_banner_top_image');
            $table->dropColumn('next_banner_top_link');
            $table->dropColumn('next_banner_top_start_date');
            $table->dropColumn('next_banner_bottom_enabled');
            $table->dropColumn('next_banner_bottom_image');
            $table->dropColumn('next_banner_bottom_link');
            $table->dropColumn('next_banner_bottom_start_date');
        });
    }
};
