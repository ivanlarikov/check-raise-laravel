<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('settings', function (Blueprint $table) {
      $table->integer('bottom_desktop_start')->default(10);
      $table->integer('bottom_mobile_start')->default(5);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('settings', function (Blueprint $table) {
      $table->dropColumn('bottom_desktop_start');
      $table->dropColumn('bottom_mobile_start');
    });
  }
};
