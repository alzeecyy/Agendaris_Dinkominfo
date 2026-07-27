<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bidang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'singkatan',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'bidang_id');
    }

    public function isSubbagian(): bool
    {
        return str_contains(strtolower($this->nama), 'subbag') || str_contains(strtolower($this->singkatan), 'subbag') || strcasecmp($this->singkatan, 'sekretariat') === 0;
    }

    public static function isSubbagianId($bidangId): bool
    {
        if (!$bidangId) {
            return false;
        }
        $bidang = static::find($bidangId);
        return $bidang ? $bidang->isSubbagian() : false;
    }
}
