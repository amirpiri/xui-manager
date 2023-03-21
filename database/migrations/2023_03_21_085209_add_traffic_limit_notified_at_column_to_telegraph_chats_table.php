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
        Schema::table('telegraph_chats', function (Blueprint $table) {
            $table->timestamp('traffic_limit_notified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telegraph_chats', function (Blueprint $table) {
            $table->dropColumn('traffic_limit_notified_at');
        });
    }
};
