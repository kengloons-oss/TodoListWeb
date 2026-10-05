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
        Schema::table('task_reminders', function (Blueprint $table) {
            $table->dateTime('shown_at')->nullable()->after('sent_at');
            $table->index(['shown_at', 'remind_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_reminders', function (Blueprint $table) {
            $table->dropIndex(['shown_at', 'remind_at']);
            $table->dropColumn('shown_at');
        });
    }
};
