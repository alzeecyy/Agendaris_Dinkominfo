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
        Schema::table('notulensis', function (Blueprint $table) {
            $table->string('pembahasan_title')->nullable()->default('Poin Pembahasan Rapat');
            $table->string('keputusan_title')->nullable()->default('Daftar Keputusan Rapat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notulensis', function (Blueprint $table) {
            $table->dropColumn(['pembahasan_title', 'keputusan_title']);
        });
    }
};
