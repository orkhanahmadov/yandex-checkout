<?php

namespace Orkhanahmadov\YandexCheckout\Models\Traits;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Orkhanahmadov\YandexCheckout\Models\YandexCheckout;
use Orkhanahmadov\YandexCheckout\YandexCheckoutService;
use YandexCheckout\Request\Payments\CreatePaymentRequestInterface;

/**
 * Trait Payable.
 *
 * @mixin Model
 */
trait HandlesYandexCheckout
{
    public function yandexCheckouts(): MorphMany
    {
        return $this->morphMany(YandexCheckout::class, 'payable');
    }

    public function createPayment(CreatePaymentRequestInterface $paymentRequest): YandexCheckout
    {
        /** @var YandexCheckoutService $yandexCheckout */
        $yandexCheckout = Container::getInstance()->make(YandexCheckoutService::class);

        return $yandexCheckout->createPayment($this, $paymentRequest);
    }
}
