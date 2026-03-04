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
        Schema::table('signups', function (Blueprint $table) {
            $table->dateTime('session_start')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable()->index();
            $table->unique(['id_user', 'id_class', 'session_start'], 'signups_unique_user_class_session');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signups', function (Blueprint $table) {
            $table->dropUnique('signups_unique_user_class_session');
            $table->dropColumn(['session_start', 'cancelled_at']);
        });
    }
};
