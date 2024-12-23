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
      $table->dropColumn('paypal_link');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('settings', function (Blueprint $table) {
      $table->string('paypal_link')->default(true);
    });
  }
};
