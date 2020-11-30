<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Yandex Checkout Shop ID
    |--------------------------------------------------------------------------
    |
    | Shop ID for Yandex Checkout. You can get it from: https://yookassa.ru/my/shop-settings
    |
    */

    'shop_id' => env('YANDEX_CHECKOUT_SHOP_ID'),

    /*
    |--------------------------------------------------------------------------
    | Yandex Checkout Secret key
    |--------------------------------------------------------------------------
    |
    | Secret key for Yandex Checkout. You can get it from: https://yookassa.ru/my/merchant/integration/api-keys
    |
    */

    'secret_key' => env('YANDEX_CHECKOUT_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Database table name
    |--------------------------------------------------------------------------
    |
    | Defines name for table in database.
    |
    */

    'table_name' => 'yandex_checkouts',

    /*
    |--------------------------------------------------------------------------
    | Checkout events
    |--------------------------------------------------------------------------
    |
    | Defines event types and event classes for each payment event.
    | Each event receives instance of payment related "Orkhanahmadov\YandexCheckout\Models\YandexCheckout" model.
    |
    */

    'events' => [

        /*
        |--------------------------------------------------------------------------
        | Checkout event types
        |--------------------------------------------------------------------------
        |
        | Lists all possible event types and related event classes.
        | If you want to use your own event classes for specific events, you can replace them here.
        |
        | Each event class needs to implement "Orkhanahmadov\YandexCheckout\Events\CheckoutEvent" class.
        |
        */

        'created' => \Orkhanahmadov\YandexCheckout\Events\CheckoutCreated::class,
        'checked' => \Orkhanahmadov\YandexCheckout\Events\CheckoutChecked::class,
        'succeeded' => \Orkhanahmadov\YandexCheckout\Events\CheckoutSucceeded::class,
        'canceled' => \Orkhanahmadov\YandexCheckout\Events\CheckoutCanceled::class,
        'refunded' => \Orkhanahmadov\YandexCheckout\Events\CheckoutRefunded::class,
    ]
];
