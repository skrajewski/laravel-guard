<?php namespace Szykra\Guard;

use Illuminate\Support\ServiceProvider;
use Szykra\Guard\Adapters\EloquentAdapter;
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
        $this->registerAdapters();
        $this->registerFactories();
        $this->registerCommands();
        $this->bindingContracts();
        $this->registerConfig();
    }

    public function provides() {
        return [
            'guard.adapter.eloquent'
        ];
    }

    private function registerAdapters()
    {
        $this->app->bindShared('Szykra\Guard\Adapters\EloquentAdapter', function() {
            return new EloquentAdapter(config('guard.model.role'));
        });

        $this->app->bind('guard.adapter.eloquent', 'Szykra\Guard\Adapters\EloquentAdapter');
    }

    private function registerFactories()
    {
        $this->app->bindShared('Szykra\Guard\Factories\RoleFactory', function() {
            return new RoleFactory(config('guard.model.role'));
        });

        $this->app->bindShared('Szykra\Guard\Factories\PermissionFactory', function() {
            return new PermissionFactory(config('guard.model.permission'));
        });
    }

    private function registerCommands()
    {
        $this->commands([
            'Szykra\Guard\Console\CreateRoleConsole',
            'Szykra\Guard\Console\CreatePermissionConsole',
            'Szykra\Guard\Console\LinkPermissionWithRoleConsole'
        ]);
    }

    private function bindingContracts()
    {
        $this->app->bind('Szykra\Guard\Contracts\Permissible', function() {
            return $this->app['auth']->user();
        });

        $this->app->bindShared('Szykra\Guard\Contracts\PermissionChecker', function() {
            $adapter = config('guard.adapter');

            return $this->app->make("guard.adapter.{$adapter}");
        });
    }

    private function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../../../config/guard.php' => 'config/guard.php'
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../../../config/guard.php', 'guard');
    }
}
