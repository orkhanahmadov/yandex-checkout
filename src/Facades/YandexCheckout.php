<?php

namespace Orkhanahmadov\YandexCheckout\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Orkhanahmadov\YandexCheckout\YandexCheckoutService
 */
class YandexCheckout extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'yandex-checkout';
    }
}
