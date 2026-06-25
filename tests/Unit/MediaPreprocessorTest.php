<?php

namespace Tests\Unit;

use App\Support\MediaLimits;
use App\Support\MediaPreprocessor;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MediaPreprocessorTest extends TestCase
{
    public function test_process_resizes_large_image_within_product_limits(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required.');
        }

        $file = UploadedFile::fake()->image('large.jpg', 2000, 1500);
        $processed = MediaPreprocessor::process($file, MediaLimits::CONTEXT_PRODUCT);

        try {
            $info = getimagesize($processed['path']);
            $this->assertNotFalse($info);
            $this->assertLessThanOrEqual(1024, $info[0]);
            $this->assertLessThanOrEqual(1024, $info[1]);
        } finally {
            @unlink($processed['path']);
        }
    }

    public function test_gif_files_are_rejected_for_cloudflare_pipeline(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension required.');
        }

        $file = UploadedFile::fake()->create('anim.gif', 100, 'image/gif');

        $this->expectException(\InvalidArgumentException::class);

        MediaPreprocessor::process($file, MediaLimits::CONTEXT_PRODUCT);
    }
}
