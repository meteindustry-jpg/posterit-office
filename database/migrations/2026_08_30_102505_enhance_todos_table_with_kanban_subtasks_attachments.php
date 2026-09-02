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
        Schema::table('todos', function (Blueprint $table) {
            $table->enum('status', ['todo', 'in_progress', 'in_review', 'completed'])->default('todo')->after('priority');
            $table->json('subtasks')->nullable()->after('description');
            $table->string('reference_url')->nullable()->after('subtasks');
            $table->foreignId('work_entry_id')->nullable()->constrained('daily_work_entries')->nullOnDelete()->after('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            $table->dropForeign(['work_entry_id']);
            $table->dropColumn(['status', 'subtasks', 'reference_url', 'work_entry_id']);
        });
    }
};
