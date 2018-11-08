<?php

namespace Mostafaznv\Recaptcha;

use Illuminate\Support\ServiceProvider;

class RecaptchaServiceProvider extends ServiceProvider
{
    const VERSION = '1.0.0';

    public function boot()
    {
        $this->bootResources();

        $app = $this->app;

        $app['validator']->extend('recaptcha', function($attribute, $value, $paramaters) use ($app) {
            $action = isset($paramaters[0]) ? $paramaters[0] : null;
            $score = isset($paramaters[1]) ? (float)$paramaters[1] : config('recaptcha.score');

            return $app['recaptcha']->verify($value, $action, $score);
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'recaptcha');

        $this->app->singleton('recaptcha', function() {
            return new Recaptcha;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Recaptcha::class];
    }

    /**
     * Boot Resources.
     *
     * view, and publishes
     */
    protected function bootResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', 'recaptcha');

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config/config.php' => config_path('recaptcha.php')], 'config');
            $this->publishes([__DIR__ . '/../views/' => resource_path('views/vendor/recaptcha')]);
        }
    }
}