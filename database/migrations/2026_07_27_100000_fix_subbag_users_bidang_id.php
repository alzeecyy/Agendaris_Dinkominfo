<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Bidang;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $subbagUmum = Bidang::where('nama', 'like', '%Subbag Umum%')->orWhere('singkatan', 'like', '%Subbag Umum%')->first();
        $subbagKeuangan = Bidang::where('nama', 'like', '%Subbag Keuangan%')->orWhere('singkatan', 'like', '%Subbag Keuangan%')->first();
        $subbagPerencanaan = Bidang::where('nama', 'like', '%Subbag Perencanaan%')->orWhere('singkatan', 'like', '%Subbag Perencanaan%')->first();

        if ($subbagUmum) {
            User::where('jabatan', 'like', '%Subbag Umum%')->update(['bidang_id' => $subbagUmum->id]);
        }
        if ($subbagKeuangan) {
            User::where('jabatan', 'like', '%Subbag Keuangan%')->update(['bidang_id' => $subbagKeuangan->id]);
        }
        if ($subbagPerencanaan) {
            User::where('jabatan', 'like', '%Subbag Perencanaan%')->update(['bidang_id' => $subbagPerencanaan->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed for rollback
    }
};
