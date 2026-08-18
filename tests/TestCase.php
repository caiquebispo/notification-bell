<?php

namespace CaiqueBispo\NotificationBell\Tests;

use CaiqueBispo\NotificationBell\Providers\NotificationServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        \CaiqueBispo\NotificationBell\Support\SchemaReadiness::reset();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        foreach (glob(__DIR__ . '/../src/database/migrations/*.php') as $migration) {
            (require $migration)->up();
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            NotificationServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('notifications.user_model', TestUser::class);
        $app['config']->set('notifications.user_table', 'users');
    }

    protected function createUser(array $attributes = []): TestUser
    {
        static $counter = 0;
        $counter++;

        return TestUser::create(array_merge([
            'name' => "User {$counter}",
            'email' => "user{$counter}@example.test",
        ], $attributes));
    }
}

class TestUser extends Authenticatable
{
    use \CaiqueBispo\NotificationBell\Traits\HasNotifications;

    protected $table = 'users';
    protected $guarded = [];
}
