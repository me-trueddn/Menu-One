<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function backup(?string $label = null): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $folderName = $label !== null && $label !== ''
            ? "{$timestamp}_{$label}"
            : $timestamp;

        $directory = storage_path('backups/'.$folderName);
        File::ensureDirectoryExists($directory);

        $driver = DB::getDriverName();

        $path = match (true) {
            in_array($driver, ['mysql', 'mariadb'], true) => $this->backupMysql($directory),
            $driver === 'sqlite' => $this->backupSqlite($directory),
            default => throw new RuntimeException("Database backup is not supported for driver [{$driver}]."),
        };

        File::put($directory.'/manifest.json', json_encode([
            'created_at' => now()->toIso8601String(),
            'driver' => $driver,
            'database' => $this->databaseName(),
            'label' => $label,
            'file' => basename($path),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }

    private function backupMysql(string $directory): string
    {
        $connection = config('database.connections.'.config('database.default'));
        $database = (string) ($connection['database'] ?? '');
        $host = (string) ($connection['host'] ?? '127.0.0.1');
        $port = (string) ($connection['port'] ?? '3306');
        $username = (string) ($connection['username'] ?? 'root');
        $password = (string) ($connection['password'] ?? '');

        $file = $directory.'/'.($database !== '' ? $database : 'database').'.sql';
        $binary = $this->mysqldumpBinary();

        $command = [
            $binary,
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--default-character-set=utf8mb4',
            '--single-transaction',
            '--routines',
            '--triggers',
            $database,
        ];

        $process = new Process($command, null, $password !== '' ? ['MYSQL_PWD' => $password] : []);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput() ?: 'mysqldump failed.'));
        }

        File::put($file, $process->getOutput());

        return $file;
    }

    private function backupSqlite(string $directory): string
    {
        $connection = config('database.connections.'.config('database.default'));
        $database = (string) ($connection['database'] ?? '');

        if ($database === '' || $database === ':memory:') {
            throw new RuntimeException('SQLite in-memory databases cannot be backed up.');
        }

        if (! File::exists($database)) {
            throw new RuntimeException("SQLite database file not found: {$database}");
        }

        $file = $directory.'/'.basename($database);
        File::copy($database, $file);

        return $file;
    }

    private function mysqldumpBinary(): string
    {
        $candidates = array_filter([
            env('MYSQLDUMP_PATH'),
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'mysqldump',
        ]);

        foreach ($candidates as $candidate) {
            $process = new Process([$candidate, '--version']);
            $process->run();

            if ($process->isSuccessful()) {
                return $candidate;
            }
        }

        throw new RuntimeException('mysqldump binary not found. Set MYSQLDUMP_PATH in .env.');
    }

    private function databaseName(): string
    {
        $connection = config('database.connections.'.config('database.default'));

        return (string) ($connection['database'] ?? '');
    }
}
