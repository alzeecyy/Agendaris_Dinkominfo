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
            strcasecmp($this->bidang->nama, 'sekretariat') === 0 ||
            $this->bidang->isSubbagian()
        );
    }

    /**
     * Checks if this user can view the Agenda Hari Ini (TV / Monitoring Board) page.
     * Allowed: Pimpinan (Ketua Master/Bidang), Sekretaris (Master/Bidang), and Sekretariat Staff.
     * Regular staff of Aptika, IKP, Statistika, etc. are excluded to avoid confusion.
     */
    public function canViewAgendaToday(): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        return $this->isSekretarisMaster() 
            || $this->isKetuaMaster() 
            || $this->isSekretarisBidang() 
            || $this->isKetuaBidang() 
            || $this->isSekretariat();
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
     * Checks if this user is the authorized secretary who can EDIT an agenda's notulensi.
     * Rule:
     * - Only the secretary creator (or secretaries in the same Bidang) can EDIT.
     * - Sekdin / Sekretariat staff can only edit agendas created by Sekretariat / themselves.
     * - Sekdin viewing Notulensi created by a Bidang will be View Only.
     */
    public function isSecretaryOfAgenda(Agenda $agenda): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        // Direct creator of the agenda can edit
        if ((string)$this->id === (string)$agenda->sekretaris_id) {
            return true;
        }

        $creator = $agenda->sekretaris;

        // If user is Sekdin / Sekretariat staff:
        if ($this->isSekretariat() || $this->isSekretarisMaster()) {
            if ($creator && ($creator->isSekretariat() || $creator->isSekretarisMaster())) {
                return true;
            }
            // Sekdin viewing a Bidang's agenda -> CANNOT EDIT (View Only)
            return false;
        }

        // If user is Admin/Sekretaris of a Subbagian / Bidang:
        if ($this->isSekretarisBidang() || $this->isStaff()) {
            if ($creator && (string)$creator->bidang_id === (string)$this->bidang_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if this user has authority to APPROVE and SIGN (TTD) an agenda's notulensi.
     * Rules:
     * - Subbagian (Umum, Keuangan, Perencanaan): Can be approved by Kasubag OR Sekdin.
     * - Bidang (IKP, Aptika, etc.): Can be approved by Kabid, Sekdin, or Kadis.
     * - Lintas Dinas: Can be approved by Kadis or Sekdin.
     */
    public function isApproverOfAgenda(Agenda $agenda): bool
    {
        if ($this->isAdmin()) {
            return false;
        }

        // 1. Kepala Dinas (ketua_master) can approve any agenda across the agency
        if ($this->isKetuaMaster()) {
            return true;
        }

        $creator = $agenda->sekretaris;
        $creatorBidangId = $creator?->bidang_id;
        $hakAkses = $agenda->hak_akses ?? [];

        // Check if agenda belongs to Sekretariat or a Subbagian under Sekretariat
        $isSubbagOrSekretariat = false;
        if ($creator && ($creator->isSekretariat() || $creator->isSekretarisMaster())) {
            $isSubbagOrSekretariat = true;
        } elseif ($creatorBidangId && Bidang::isSubbagianId($creatorBidangId)) {
            $isSubbagOrSekretariat = true;
        }

        // 2. Sekdin (sekretaris_master / Sekretariat Pimpinan):
        if ($this->isSekretarisMaster() || $this->isSekretariat()) {
            // Sekdin has authority to approve & sign any Notulensi under Sekretariat/Subbagian or Lintas Dinas
            if ($isSubbagOrSekretariat || in_array('semua_orang', $hakAkses) || count($hakAkses) > 1 || count($hakAkses) === 0) {
                return true;
            }
        }

        // 3. Kasubag / Kabid (ketua_bidang):
        if ($this->isKetuaBidang()) {
            // Kasubag / Kabid can approve if creator is in the same Subbag / Bidang
            if ($creatorBidangId && (string)$this->bidang_id === (string)$creatorBidangId) {
                return true;
            }
            // Or if agenda hak_akses matches user's bidang_id
            if (in_array((string)$this->bidang_id, array_map('strval', $hakAkses))) {
                return true;
            }
        }

        return false;
    }
}
