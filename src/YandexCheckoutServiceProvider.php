<?php

namespace Orkhanahmadov\YandexCheckout;

use Illuminate\Support\ServiceProvider;

class YandexCheckoutServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('yandex-checkout.php'),
            ], 'config');

            if (! class_exists('CreateYandexCheckoutsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/yandex_checkouts_table.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_yandex_checkouts_table.php'),
                ], 'migrations');
            }

            // Registering package commands.
            // $this->commands([]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'yandex-checkout');

        $this->app->singleton('yandex-checkout', function () {
            return $this->app->make(YandexCheckout::class);
        });
    }
}
