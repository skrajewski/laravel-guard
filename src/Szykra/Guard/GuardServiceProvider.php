<?php namespace Szykra\Guard;

use Illuminate\Support\ServiceProvider;

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

    }
}