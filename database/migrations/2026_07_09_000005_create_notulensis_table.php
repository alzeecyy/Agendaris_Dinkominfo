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
        Schema::create('notulensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_id')->unique()->constrained('agendas')->cascadeOnDelete();
            $table->string('audio_path')->nullable();
            $table->string('audio_name')->nullable();
            $table->longText('transkrip_raw')->nullable();
            $table->text('ringkasan')->nullable();
            $table->text('pembahasan')->nullable();
            $table->text('keputusan')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->string('status')->default('draft'); // 'draft', 'menunggu_review', 'disahkan'
            $table->text('catatan_revisi')->nullable();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_edited_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notulensis');
    }
};
