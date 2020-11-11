<?php

return [
    'table_name' => 'yandex_checkouts',

    'shop_id' => env('YANDEX_CHECKOUT_SHOP_ID'),
    'secret_key' => env('YANDEX_CHECKOUT_SECRET_KEY'),

    'events' => [
        'enabled' => true,

        'created' => \Orkhanahmadov\YandexCheckout\Events\CheckoutCreated::class,
        'checked' => \Orkhanahmadov\YandexCheckout\Events\CheckoutChecked::class,
        'succeeded' => \Orkhanahmadov\YandexCheckout\Events\CheckoutSucceeded::class,
        'canceled' => \Orkhanahmadov\YandexCheckout\Events\CheckoutCanceled::class,
    ]
];
