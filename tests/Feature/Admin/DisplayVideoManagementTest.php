<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\DisplayVideo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Storage::fake('public');
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
});

test('super admin can add youtube video to playlist via standard url', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->post(route('admin.display-videos.store'), [
            'title' => 'Video Edukasi Rawat Jalan',
            'youtube_url' => 'https://www.youtube.com/watch?v=zVgKnfN9i34',
            'order' => 1,
            'is_active' => true,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('display_videos', [
        'title' => 'Video Edukasi Rawat Jalan',
        'order' => 1,
        'is_active' => true,
    ]);

    $video = DisplayVideo::first();
    expect($video)->not->toBeNull();
    expect($video->youtube_id)->toBe('zVgKnfN9i34');
    expect($video->embed_url)->toContain('https://www.youtube.com/embed/zVgKnfN9i34');
});

test('super admin can add youtube video to playlist via iframe embed code', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $iframeCode = '<iframe width="560" height="315" src="https://www.youtube.com/embed/zVgKnfN9i34?si=NCoyL7hkmurvkXH2" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';

    $response = $this->actingAs($admin)
        ->post(route('admin.display-videos.store'), [
            'title' => 'Video Profil SIMRS Rumah Sakit',
            'youtube_url' => $iframeCode,
            'order' => 2,
            'is_active' => true,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $video = DisplayVideo::where('title', 'Video Profil SIMRS Rumah Sakit')->first();
    expect($video)->not->toBeNull();
    expect($video->youtube_id)->toBe('zVgKnfN9i34');
    expect($video->thumbnail_url)->toBe('https://img.youtube.com/vi/zVgKnfN9i34/hqdefault.jpg');
});

test('youtube video submission validates required fields and invalid url', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->post(route('admin.display-videos.store'), [
            'title' => '',
            'youtube_url' => 'https://invalid-domain.com/video',
        ]);

    $response->assertSessionHasErrors(['title', 'youtube_url']);
});

test('super admin can toggle display video active status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $video = DisplayVideo::create([
        'title' => 'Video Tips Sehat',
        'file_path' => 'https://www.youtube.com/watch?v=zVgKnfN9i34',
        'order' => 1,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->patch(route('admin.display-videos.toggle', $video));

    $response->assertRedirect();
    expect($video->fresh()->is_active)->toBeFalse();

    // Toggle back
    $this->actingAs($admin)
        ->patch(route('admin.display-videos.toggle', $video));

    expect($video->fresh()->is_active)->toBeTrue();
});

test('super admin can delete display video', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $video = DisplayVideo::create([
        'title' => 'Video Dihapus',
        'file_path' => 'https://www.youtube.com/watch?v=zVgKnfN9i34',
        'order' => 2,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->delete(route('admin.display-videos.destroy', $video));

    $response->assertRedirect();
    $this->assertDatabaseMissing('display_videos', ['id' => $video->id]);
});

test('super admin can upload physical video file up to 800mb to playlist', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $fakeVideo = UploadedFile::fake()->create('edukasi-layanan.mp4', 15000, 'video/mp4');

    $response = $this->actingAs($admin)
        ->post(route('admin.display-videos.store'), [
            'source_type' => 'file',
            'title' => 'Video Panduan Rawat Inap & BPJS',
            'video_file' => $fakeVideo,
            'order' => 3,
            'is_active' => true,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $video = DisplayVideo::where('title', 'Video Panduan Rawat Inap & BPJS')->first();
    expect($video)->not->toBeNull();
    expect($video->file_path)->toStartWith('display_videos/');
    expect($video->youtube_id)->toBeNull();
    expect($video->source_type)->toBe('file');
    expect($video->is_youtube)->toBeFalse();
    expect($video->video_url)->toContain('storage/display_videos/');

    expect(Storage::disk('public')->exists($video->file_path))->toBeTrue();
});

test('physical video file upload rejects files over 800mb', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    // 850 MB = 870,400 KB (over 800MB limit of 819,200 KB)
    $largeVideo = UploadedFile::fake()->create('large-video.mp4', 850000, 'video/mp4');

    $response = $this->actingAs($admin)
        ->post(route('admin.display-videos.store'), [
            'source_type' => 'file',
            'title' => 'Video Terlalu Besar',
            'video_file' => $largeVideo,
        ]);

    $response->assertSessionHasErrors(['video_file']);
});

test('super admin can delete display video and removes physical file from disk', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $fakeVideo = UploadedFile::fake()->create('hapus-saya.mp4', 5000, 'video/mp4');
    $path = $fakeVideo->store('display_videos', 'public');

    $video = DisplayVideo::create([
        'title' => 'Video File Dihapus',
        'file_path' => $path,
        'order' => 4,
        'is_active' => true,
    ]);

    expect(Storage::disk('public')->exists($path))->toBeTrue();

    $response = $this->actingAs($admin)
        ->delete(route('admin.display-videos.destroy', $video));

    $response->assertRedirect();
    $this->assertDatabaseMissing('display_videos', ['id' => $video->id]);
    expect(Storage::disk('public')->exists($path))->toBeFalse();
});

test('public display screen receives active video playlist', function () {
    DisplayVideo::create([
        'title' => 'Video Aktif 1',
        'file_path' => 'https://www.youtube.com/watch?v=zVgKnfN9i34',
        'order' => 1,
        'is_active' => true,
    ]);

    DisplayVideo::create([
        'title' => 'Video Nonaktif',
        'file_path' => 'https://www.youtube.com/watch?v=zVgKnfN9i34',
        'order' => 2,
        'is_active' => false,
    ]);

    $response = $this->get(route('display.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Display/Index')
        ->has('videos', 1)
        ->where('videos.0.title', 'Video Aktif 1')
    );
});
