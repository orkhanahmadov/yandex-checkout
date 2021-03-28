<?php

namespace Orkhanahmadov\YandexCheckout;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Orkhanahmadov\YandexCheckout\Models\YandexCheckout;
use YooKassa\Client;
use YooKassa\Request\Payments\AbstractPaymentResponse;
use YooKassa\Request\Payments\CreatePaymentRequestInterface;
use YooKassa\Request\Payments\Payment\CreateCaptureRequestInterface;
use YooKassa\Request\Refunds\CreateRefundRequestInterface;

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

    /**
     * @param  Model  $model
     * @param  CreatePaymentRequestInterface|array  $paymentRequest
     * @param  string|null  $idempotenceKey
     * @return YandexCheckout
     */
    public function createPayment(Model $model, $paymentRequest, ?string $idempotenceKey = null): YandexCheckout
    {
        $paymentResponse = $this->client->createPayment($paymentRequest, $idempotenceKey);

        $yandexCheckoutModel = new YandexCheckout();
        $yandexCheckoutModel->payable_type = get_class($model);
        $yandexCheckoutModel->payable_id = $model->getKey();
        $yandexCheckoutModel->payment_id = $paymentResponse->getId();
        $yandexCheckoutModel->status = $paymentResponse->getStatus();
        $yandexCheckoutModel->response = $paymentResponse->jsonSerialize();
        $yandexCheckoutModel->save();

        $this->dispatchEvent($yandexCheckoutModel, 'created');

        return $yandexCheckoutModel;
    }

    /**
     * @param  YandexCheckout  $model
     * @return YandexCheckout
     */
    public function paymentInfo(YandexCheckout $model): YandexCheckout
    {
        $paymentResponse = $this->client->getPaymentInfo($model->payment_id);

        $model = $this->updateCheckoutModel($model, $paymentResponse);

        $this->dispatchEvent($model, 'checked');
        $this->dispatchEvent($model);

        return $model;
    }

    /**
     * @param  YandexCheckout  $model
     * @param  CreateCaptureRequestInterface|array  $captureRequest
     * @param  string|null  $idempotenceKey
     * @return YandexCheckout
     */
    public function capturePayment(YandexCheckout $model, $captureRequest, ?string $idempotenceKey = null): YandexCheckout
    {
        $captureResponse = $this->client->capturePayment($captureRequest, $model->payment_id, $idempotenceKey);

        $model = $this->updateCheckoutModel($model, $captureResponse);

        $this->dispatchEvent($model);

        return $model;
    }

    /**
     * @param  YandexCheckout  $model
     * @param  string|null  $idempotenceKey
     * @return YandexCheckout
     */
    public function cancelPayment(YandexCheckout $model, ?string $idempotenceKey = null): YandexCheckout
    {
        $cancelResponse = $this->client->cancelPayment($model->payment_id, $idempotenceKey);

        $model = $this->updateCheckoutModel($model, $cancelResponse);

        $this->dispatchEvent($model);

        return $model;
    }

    /**
     * @param  YandexCheckout  $model
     * @param  CreateRefundRequestInterface|array  $refundRequest
     * @param  string|null  $idempotenceKey
     * @return YandexCheckout
     */
    public function refundPayment(YandexCheckout $model, $refundRequest, ?string $idempotenceKey = null)
    {
        $refundResponse = $this->client->createRefund($refundRequest, $idempotenceKey);

        if ($refundResponse->getStatus() === YandexCheckout::STATUS_SUCCEEDED) {
            $this->dispatchEvent($model, 'refunded');

            $model = $this->paymentInfo($model);
        }

        return $model;
    }

    private function updateCheckoutModel(YandexCheckout $model, AbstractPaymentResponse $yandexResponse): YandexCheckout
    {
        $model->status = $yandexResponse->getStatus();
        $model->response = $yandexResponse->jsonSerialize();
        $model->save();

        return $model;
    }

    private function dispatchEvent(YandexCheckout $yandexCheckout, ?string $name = null): void
    {
        $name = $name ?? $yandexCheckout->status;

        if ($event = config("yandex-checkout.events.{$name}")) {
            $event::dispatch($yandexCheckout);
        }
    }
}
