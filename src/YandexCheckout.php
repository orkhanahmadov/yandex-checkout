<?php

namespace Orkhanahmadov\YandexCheckout;

use Illuminate\Contracts\Config\Repository;
use YandexCheckout\Client;
use YandexCheckout\Request\Payments\CreatePaymentRequestInterface;
use YandexCheckout\Request\Payments\CreatePaymentResponse;

class YandexCheckout
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

    public function payment(CreatePaymentRequestInterface $paymentRequest): CreatePaymentResponse
    {
        return $this->client->createPayment($paymentRequest);
    }
}
