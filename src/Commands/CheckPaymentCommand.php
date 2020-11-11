<?php

namespace Orkhanahmadov\YandexCheckout\Commands;

use Illuminate\Console\Command;
use Orkhanahmadov\YandexCheckout\Models\YandexCheckout as YandexCheckoutModel;
use Orkhanahmadov\YandexCheckout\YandexCheckout;

class CheckPaymentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yandex-checkout:check {paymentId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks payment result for given or all pending payments';

    /**
     * Execute the console command.
     *
     * @param YandexCheckout $yandexCheckout
     */
    public function handle(YandexCheckout $yandexCheckout): void
    {
        if ($paymentKey = $this->argument('paymentId')) {
            $payment = YandexCheckoutModel::where('payment_key', $paymentKey)->firstOrFail();

            $yandexCheckout->paymentInfo($payment);

            return;
        }

        foreach (YandexCheckoutModel::pending()->cursor() as $payment) {
            $yandexCheckout->paymentInfo($payment);
        }
    }
}
