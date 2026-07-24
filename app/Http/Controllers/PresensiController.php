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

        if ($agenda->isPresensiNotStarted()) {
            $jamMulai = substr($agenda->jam_mulai, 0, 5);
            $tanggalStr = $agenda->tanggal ? $agenda->tanggal->translatedFormat('d F Y') : '';
            return back()->with('error', "Absensi belum dibuka. Absensi baru dapat diisi saat waktu rapat dimulai ({$tanggalStr} jam {$jamMulai} WIB).");
        }

        if ($agenda->isPresensiExpired()) {
            return back()->with('error', 'Absensi telah ditutup. Batas waktu toleransi presensi mandiri (maksimal 1 jam setelah rapat selesai) telah berakhir.');
        }

        $validated = $request->validate([
            'status' => 'required|in:hadir,izin,sakit',
            'keterangan' => 'nullable|string|max:500',
            'signature' => 'required_if:status,hadir|nullable|string',
        ]);

        // Check if already checked in
        $existing = Presensi::where('agenda_id', $agenda->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Presensi Anda sudah tercatat dan tidak dapat diubah secara mandiri.');
        }

        // Decode and save signature image (only for hadir status)
        $signaturePath = null;
        if ($validated['status'] === 'hadir') {
            $dataUrl = $validated['signature'] ?? null;
            if ($dataUrl && preg_match('/^data:image\/(\w+);base64,/', $dataUrl, $type)) {
                $base64Data = substr($dataUrl, strpos($dataUrl, ',') + 1);
                $ext = strtolower($type[1]); // png, jpg, etc.

                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $decoded = base64_decode($base64Data);

                    if ($decoded !== false) {
                        $filename = 'sig_' . $agenda->id . '_' . $user->id . '_' . time() . '.' . $ext;
                        $path = 'presensi/signatures/' . $filename;

                        // Save to public storage disk
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $decoded);
                        $signaturePath = $path;
                    }
                }
            }

            if (!$signaturePath) {
                return back()->withErrors(['signature' => 'Tanda tangan digital wajib diisi dengan benar.'])->withInput();
            }
        }

        // Save presence safely against double-click race conditions
        try {
            Presensi::create([
                'agenda_id' => $agenda->id,
                'user_id' => $user->id,
                'status' => $validated['status'],
                'tanda_tangan' => $signaturePath,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Presensi Anda telah tercatat.');
        }

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

        if (!$user->isSecretaryOfAgenda($agenda)) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk mengoreksi presensi.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:hadir,izin,sakit,alfa,Belum Absen',
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
            $dataToUpdate = [
                'status' => $validated['status'],
            ];
            if ($validated['status'] !== 'hadir') {
                $dataToUpdate['tanda_tangan'] = null;
            }

            Presensi::updateOrCreate([
                'agenda_id' => $agenda->id,
                'user_id' => $employee->id,
            ], $dataToUpdate);

            $statusLabels = [
                'hadir' => 'Hadir',
                'izin' => 'Izin',
                'sakit' => 'Sakit',
                'alfa' => 'Alfa',
            ];

            return back()->with('success', 'Status presensi ' . $employee->name . ' diubah menjadi: ' . $statusLabels[$validated['status']] . '.');
        }
    }
}
