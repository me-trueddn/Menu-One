<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FaviconValidationTest extends TestCase
{
    public function test_favicon_validation_rejects_image_rule_for_ico(): void
    {
        $file = UploadedFile::fake()->create('favicon.ico', 10, 'image/x-icon');

        $validator = Validator::make(
            ['site_favicon' => $file],
            ['site_favicon' => ['nullable', 'image', 'max:1024']],
        );

        $this->assertTrue($validator->fails());
    }

    public function test_favicon_validation_accepts_ico_and_common_image_types(): void
    {
        $rules = ['site_favicon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg,ico', 'max:1024']];

        foreach (['ico', 'png', 'jpg', 'webp'] as $ext) {
            $file = UploadedFile::fake()->create('favicon.'.$ext, 10, match ($ext) {
                'ico' => 'image/x-icon',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'webp' => 'image/webp',
            });

            $validator = Validator::make(['site_favicon' => $file], $rules);

            $this->assertFalse($validator->fails(), "Expected {$ext} to pass, got: ".json_encode($validator->errors()->all()));
        }
    }
}
