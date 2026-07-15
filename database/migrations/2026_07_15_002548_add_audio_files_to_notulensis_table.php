<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notulensis', function (Blueprint $table) {
            $table->json('audio_files')->nullable();
        });

        // Migrate existing columns: if audio_path exists, save it as the first item in audio_files array
        $notulensis = DB::table('notulensis')->whereNotNull('audio_path')->get();
        foreach ($notulensis as $n) {
            if ($n->audio_path) {
                $files = [
                    [
                        'name' => $n->audio_name ?? basename($n->audio_path),
                        'path' => $n->audio_path
                    ]
                ];
                DB::table('notulensis')->where('id', $n->id)->update([
                    'audio_files' => json_encode($files)
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notulensis', function (Blueprint $table) {
            $table->dropColumn('audio_files');
        });
    }
};
