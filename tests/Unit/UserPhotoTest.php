<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * The profile photo rules and the accessor that resolves one for display.
 * Deliberately database-free: User::PHOTO_RULES is the same constant the
 * profile controller validates with, so these assert the real rule.
 */
class UserPhotoTest extends TestCase
{
    private function fails(UploadedFile $file): bool
    {
        return Validator::make(['photo' => $file], ['photo' => User::PHOTO_RULES])->fails();
    }

    public function test_a_normal_photo_is_accepted(): void
    {
        $this->assertFalse($this->fails(UploadedFile::fake()->image('me.jpg', 400, 400)));
        $this->assertFalse($this->fails(UploadedFile::fake()->image('me.png', 100, 100)));
        $this->assertFalse($this->fails(UploadedFile::fake()->image('me.webp', 800, 600)));
    }

    public function test_no_photo_is_fine_because_the_field_is_optional(): void
    {
        $this->assertFalse(Validator::make(['photo' => null], ['photo' => User::PHOTO_RULES])->fails());
    }

    public function test_a_document_is_rejected(): void
    {
        $this->assertTrue($this->fails(UploadedFile::fake()->create('cv.pdf', 40, 'application/pdf')));
    }

    public function test_an_unsupported_image_format_is_rejected(): void
    {
        $this->assertTrue($this->fails(UploadedFile::fake()->image('shot.gif', 300, 300)));
    }

    public function test_a_file_over_two_megabytes_is_rejected(): void
    {
        $this->assertTrue($this->fails(UploadedFile::fake()->image('huge.jpg', 900, 900)->size(3000)));
    }

    public function test_an_image_too_small_to_show_is_rejected(): void
    {
        $this->assertTrue($this->fails(UploadedFile::fake()->image('tiny.jpg', 40, 40)));
    }

    public function test_no_avatar_resolves_to_null_so_initials_are_used(): void
    {
        $this->assertNull((new User())->avatarUrl());
    }

    public function test_a_stored_path_resolves_against_the_public_disk(): void
    {
        $user = new User(['avatar_url' => 'avatars/me.jpg']);

        $this->assertSame(asset('storage/avatars/me.jpg'), $user->avatarUrl());
    }

    public function test_an_external_url_is_handed_back_untouched(): void
    {
        foreach (['https://cdn.example.com/me.png', 'http://cdn.example.com/me.png'] as $url) {
            $this->assertSame($url, (new User(['avatar_url' => $url]))->avatarUrl());
        }
    }

    public function test_only_a_file_this_app_stored_counts_as_an_upload(): void
    {
        Storage::fake('public');

        $this->assertFalse((new User())->hasUploadedAvatar());
        $this->assertFalse((new User(['avatar_url' => 'https://cdn.example.com/me.png']))->hasUploadedAvatar());
        $this->assertFalse((new User(['avatar_url' => 'avatars/gone.jpg']))->hasUploadedAvatar());

        Storage::disk('public')->put('avatars/here.jpg', 'x');
        $this->assertTrue((new User(['avatar_url' => 'avatars/here.jpg']))->hasUploadedAvatar());
    }
}
