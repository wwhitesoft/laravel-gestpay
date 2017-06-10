<?php

/**
 *
 * Biscolab Laravel Gestpay - GestpayServiceProvider Class
 * web : robertobelotti.com, github.com/biscolab
 *
 * @package Biscolab\Gestpay
 * @author author: Roberto Belotti - info@robertobelotti.com
 * @license MIT License @ https://github.com/biscolab/laravel-gestpay/blob/master/LICENSE
 */

namespace Biscolab\Gestpay;

use Illuminate\Support\ServiceProvider;
use Biscolab\Gestpay\GestpayBuilder;
use Validator;

class GestpayServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/gestpay.php' => config_path('gestpay.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfigFrom(
            __DIR__ . '/../config/gestpay.php', 'gestpay'
        );
        $this->registerGestpayBuilder();

        $this->app->alias('gestpay', 'Biscolab\Gestpay\GestpayBuilder');
    }

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerGestpayBuilder()
    {
        $this->app->singleton('gestpay', function ($app) {
            return new GestpayBuilder(config('gestpay.shopLogin'), config('gestpay.uicCode'), config('gestpay.test'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['gestpay', 'Biscolab\Gestpay\GestpayBuilder'];
    }
 
}
