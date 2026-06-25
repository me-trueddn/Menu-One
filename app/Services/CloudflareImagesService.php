<?php

namespace App\Services;

use App\Support\CloudflarePolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareImagesService
{
    private const API_BASE = 'https://api.cloudflare.com/client/v4';

    public function upload(UploadedFile $file, array $metadata = []): string
    {
        $accountId = CloudflarePolicy::accountId();
        $token = CloudflarePolicy::apiToken();

        $response = Http::withToken($token)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post(static::API_BASE."/accounts/{$accountId}/images/v1", [
                'metadata' => json_encode($metadata),
                'requireSignedURLs' => 'false',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(__('menu.cloudflare_upload_failed'));
        }

        $imageId = (string) data_get($response->json(), 'result.id', '');

        if ($imageId === '') {
            throw new RuntimeException(__('menu.cloudflare_upload_failed'));
        }

        return $imageId;
    }

    public function uploadPath(string $path, string $filename, string $mime, array $metadata = []): string
    {
        $accountId = CloudflarePolicy::accountId();
        $token = CloudflarePolicy::apiToken();

        $response = Http::withToken($token)
            ->attach('file', file_get_contents($path), $filename)
            ->post(static::API_BASE."/accounts/{$accountId}/images/v1", [
                'metadata' => json_encode($metadata),
                'requireSignedURLs' => 'false',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(__('menu.cloudflare_upload_failed'));
        }

        $imageId = (string) data_get($response->json(), 'result.id', '');

        if ($imageId === '') {
            throw new RuntimeException(__('menu.cloudflare_upload_failed'));
        }

        return $imageId;
    }

    public function delete(string $imageId): void
    {
        if ($imageId === '') {
            return;
        }

        $accountId = CloudflarePolicy::accountId();
        $token = CloudflarePolicy::apiToken();

        Http::withToken($token)
            ->delete(static::API_BASE."/accounts/{$accountId}/images/v1/{$imageId}");
    }

    public function deliveryUrl(string $imageId, string $variant = 'public'): string
    {
        $hash = CloudflarePolicy::accountHash();

        return "https://imagedelivery.net/{$hash}/{$imageId}/{$variant}";
    }

    public function ping(): bool
    {
        $accountId = CloudflarePolicy::accountId();
        $token = CloudflarePolicy::apiToken();

        $response = Http::withToken($token)
            ->get(static::API_BASE."/accounts/{$accountId}/images/v1", [
                'per_page' => 1,
            ]);

        return $response->successful();
    }
}
