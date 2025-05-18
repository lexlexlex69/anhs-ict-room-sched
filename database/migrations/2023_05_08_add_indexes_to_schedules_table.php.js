<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('teacher_id');
            $table->index('room_id');
            $table->index('status');
            $table->index('date');
            $table->index(['date', 'start_time', 'end_time']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('user_type');
            $table->index('subject');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->index('subject');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['teacher_id']);
            $table->dropIndex(['room_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['date']);
            $table->dropIndex(['date', 'start_time', 'end_time']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_type']);
            $table->dropIndex(['subject']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['subject']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read']);
        });
    }
}