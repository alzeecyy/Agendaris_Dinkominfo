<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nip',
        'jabatan',
        'bidang_id',
        'role',
        'password',
        'must_change_password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'user_id');
    }

    // Role helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSekretarisMaster(): bool
    {
        return $this->role === 'sekretaris_master';
    }

    public function isKetuaMaster(): bool
    {
        return $this->role === 'ketua_master';
    }

    public function isSekretarisBidang(): bool
    {
        return $this->role === 'sekretaris_bidang';
    }

    public function isKetuaBidang(): bool
    {
        return $this->role === 'ketua_bidang';
    }

    public function isKetua(): bool
    {
        return $this->role === 'ketua_master' || $this->role === 'ketua_bidang';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isSekretariat(): bool
    {
        if (!$this->bidang_id) {
            return false;
        }

        return $this->bidang && (
            strcasecmp($this->bidang->singkatan, 'sekretariat') === 0 || 
            strcasecmp($this->bidang->nama, 'sekretariat') === 0
        );
    }

    /**
     * Checks if this user has access to view/participate in an agenda.
     */
    public function hasAccessToAgenda(Agenda $agenda): bool
    {
        if ($this->isAdmin()) {
            return false; // Admins don't participate in agendas or view their content
        }

        if ($this->isSekretarisMaster() || $this->isKetuaMaster() || $this->isSekretariat()) {
            return true; // Masters & Sekretariat staff can view all agendas across all bidangs
        }

        // If specific meeting_participants are saved for this agenda, check if user is invited
        if ($agenda->participants()->exists()) {
            return $agenda->participants()->where('users.id', $this->id)->exists();
        }

        // For Bidang roles & Staff fallback:
        $hakAkses = $agenda->hak_akses ?? [];
        
        if (in_array('semua_orang', $hakAkses)) {
            return true;
        }

        return in_array((string)$this->bidang_id, array_map('strval', $hakAkses));
    }

    /**
     * Checks if this user is a secretary with management rights for an agenda.
     */
    public function isSecretaryOfAgenda(Agenda $agenda): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        // Master secretary or Sekretariat staff has full management rights for any agenda (backup Sekdin)
        if ($this->isSekretarisMaster() || $this->isSekretariat()) {
            return true;
        }

        // The secretary user who created this agenda has rights
        if ($this->id == $agenda->sekretaris_id && ($this->isSekretarisMaster() || $this->isSekretarisBidang())) {
            return true;
        }

        // Bidang secretary has rights if agenda is for 'semua_orang' or includes their bidang_id
        if ($this->isSekretarisBidang()) {
            $hakAkses = $agenda->hak_akses ?? [];
            if (in_array('semua_orang', $hakAkses) || in_array((string)$this->bidang_id, array_map('strval', $hakAkses))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if this user is an approver (Ketua) for an agenda's notulensi.
     * Rule:
     * - If agenda includes 1 Dinkominfo (semua_orang / multiple bidangs) -> ONLY Kepala Dinas (ketua_master) can approve.
     * - If agenda is for a single specific Bidang (e.g. Aptika only) -> Kepala Bidang (ketua_bidang) of that specific bidang approves.
     */
    public function isApproverOfAgenda(Agenda $agenda): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        $hakAkses = $agenda->hak_akses ?? [];
        $isLintasDinas = in_array('semua_orang', $hakAkses) || count($hakAkses) > 1 || count($hakAkses) === 0;

        if ($isLintasDinas) {
            return $this->isKetuaMaster();
        }

        if ($this->isKetuaBidang()) {
            $singleBidangId = $hakAkses[0] ?? null;
            if ($singleBidangId && (string)$this->bidang_id === (string)$singleBidangId) {
                return true;
            }
        }

        if ($this->isKetuaMaster()) {
            return true;
        }

        return false;
    }
}
