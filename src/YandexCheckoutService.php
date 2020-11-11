<?php

namespace Orkhanahmadov\YandexCheckout;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Orkhanahmadov\YandexCheckout\Models\YandexCheckout;
use YandexCheckout\Client;
use YandexCheckout\Request\Payments\CreatePaymentRequestInterface;

class YandexCheckoutService
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Repository $config)
    {
        $this->client = new Client();
        $this->client->setAuth(
            $config->get('yandex-checkout.shop_id'),
            $config->get('yandex-checkout.secret_key')
        );
    }

    public function createPayment(Model $model, CreatePaymentRequestInterface $paymentRequest): YandexCheckout
    {
        $paymentResponse = $this->client->createPayment($paymentRequest);

        $yandexCheckoutModel = new YandexCheckout();
        $yandexCheckoutModel->payable_type = get_class($model);
        $yandexCheckoutModel->payable_id = $model->getKey();
        $yandexCheckoutModel->payment_id = $paymentResponse->getId();
        $yandexCheckoutModel->status = $paymentResponse->getStatus();
        $yandexCheckoutModel->response = $paymentResponse->jsonSerialize();
        $yandexCheckoutModel->save();

        $this->dispatchEvent('created', $yandexCheckoutModel);

        return $yandexCheckoutModel;
    }

    /**
     * @param YandexCheckout|string $payment
     * @return YandexCheckout
     */
    public function paymentInfo($payment): YandexCheckout
    {
        if (! $payment instanceof YandexCheckout) {
            $payment = YandexCheckout::where('payment_id', $payment)->firstOrFail();
        }

        $paymentResponse = $this->client->getPaymentInfo($payment->payment_id);

        $payment->status = $paymentResponse->getStatus();
        $payment->response = $paymentResponse->jsonSerialize();
        $payment->save();

        $this->dispatchEvent('checked', $payment);
        $this->dispatchEvent($payment->status, $payment);

        return $payment;
    }

    private function dispatchEvent(string $name, YandexCheckout $yandexCheckout): void
    {
        $event = config("yandex-checkout.events.{$name}");

        if ($event && config('yandex-checkout.events.enabled')) {
            $event::dispatch($yandexCheckout);
        }
    }
}
