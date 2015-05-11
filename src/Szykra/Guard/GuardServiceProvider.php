<?php namespace Szykra\Guard;

use Illuminate\Support\ServiceProvider;
use Szykra\Guard\Factories\PermissionFactory;
use Szykra\Guard\Factories\RoleFactory;

class GuardServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Szykra\Guard\Contracts\Permissible', function($app) {
            return $app['auth']->user();
        });

        $this->app->singleton('Szykra\Guard\Contracts\PermissionChecker', 'Szykra\Guard\Adapters\EloquentAdapter');

        $this->app->bindShared('Szykra\Guard\Factories\RoleFactory', function() {
            return new RoleFactory(config('guard.model.role'));
        });

        $this->app->bindShared('Szykra\Guard\Factories\PermissionFactory', function() {
            return new PermissionFactory(config('guard.model.permission'));
        });

        $this->commands([
            'Szykra\Guard\Console\CreateRoleConsole',
            'Szykra\Guard\Console\CreatePermissionConsole',
            'Szykra\Guard\Console\LinkPermissionWithRoleConsole'
        ]);

        $this->publishes([
            __DIR__.'/../../../config/guard.php' => 'config/guard.php'
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../../../config/guard.php', 'guard');
    }
}
