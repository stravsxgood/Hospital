<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $title
 * @property string $file_path
 * @property int $order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $youtube_id
 * @property-read string $embed_url
 * @property-read string $video_url
 * @property-read string $thumbnail_url
 * @property-read string $file_size_formatted
 * @property-read string $source_type
 * @property-read bool $is_youtube
 */
class DisplayVideo extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'display_videos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'file_path',
        'order',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'youtube_id',
        'embed_url',
        'video_url',
        'thumbnail_url',
        'file_size_formatted',
        'source_type',
        'is_youtube',
    ];

    /**
     * Accessor: Mengecek apakah video bertipe YouTube.
     */
    public function getIsYoutubeAttribute(): bool
    {
        return ! empty($this->youtube_id);
    }

    /**
     * Accessor: Tipe sumber video ('youtube' atau 'file').
     */
    public function getSourceTypeAttribute(): string
    {
        return $this->is_youtube ? 'youtube' : 'file';
    }

    /**
     * Helper statik: Ekstraksi YouTube Video ID dari berbagai format link atau kode <iframe>.
     */
    public static function extractYoutubeId(?string $input): ?string
    {
        if (empty($input)) {
            return null;
        }

        $input = trim($input);

        // 1. Jika pengguna menempelkan tag <iframe> lengkap
        if (preg_match('/src=["\']([^"\']+)["\']/i', $input, $srcMatches)) {
            $input = $srcMatches[1];
        }

        // 2. Format URL standar youtube.com/watch?v=..., youtu.be/..., /embed/..., /shorts/...
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?|shorts)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $input, $match)) {
            return $match[1];
        }

        // 3. ID langsung 11 karakter
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }

        return null;
    }

    /**
     * Accessor: Mendapatkan 11-digit YouTube ID.
     */
    public function getYoutubeIdAttribute(): ?string
    {
        return self::extractYoutubeId($this->file_path);
    }

    /**
     * Accessor: URL Embed YouTube untuk pemutaran otomatis di <iframe>.
     */
    public function getEmbedUrlAttribute(): string
    {
        $id = $this->youtube_id;

        if ($id) {
            return "https://www.youtube.com/embed/{$id}?autoplay=1&mute=1&loop=1&playlist={$id}&enablejsapi=1&playsinline=1&rel=0";
        }

        return $this->video_url;
    }

    /**
     * Accessor: Thumbnail kualitas tinggi dari YouTube.
     */
    public function getThumbnailUrlAttribute(): string
    {
        $id = $this->youtube_id;

        if ($id) {
            return "https://img.youtube.com/vi/{$id}/hqdefault.jpg";
        }

        return '';
    }

    /**
     * Accessor: Mendapatkan URL publik video (YouTube atau storage lokal).
     */
    public function getVideoUrlAttribute(): string
    {
        if (empty($this->file_path)) {
            return '';
        }

        $id = $this->youtube_id;
        if ($id) {
            return "https://www.youtube.com/watch?v={$id}";
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        $url = $disk->url($this->file_path);

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            return asset($url);
        }

        return $url;
    }

    /**
     * Accessor: Format ukuran file / indikator tipe YouTube.
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if ($this->youtube_id) {
            return 'YouTube Stream';
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');

        if (empty($this->file_path) || ! $disk->exists($this->file_path)) {
            return '0 MB';
        }

        try {
            $bytes = $disk->size($this->file_path);

            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2).' MB';
            }

            return number_format($bytes / 1024, 1).' KB';
        } catch (\Throwable) {
            return '0 MB';
        }
    }
}
