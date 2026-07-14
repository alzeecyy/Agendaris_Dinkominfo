<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgendaExternalParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'agenda_id',
        'nama',
        'jabatan',
        'instansi',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class, 'agenda_id');
    }
}
