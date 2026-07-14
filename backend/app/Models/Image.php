<?php

namespace App\Models;

use App\Support\ImageVariants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use Prunable;
    use SoftDeletes;

    protected $fillable = [
        'family_id',
        'user_id',
        'title',
        'path',
        'thumbnail_path',
        'placeholder',
        'taken_at',
        'width',
        'height',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Alle Dateien dieses Bildes: Original, Thumbnail und responsive Varianten. */
    public function filePaths(): array
    {
        $variants = array_map(
            fn (int $width) => ImageVariants::path($this->path, $width),
            ImageVariants::WIDTHS,
        );

        return array_filter([$this->path, $this->thumbnail_path, ...$variants]);
    }

    /** Löscht die Storage-Dateien und entfernt die Row endgültig (Papierkorb-Purge). */
    public function purge(): void
    {
        Storage::disk(config('filesystems.media'))->delete($this->filePaths());
        $this->forceDelete();
    }

    /**
     * Auto-Purge (ADR-0020): Bilder, deren Papierkorb-Frist abgelaufen ist.
     * `model:prune` läuft täglich (routes/console.php) und ruft vor dem
     * forceDelete je Model pruning() auf – dort räumen wir die Dateien weg.
     */
    public function prunable(): Builder
    {
        return static::onlyTrashed()->where(
            'deleted_at',
            '<=',
            now()->subDays((int) config('features.trash_retention_days')),
        );
    }

    protected function pruning(): void
    {
        Storage::disk(config('filesystems.media'))->delete($this->filePaths());
    }
}
