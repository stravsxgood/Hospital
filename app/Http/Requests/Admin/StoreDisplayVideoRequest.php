<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\DisplayVideo;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreDisplayVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|Closure>>
     */
    public function rules(): array
    {
        $sourceType = $this->input('source_type', $this->hasFile('video_file') ? 'file' : 'youtube');

        return [
            'source_type' => ['nullable', 'string', 'in:youtube,file'],
            'title' => ['required', 'string', 'max:255'],
            'youtube_url' => [
                $sourceType === 'youtube' ? 'required' : 'nullable',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($sourceType): void {
                    if ($sourceType === 'youtube' && (! is_string($value) || empty(DisplayVideo::extractYoutubeId($value)))) {
                        $fail('Link YouTube atau kode embed iframe tidak valid. Masukkan link video YouTube (misal: https://www.youtube.com/watch?v=...) atau kode <iframe> embed.');
                    }
                },
            ],
            'video_file' => [
                $sourceType === 'file' ? 'required' : 'nullable',
                'file',
                'mimes:mp4,webm,ogg,mov,m4v',
                'max:819200', // Maksimal 800MB (800 * 1024 KB = 819.200 KB)
            ],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul video edukasi/promosi wajib diisi.',
            'title.max' => 'Judul video maksimal 255 karakter.',
            'youtube_url.required' => 'Link YouTube atau kode iframe video wajib diisi saat memilih metode YouTube.',
            'video_file.required' => 'File video wajib dipilih saat menggunakan metode upload file.',
            'video_file.file' => 'Berkas yang diunggah harus berupa file video yang valid.',
            'video_file.mimes' => 'Format file video harus berupa MP4, WebM, OGG, MOV, atau M4V.',
            'video_file.max' => 'Ukuran file video melebihi batas maksimal 800 MB.',
        ];
    }
}
