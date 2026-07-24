<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'deskripsi',
        'kategori',
        'hak_akses',
        'butuh_presensi',
        'nomor_surat_dasar',
        'sekretaris_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'hak_akses' => 'array',
            'butuh_presensi' => 'boolean',
        ];
    }

    public function sekretaris(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sekretaris_id');
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'agenda_id');
    }

    public function externalParticipants(): HasMany
    {
        return $this->hasMany(AgendaExternalParticipant::class, 'agenda_id');
    }

    public function notulensi(): HasOne
    {
        return $this->hasOne(Notulensi::class, 'agenda_id');
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_participants', 'agenda_id', 'user_id')->withTimestamps();
    }

    /**
     * Get internal participants of this meeting.
     * Returns meeting_participants users if defined (merged with any users who have recorded presensi history);
     * falls back to all active bidang users if legacy agenda.
     */
    public function getInternalParticipants()
    {
        if ($this->participants()->exists()) {
            $invited = $this->participants()->where('active', true)->where('role', '!=', 'admin')->get();
            $attendedUserIds = Presensi::where('agenda_id', $this->id)
                ->whereIn('status', ['hadir', 'izin', 'sakit', 'alfa'])
                ->pluck('user_id');

            if ($attendedUserIds->isNotEmpty()) {
                $attendedUsers = User::whereIn('id', $attendedUserIds)
                    ->where('active', true)
                    ->where('role', '!=', 'admin')
                    ->get();
                $merged = $invited->concat($attendedUsers)->unique('id')->values();
                return $merged->sortBy('name')->values();
            }

            return $invited->sortBy('name')->values();
        }

        $hakAkses = $this->hak_akses ?? [];
        $query = User::where('role', '!=', 'admin')->where('active', true);
        if (!in_array('semua_orang', $hakAkses)) {
            $query->whereIn('bidang_id', $hakAkses);
        }
        return $query->orderBy('name')->get();
    }

    /**
     * Check if presensi period has not started yet (before tanggal & jam_mulai).
     */
    public function isPresensiNotStarted(): bool
    {
        if (!$this->tanggal || !$this->jam_mulai) {
            return false;
        }

        $startDateTime = \Carbon\Carbon::parse($this->tanggal->toDateString() . ' ' . $this->jam_mulai, 'Asia/Jakarta');
        return now()->setTimezone('Asia/Jakarta')->lessThan($startDateTime);
    }

    /**
     * Check if the meeting is currently in grace period for attendance (within 1 hour after jam_selesai).
     */
    public function isPresensiInGracePeriod(): bool
    {
        if (!$this->tanggal || !$this->jam_selesai) {
            return false;
        }

        $endDateTime = \Carbon\Carbon::parse($this->tanggal->toDateString() . ' ' . $this->jam_selesai, 'Asia/Jakarta');
        $limitDateTime = $endDateTime->copy()->addHour();
        $now = now()->setTimezone('Asia/Jakarta');

        return $now->greaterThan($endDateTime) && $now->lessThanOrEqualTo($limitDateTime);
    }

    /**
     * Check if the self-attendance filling period has expired (more than 1 hour after jam_selesai).
     */
    public function isPresensiExpired(): bool
    {
        if (!$this->tanggal || !$this->jam_selesai) {
            return false;
        }

        $endDateTime = \Carbon\Carbon::parse($this->tanggal->toDateString() . ' ' . $this->jam_selesai, 'Asia/Jakarta');
        $limitDateTime = $endDateTime->copy()->addHour();

        return now()->setTimezone('Asia/Jakarta')->greaterThan($limitDateTime);
    }

    /**
     * Check if attendance can currently be submitted by participant.
     */
    public function canPresensiBeFilled(): bool
    {
        return !$this->isPresensiNotStarted() && !$this->isPresensiExpired();
    }
}
