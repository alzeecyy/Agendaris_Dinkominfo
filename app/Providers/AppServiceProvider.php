<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID', 'indonesian', 'id');

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('users') && \Illuminate\Support\Facades\Schema::hasTable('bidangs')) {
                $subbagUmum = \App\Models\Bidang::where('nama', 'like', '%Subbag Umum%')->orWhere('singkatan', 'like', '%Subbag Umum%')->first();
                $subbagKeuangan = \App\Models\Bidang::where('nama', 'like', '%Subbag Keuangan%')->orWhere('singkatan', 'like', '%Subbag Keuangan%')->first();
                $subbagPerencanaan = \App\Models\Bidang::where('nama', 'like', '%Subbag Perencanaan%')->orWhere('singkatan', 'like', '%Subbag Perencanaan%')->first();

                if ($subbagUmum) {
                    \App\Models\User::where(function($q){ $q->where('jabatan', 'like', '%Subbag Umum%')->orWhere('jabatan', 'like', '%Kasubag Umum%'); })
                        ->where('bidang_id', '!=', $subbagUmum->id)
                        ->update(['bidang_id' => $subbagUmum->id]);
                }
                if ($subbagKeuangan) {
                    \App\Models\User::where(function($q){ $q->where('jabatan', 'like', '%Subbag Keuangan%')->orWhere('jabatan', 'like', '%Kasubag Keuangan%'); })
                        ->where('bidang_id', '!=', $subbagKeuangan->id)
                        ->update(['bidang_id' => $subbagKeuangan->id]);
                }
                if ($subbagPerencanaan) {
                    \App\Models\User::where(function($q){ $q->where('jabatan', 'like', '%Subbag Perencanaan%')->orWhere('jabatan', 'like', '%Kasubag Perencanaan%'); })
                        ->where('bidang_id', '!=', $subbagPerencanaan->id)
                        ->update(['bidang_id' => $subbagPerencanaan->id]);
                }
            }
        } catch (\Throwable $e) {
            // Silence exceptions during early installation / migrations
        }
    }
}
