<?php

namespace Tests\Unit;

use App\Support\VersionManager;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VersionManagerTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempFile = storage_path('framework/testing-version.json');
        File::delete($this->tempFile);
    }

    protected function tearDown(): void
    {
        File::delete($this->tempFile);

        parent::tearDown();
    }

    public function test_bump_increments_patch_version(): void
    {
        $manager = new VersionManager($this->tempFile);

        $this->assertSame('1.0.1', $manager->bump());
        $this->assertSame('1.0.2', $manager->bump());
    }

    public function test_build_release_follows_expected_cycle(): void
    {
        $manager = new VersionManager($this->tempFile);

        for ($i = 0; $i < 10; $i++) {
            $manager->bump();
        }

        $first = $manager->buildRelease();
        $this->assertSame('Build 1 - 1.0.10', $first['label']);
        $this->assertSame('2.0.1', $first['next_version']);

        $second = $manager->buildRelease();
        $this->assertSame('Build 2 - 2.0.1', $second['label']);
        $this->assertSame('3.0.1', $second['next_version']);

        $manager->bump();
        $manager->bump();
        $manager->bump();

        $third = $manager->buildRelease();
        $this->assertSame('Build 3 - 3.0.4', $third['label']);
    }
}
