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
    Schema::table('tournament_details', function (Blueprint $table) {
      $table->integer('reentry')->default(0);
      $table->integer('reentry_bounty')->default(0);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('tournament_details', function (Blueprint $table) {
      $table->dropColumn('reentry');
      $table->dropColumn('reentry_bounty');
    });
  }
};
