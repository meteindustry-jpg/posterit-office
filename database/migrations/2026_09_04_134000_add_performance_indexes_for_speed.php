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
        Schema::table('daily_work_entries', function (Blueprint $table) {
            $table->index('date');
            $table->index(['date', 'employee_id']);
            $table->index(['date', 'work_category_id']);
        });

        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->index('date');
            $table->index(['date', 'status']);
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->index('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_work_entries', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['date', 'employee_id']);
            $table->dropIndex(['date', 'work_category_id']);
        });

        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['date', 'status']);
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropIndex(['key']);
        });
    }
};
