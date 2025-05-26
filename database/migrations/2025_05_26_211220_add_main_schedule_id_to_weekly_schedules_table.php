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
        Schema::table('weekly_schedules', function (Blueprint $table) {
            // Add foreign key column, nullable so existing weekly schedules won't break immediately
            $table->foreignId('main_schedule_id')->nullable()->constrained('main_schedules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_schedules', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['main_schedule_id']);
            $table->dropColumn('main_schedule_id');
        });
    }
};
