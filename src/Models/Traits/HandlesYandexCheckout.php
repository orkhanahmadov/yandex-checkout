<?php

namespace Orkhanahmadov\YandexCheckout\Models\Traits;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Orkhanahmadov\YandexCheckout\Models\YandexCheckout as YandexCheckoutModel;
use Orkhanahmadov\YandexCheckout\YandexCheckout;
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
        return $this->morphMany(YandexCheckoutModel::class, 'payable');
    }

    public function successfulYandexCheckouts(): MorphMany
    {
        return $this->morphMany(YandexCheckoutModel::class, 'payable')->succeeded();
    }

    public function createPayment(CreatePaymentRequestInterface $paymentRequest): YandexCheckoutModel
    {
        /** @var YandexCheckout $yandexCheckout */
        $yandexCheckout = Container::getInstance()->make(YandexCheckout::class);

        return $yandexCheckout->createPayment($this, $paymentRequest);
    }
}
