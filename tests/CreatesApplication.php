<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use RuntimeException;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // Must happen here, not in setUp(): RefreshDatabase wipes the database
        // from setUpTraits(), which runs after the application is created but
        // before the test's own setUp() body.
        $this->guardAgainstNonTestDatabase($app);

        return $app;
    }

    /**
     * Refuse to run against anything but the in-memory test database.
     *
     * phpunit.xml selects sqlite through server variables, which config() only
     * reads when the config cache is absent. With bootstrap/cache/config.php
     * present, Laravel serves the cached array instead and the suite silently
     * points at the real development database — where RefreshDatabase runs
     * migrate:fresh and drops every table.
     */
    private function guardAgainstNonTestDatabase(Application $app): void
    {
        $config = $app->make('config');
        $connection = $config->get('database.default');
        $database = $config->get("database.connections.{$connection}.database");

        if ($connection === 'sqlite' && in_array($database, [':memory:', ''], true)) {
            return;
        }

        throw new RuntimeException(
            "Refusing to run the test suite against `{$connection}` ({$database}).\n"
            . "Tests expect sqlite :memory:, and RefreshDatabase would wipe this database.\n\n"
            . "This usually means the config cache is stale: run `php artisan config:clear` and try again."
        );
    }
}
