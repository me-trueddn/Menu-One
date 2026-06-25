<?php

namespace App\Services;

use App\Support\CloudflarePolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareStreamService
{
    private const API_BASE = 'https://api.cloudflare.com/client/v4';

    public function upload(UploadedFile $file, array $metadata = []): string
    {
        $accountId = CloudflarePolicy::accountId();
        $token = CloudflarePolicy::apiToken();

        $response = Http::withToken($token)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post(static::API_BASE."/accounts/{$accountId}/stream", [
                'meta' => json_encode($metadata),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(__('menu.cloudflare_stream_upload_failed'));
        }

        $uid = (string) data_get($response->json(), 'result.uid', '');

        if ($uid === '') {
            throw new RuntimeException(__('menu.cloudflare_stream_upload_failed'));
        }

        return $uid;
    }

    public function delete(string $uid): void
    {
        if ($uid === '') {
            return;
        }

        $accountId = CloudflarePolicy::accountId();
        $token = CloudflarePolicy::apiToken();

        Http::withToken($token)
            ->delete(static::API_BASE."/accounts/{$accountId}/stream/{$uid}");
    }

    public function playbackUrl(string $uid): string
    {
        $subdomain = CloudflarePolicy::streamCustomerSubdomain();

        if ($subdomain !== '') {
            return "https://{$subdomain}/{$uid}/watch";
        }

        return "https://customer-videodelivery.net/{$uid}/watch";
    }

    public function iframeUrl(string $uid): string
    {
        $subdomain = CloudflarePolicy::streamCustomerSubdomain();

        if ($subdomain !== '') {
            return "https://{$subdomain}/{$uid}/iframe";
        }

        return "https://customer-videodelivery.net/{$uid}/iframe";
    }

    public function ping(): bool
    {
        $accountId = CloudflarePolicy::accountId();
        $token = CloudflarePolicy::apiToken();

        $response = Http::withToken($token)
            ->get(static::API_BASE."/accounts/{$accountId}/stream", [
                'limit' => 1,
            ]);

        return $response->successful();
    }
}
