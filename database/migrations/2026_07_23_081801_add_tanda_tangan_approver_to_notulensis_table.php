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
            $table->text('tanda_tangan_approver')->nullable()->after('approver_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notulensis', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan_approver');
        });
    }
};
