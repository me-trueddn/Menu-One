<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->guardAgainstNonSqliteTestDatabase();
    }

    protected function guardAgainstNonSqliteTestDatabase(): void
    {
        $connection = (string) config('database.default');
        $driver = (string) config("database.connections.{$connection}.driver");
        $database = (string) config("database.connections.{$connection}.database");

        $allowedSqlite = $database === ':memory:'
            || str_ends_with($database, 'testing.sqlite');

        if ($driver !== 'sqlite' || ! $allowedSqlite) {
            throw new RuntimeException(
                "Tests blocked: expected sqlite (:memory:), got [{$driver}:{$database}] on connection [{$connection}]. ".
                'Run: php artisan config:clear'
            );
        }
    }
}
