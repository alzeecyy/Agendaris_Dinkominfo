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
}
