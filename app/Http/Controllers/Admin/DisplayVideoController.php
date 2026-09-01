<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDisplayVideoRequest;
use App\Models\DisplayVideo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DisplayVideoController extends Controller
{
    /**
     * Menyimpan video baru (via Link YouTube atau Upload File Video maks. 800MB) ke playlist display TV.
     */
    public function store(StoreDisplayVideoRequest $request): RedirectResponse
    {
        $sourceType = (string) $request->input('source_type', $request->hasFile('video_file') ? 'file' : 'youtube');

        if ($sourceType === 'file' && $request->hasFile('video_file')) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $request->file('video_file');
            $filePath = $uploadedFile->store('display_videos', 'public');
            $successMessage = 'File video lokal berhasil diunggah dan ditambahkan ke playlist display TV.';
        } else {
            $inputUrl = (string) $request->validated('youtube_url');
            $youtubeId = DisplayVideo::extractYoutubeId($inputUrl);
            $filePath = $youtubeId ? "https://www.youtube.com/watch?v={$youtubeId}" : $inputUrl;
            $successMessage = 'Video YouTube berhasil ditambahkan ke playlist display TV.';
        }

        $order = $request->filled('order')
            ? (int) $request->input('order')
            : (int) ((DisplayVideo::max('order') ?? 0) + 1);

        DisplayVideo::create([
            'title' => (string) $request->validated('title'),
            'file_path' => $filePath,
            'order' => $order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        Cache::forget('display_videos_active');

        return back()->with('success', $successMessage);
    }

    /**
     * Mengubah status aktif / nonaktif video di playlist display TV.
     */
    public function toggle(DisplayVideo $displayVideo): RedirectResponse
    {
        $displayVideo->update([
            'is_active' => ! $displayVideo->is_active,
        ]);

        Cache::forget('display_videos_active');

        return back()->with('success', 'Status pemutaran video berhasil diperbarui.');
    }

    /**
     * Menghapus record video dari database dan storage (jika legacy file).
     */
    public function destroy(DisplayVideo $displayVideo): RedirectResponse
    {
        if (! empty($displayVideo->file_path) && ! str_starts_with($displayVideo->file_path, 'http') && Storage::disk('public')->exists($displayVideo->file_path)) {
            Storage::disk('public')->delete($displayVideo->file_path);
        }

        $displayVideo->delete();

        Cache::forget('display_videos_active');

        return back()->with('success', 'Video berhasil dihapus dari playlist display.');
    }
}
