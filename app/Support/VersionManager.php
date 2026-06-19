<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class VersionManager
{
    public function __construct(private ?string $path = null) {}

    public function path(): string
    {
        return $this->path ?? base_path('version.json');
    }

    /** @return array{major: int, minor: int, patch: int, build: int, history: array<int, array<string, mixed>>} */
    public function data(): array
    {
        if (! is_file($this->path())) {
            return $this->defaults();
        }

        $decoded = json_decode((string) file_get_contents($this->path()), true);

        return is_array($decoded) ? array_merge($this->defaults(), $decoded) : $this->defaults();
    }

    public function current(): string
    {
        return $this->format($this->data());
    }

    public function buildNumber(): int
    {
        return (int) $this->data()['build'];
    }

    public function bump(): string
    {
        $data = $this->data();
        $data['patch']++;
        $this->save($data);

        return $this->format($data);
    }

    /** @return array{build: int, version: string, label: string, next_version: string} */
    public function buildRelease(): array
    {
        $data = $this->data();
        $data['build']++;
        $version = $this->format($data);
        $buildNumber = $data['build'];
        $label = sprintf('Build %d - %s', $buildNumber, $version);

        $data['history'][] = [
            'build' => $buildNumber,
            'version' => $version,
            'label' => $label,
            'built_at' => Carbon::now()->toIso8601String(),
        ];

        $data['major'] = $buildNumber + 1;
        $data['minor'] = 0;
        $data['patch'] = 1;

        $this->save($data);

        return [
            'build' => $buildNumber,
            'version' => $version,
            'label' => $label,
            'next_version' => $this->format($data),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function history(): array
    {
        return $this->data()['history'] ?? [];
    }

    /** @param array{major: int, minor: int, patch: int, build: int, history?: array<int, array<string, mixed>>} $data */
    private function format(array $data): string
    {
        return sprintf('%d.%d.%d', $data['major'], $data['minor'], $data['patch']);
    }

    /** @param array{major: int, minor: int, patch: int, build: int, history?: array<int, array<string, mixed>>} $data */
    private function save(array $data): void
    {
        $payload = [
            'major' => (int) $data['major'],
            'minor' => (int) $data['minor'],
            'patch' => (int) $data['patch'],
            'build' => (int) $data['build'],
            'history' => array_values($data['history'] ?? []),
        ];

        file_put_contents(
            $this->path(),
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL,
            LOCK_EX
        );
    }

    /** @return array{major: int, minor: int, patch: int, build: int, history: array<int, array<string, mixed>>} */
    private function defaults(): array
    {
        return [
            'major' => 1,
            'minor' => 0,
            'patch' => 0,
            'build' => 0,
            'history' => [],
        ];
    }
}
