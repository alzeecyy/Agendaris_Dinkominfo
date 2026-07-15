<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notulensi extends Model
{
    use HasFactory;

    protected $table = 'notulensis';

    protected $fillable = [
        'agenda_id',
        'audio_path',
        'audio_name',
        'audio_files',
        'transkrip_raw',
        'ringkasan',
        'pembahasan',
        'keputusan',
        'kesimpulan',
        'status',
        'catatan_revisi',
        'approver_id',
        'last_edited_by_id',
        'pembahasan_title',
        'keputusan_title',
        'is_transcribing',
        'transkrip_error',
    ];

    protected $casts = [
        'audio_files' => 'array',
        'is_transcribing' => 'boolean',
    ];

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class, 'agenda_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function lastEditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by_id');
    }

    public function getPembahasanTitleAttribute($value)
    {
        return $value ?: 'Poin Pembahasan Rapat';
    }

    public function getKeputusanTitleAttribute($value)
    {
        return $value ?: 'Daftar Keputusan Rapat';
    }
}
