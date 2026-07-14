<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\Presensi;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    /**
     * Submit attendance status (self-attendance).
     */
    public function absen(Request $request, Agenda $agenda)
    {
        $user = Auth::user();

        // Validate agenda and access
        if (!$agenda->butuh_presensi) {
            return back()->with('error', 'Presensi digital dinonaktifkan untuk agenda ini.');
        }

        if (!$user->hasAccessToAgenda($agenda)) {
            return back()->with('error', 'Anda tidak memiliki akses ke agenda ini.');
        }

        $validated = $request->validate([
            'status' => 'required|in:hadir,izin,sakit',
        ]);

        // Check if already checked in
        $existing = Presensi::where('agenda_id', $agenda->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Presensi Anda sudah tercatat dan tidak dapat diubah secara mandiri.');
        }

        // Save presence
        Presensi::create([
            'agenda_id' => $agenda->id,
            'user_id' => $user->id,
            'status' => $validated['status'],
        ]);

        $statusLabels = [
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
        ];

        return back()->with('success', 'Presensi Anda tercatat sebagai: ' . $statusLabels[$validated['status']] . '.');
    }

    /**
     * Secretary manual correction of an employee's attendance.
     */
    public function koreksi(Request $request, Agenda $agenda)
    {
        $user = Auth::user();
        $hakAkses = $agenda->hak_akses;

        // Check if user has secretary access to this agenda
        $isSecretaryOfAgenda = $user->isSekretarisMaster() || 
            ($user->isSekretarisBidang() && in_array((string)$user->bidang_id, array_map('strval', $hakAkses)));

        if (!$isSecretaryOfAgenda) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk mengoreksi presensi.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,Belum Absen',
        ]);

        $employee = \App\Models\User::find($validated['user_id']);
        
        // Ensure the corrected employee is within the allowed bidang of the agenda
        if (!$employee->hasAccessToAgenda($agenda)) {
            return back()->with('error', 'Pegawai bersangkutan tidak memiliki akses ke agenda ini.');
        }

        if ($validated['status'] === 'Belum Absen') {
            // Delete presence record
            Presensi::where('agenda_id', $agenda->id)
                ->where('user_id', $employee->id)
                ->delete();
                
            return back()->with('success', 'Status presensi ' . $employee->name . ' berhasil direset.');
        } else {
            // Save or update presence
            Presensi::updateOrCreate([
                'agenda_id' => $agenda->id,
                'user_id' => $employee->id,
            ], [
                'status' => $validated['status'],
            ]);

            $statusLabels = [
                'hadir' => 'Hadir',
                'izin' => 'Izin',
                'sakit' => 'Sakit',
            ];

            return back()->with('success', 'Status presensi ' . $employee->name . ' diubah menjadi: ' . $statusLabels[$validated['status']] . '.');
        }
    }
}
