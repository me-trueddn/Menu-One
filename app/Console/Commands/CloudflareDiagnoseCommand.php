<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\CloudflareImagesService;
use App\Services\CloudflareStreamService;
use App\Support\CloudflarePolicy;
use Illuminate\Console\Command;

class CloudflareDiagnoseCommand extends Command
{
    protected $signature = 'cloudflare:diagnose';

    protected $description = 'Verify Cloudflare Images and Stream configuration';

    public function handle(CloudflareImagesService $images, CloudflareStreamService $stream): int
    {
        Setting::flushCache();

        $this->table(
            ['Key', 'Value'],
            [
                ['Images enabled', CloudflarePolicy::imagesEnabled() ? 'yes' : 'no'],
                ['Stream enabled', CloudflarePolicy::streamEnabled() ? 'yes' : 'no'],
                ['Account ID', CloudflarePolicy::accountId() !== '' ? CloudflarePolicy::accountId() : '—'],
                ['Account hash', CloudflarePolicy::accountHash() !== '' ? CloudflarePolicy::accountHash() : '—'],
                ['API token OK', CloudflarePolicy::apiToken() !== '' ? 'yes' : 'no'],
                ['Token decrypt failed', CloudflarePolicy::apiTokenDecryptFailed() ? 'yes' : 'no'],
                ['Configured', CloudflarePolicy::configured() ? 'yes' : 'no'],
                ['Sample image URL', CloudflarePolicy::sampleDeliveryUrl()],
                ['Sample video URL', CloudflarePolicy::samplePlaybackUrl()],
            ],
        );

        if (! CloudflarePolicy::configured()) {
            $this->warn('Cloudflare Images is not fully configured. Enable it under Platform → Site settings.');

            return self::SUCCESS;
        }

        $this->line('Images API: '.($images->ping() ? 'OK' : 'FAILED'));

        if (CloudflarePolicy::streamEnabled()) {
            $this->line('Stream API: '.($stream->ping() ? 'OK' : 'FAILED'));
        }

        $this->newLine();
        $this->comment('Create fixed variants in Cloudflare Dashboard → Images → Variants:');
        foreach (CloudflarePolicy::IMAGE_VARIANTS as $variant) {
            $this->line(" - {$variant}");
        }

        return self::SUCCESS;
    }
}
