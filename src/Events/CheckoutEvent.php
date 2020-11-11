<?php

namespace Orkhanahmadov\YandexCheckout\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Orkhanahmadov\YandexCheckout\Models\YandexCheckout;

abstract class CheckoutEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var YandexCheckout
     */
    public $yandexCheckout;

    /**
     * Create a new event instance.
     *
     * @param YandexCheckout $yandexCheckout
     */
    public function __construct(YandexCheckout $yandexCheckout)
    {
        $this->yandexCheckout = $yandexCheckout;
    }
}
