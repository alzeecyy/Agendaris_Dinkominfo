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

    /**
     * Get effective bidang_id attribute, resolving Subbag & Kasubag users to their respective Subbag Bidang ID.
     */
    public function getBidangIdAttribute($value)
    {
        if ($value && !empty($this->attributes['jabatan'])) {
            $jabatanLower = strtolower($this->attributes['jabatan']);
            if (str_contains($jabatanLower, 'subbag') || str_contains($jabatanLower, 'kasubag')) {
                $subbag = null;
                if (str_contains($jabatanLower, 'umum')) {
                    $subbag = Bidang::where('nama', 'like', '%Subbag Umum%')->orWhere('singkatan', 'like', '%Subbag Umum%')->first();
                } elseif (str_contains($jabatanLower, 'keuangan')) {
                    $subbag = Bidang::where('nama', 'like', '%Subbag Keuangan%')->orWhere('singkatan', 'like', '%Subbag Keuangan%')->first();
                } elseif (str_contains($jabatanLower, 'perencanaan')) {
                    $subbag = Bidang::where('nama', 'like', '%Subbag Perencanaan%')->orWhere('singkatan', 'like', '%Subbag Perencanaan%')->first();
                }
                if ($subbag) {
                    return $subbag->id;
                }
            }
        }
        return $value;
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

        $bidang = $this->bidang ?: Bidang::find($this->bidang_id);
        return $bidang && (
            strcasecmp($bidang->singkatan, 'sekretariat') === 0 || 
            strcasecmp($bidang->nama, 'sekretariat') === 0 ||
            $bidang->isSubbagian()
        );
    }

    public function isSekretariatScope(): bool
    {
        return $this->isSekretarisMaster() || $this->isKetuaMaster() || $this->isSekretariat();
    }

    /**
     * Get user role display label nicely formatted for header, badges, and profile.
     */
    public function getRoleLabelAttribute(): string
    {
        if ($this->isAdmin()) {
            return 'Administrator';
        }
        if ($this->isSekretarisMaster()) {
            return 'Sekretaris Dinas';
        }
        if ($this->isKetuaMaster()) {
            return 'Kepala Dinas';
        }

        $bid = $this->bidang;
        $bidName = $bid ? ($bid->singkatan ?? $bid->nama) : '';
        $isSubbag = $bid ? (str_contains(strtolower($bid->nama), 'subbag') || str_contains(strtolower($bid->singkatan), 'subbag')) : false;

        if (!$isSubbag && str_contains(strtolower((string)$this->jabatan), 'subbag')) {
            $isSubbag = true;
        }

        if ($this->isSekretarisBidang()) {
            if ($isSubbag) {
                if ($bidName && str_contains(strtolower($bidName), 'subbag')) {
                    return 'Admin ' . $bidName;
                }
                return $this->jabatan ?: ($bidName ? 'Admin ' . $bidName : 'Admin Subbag');
            }
            return $bidName ? "Admin Bidang {$bidName}" : 'Admin Bidang';
        }

        if ($this->isKetuaBidang()) {
            if ($isSubbag) {
                return $this->jabatan ?: ($bidName ? "Kasubag {$bidName}" : 'Kasubag');
            }
            return $bidName ? "Ketua Bidang {$bidName}" : 'Ketua Bidang';
        }

        if ($this->isStaff()) {
            if ($isSubbag) {
                return "Staff " . ($bidName ?: 'Subbag');
            }
            return $bidName ? "Staff {$bidName}" : 'Staff Pegawai';
        }

        return ucfirst(str_replace('_', ' ', $this->role));
    }

    /**
     * Checks if this user can view the Agenda Hari Ini (TV / Monitoring Board) page.
     * Allowed ONLY for Admins & Secretaries (Admin Master, Admin Bidang/Subbag, Sekretaris Dinas).
     */
    public function canViewAgendaToday(): bool
    {
        return $this->isAdmin()
            || $this->isSekretarisMaster() 
            || $this->isSekretarisBidang();
    }

    /**
     * Checks if this user has access to view/participate in an agenda.
     */
    public function hasAccessToAgenda(Agenda $agenda): bool
    {
        if ($this->isAdmin()) {
            return false; // Admins don't participate in agendas or view their content
        }

        // Direct creator of agenda always has access
        if ((string)$this->id === (string)$agenda->sekretaris_id) {
            return true;
        }

        $hakAkses = $agenda->hak_akses ?? [];

        // Rapat Lintas Dinas / Semua Orang is accessible to everyone including Kadis & Sekdin
        if (in_array('semua_orang', $hakAkses)) {
            return true;
        }

        // Sekretaris Dinas (Sekdin) has management access across agency agendas
        if ($this->isSekretarisMaster()) {
            return true;
        }

        // Kepala Dinas (ketua_master) has access IF:
        // 1. Agenda is Lintas Dinas (semua_orang)
        // 2. Or if meeting_participants exist, Kepala Dinas is explicitly included
        // 3. Or if no specific meeting_participants exist, agenda was created by Sekretariat Dinas
        if ($this->isKetuaMaster()) {
            if (in_array('semua_orang', $hakAkses)) {
                return true;
            }
            if ($agenda->participants()->exists()) {
                return $agenda->participants()->where('users.id', $this->id)->exists();
            }
            $creator = $agenda->sekretaris;
            return $creator && ($creator->isSekretarisMaster() || ($creator->bidang && (strcasecmp($creator->bidang->singkatan, 'sekretariat') === 0 || strcasecmp($creator->bidang->nama, 'sekretariat') === 0)));
        }

        // If specific meeting_participants are saved for this agenda, check if user is explicitly invited
        if ($agenda->participants()->exists()) {
            return $agenda->participants()->where('users.id', $this->id)->exists();
        }

        // Check if user's bidang_id is explicitly in target hak_akses
        if ($this->bidang_id && in_array((string)$this->bidang_id, array_map('strval', $hakAkses))) {
            return true;
        }

        // Check if agenda belongs to user's same bidang
        if ($this->bidang_id && $agenda->sekretaris && (string)$agenda->sekretaris->bidang_id === (string)$this->bidang_id) {
            return true;
        }

        return false;
    }

    /**
     * Checks if this user is the meeting creator who can CREATE, EDIT, and MANAGE an agenda's notulensi.
     * Rule: Strictly based on meeting creator ID (agenda.sekretaris_id == user.id) or System Admin.
     */
    public function isSecretaryOfAgenda(Agenda $agenda): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Direct creator of the agenda is the ONLY ONE who can manage/edit notulensi
        return (string)$this->id === (string)$agenda->sekretaris_id;
    }

    /**
     * Alias method for clarity in permission checks: Can user manage notulensi?
     */
    public function canManageNotulensi(Agenda $agenda): bool
    {
        return $this->isSecretaryOfAgenda($agenda);
    }

    /**
     * Checks if this user has authority to APPROVE and SIGN (TTD) an agenda's notulensi.
     * Rules:
     * 1. KADIN (ketua_master): Can ONLY approve & sign agendas created by SEKDIN (sekretaris_master).
     * 2. SEKDIN (sekretaris_master): Can approve & sign agendas created in Sekretariat scope or Subbag under Sekretariat.
     * 3. Kasubag / Kabid (ketua_bidang): Can approve agendas created by their own Bidang / Subbag.
     */
    public function isApproverOfAgenda(Agenda $agenda): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        $creator = $agenda->sekretaris;
        $creatorBidangId = $creator?->bidang_id;

        // 1. Kepala Dinas (ketua_master):
        // ACC ONLY if agenda was created by SEKDIN (sekretaris_master)
        if ($this->isKetuaMaster()) {
            return $creator && $creator->isSekretarisMaster();
        }

        // Check if agenda belongs to Sekretariat or a Subbagian under Sekretariat
        $isSubbagOrSekretariat = false;
        if ($creator && ($creator->isSekretariat() || $creator->isSekretarisMaster())) {
            $isSubbagOrSekretariat = true;
        } elseif ($creatorBidangId && Bidang::isSubbagianId($creatorBidangId)) {
            $isSubbagOrSekretariat = true;
        }

        // 2. Sekdin (sekretaris_master):
        // ACC for agendas in Sekretariat scope or Subbag under Sekretariat
        if ($this->isSekretarisMaster()) {
            return $isSubbagOrSekretariat;
        }

        // 3. Kasubag / Kabid (ketua_bidang):
        // ACC for agendas created within their own Bidang / Subbag
        if ($this->isKetuaBidang()) {
            if ($creatorBidangId && (string)$this->bidang_id === (string)$creatorBidangId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user is authorized to perform attendance correction (Koreksi Presensi) for a target participant.
     * Allowed if user is meeting creator OR user is Admin (sekretaris_bidang/master) of the target participant's Bidang/Subbag.
     */
    public function canKoreksiPresensi(Agenda $agenda, User $targetUser): bool
    {
        if ($this->isAdmin() || (string)$this->id === (string)$agenda->sekretaris_id) {
            return true;
        }

        // SEKDIN can correct attendance for Sekretariat & Subbags
        if ($this->isSekretarisMaster()) {
            return true;
        }

        // Admin of target user's Bidang/Subbag
        if ($this->isSekretarisBidang() && $this->bidang_id && (string)$this->bidang_id === (string)$targetUser->bidang_id) {
            return true;
        }

        return false;
    }
}
