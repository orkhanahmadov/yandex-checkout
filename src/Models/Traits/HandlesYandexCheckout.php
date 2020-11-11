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
    public function checkouts(): MorphMany
    {
        return $this->morphMany(YandexCheckoutModel::class, 'payable');
    }

    public function successfulCheckouts(): MorphMany
    {
        return $this->morphMany(YandexCheckoutModel::class, 'payable')->successful();
    }

    public function createPayment(CreatePaymentRequestInterface $paymentRequest): YandexCheckoutModel
    {
        /** @var YandexCheckout $yandexCheckout */
        $yandexCheckout = Container::getInstance()->make(YandexCheckout::class);

        $paymentResponse = $yandexCheckout->payment($paymentRequest);

        $yandexCheckoutModel = new YandexCheckoutModel();
        $yandexCheckoutModel->payable_type = self::class;
        $yandexCheckoutModel->payable_id = $this->getKey();
        $yandexCheckoutModel->payment_id = $paymentResponse->getId();
        $yandexCheckoutModel->status = $paymentResponse->getStatus();
        $yandexCheckoutModel->response = $paymentResponse->jsonSerialize();
        $yandexCheckoutModel->save();

        return $yandexCheckoutModel;
    }
}
