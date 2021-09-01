<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected $database = 'testbench';

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
    }

    protected function getEnvironmentSetUp($app)
    {
        $this->setDatabase($app);
    }

    private function setDatabase($app)
    {
        $app['config']->set('database.default', $this->database);

        $app['config']->set('database.connections.' . $this->database, [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    private function migrate()
    {
        $this->loadLaravelMigrations($this->database);

        $this->setUpDatabase();

        $this->refreshDatabase();
    }

    private function setUpDatabase()
    {
        $this->artisan('migrate', ['--realpath' => realpath(__DIR__ . '/../database/migrations')]);

        $this->app['db']->connection()->getSchemaBuilder()->create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('email')->nullable();
            $table->json('address')->nullable();
            $table->timestamps();
        });
    }
}
