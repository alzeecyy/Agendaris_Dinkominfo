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

    public function getRingkasanHtmlAttribute()
    {
        return self::markdownToHtml($this->ringkasan);
    }

    public static function markdownToHtml($text)
    {
        if (empty($text)) {
            return '';
        }

        $html = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        // Headers
        $html = preg_replace('/^#\s+(.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^##\s+(.+)$/m', '<h4>$1</h4>', $html);
        $html = preg_replace('/^###\s+(.+)$/m', '<h5>$1</h5>', $html);

        // Bold
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);

        // Italic
        $html = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $html);

        // Line breaks
        $html = nl2br($html);

        // Clean up breaks around header tags (removes one or more breaks before and after headers)
        $html = preg_replace('/(<\/h[345]>)\s*(<br\s*\/?>\s*)+/i', '$1', $html);
        $html = preg_replace('/(<br\s*\/?>\s*)+(<h[345]>)/i', '$2', $html);

        return $html;
    }
}
