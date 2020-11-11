<?php

namespace Orkhanahmadov\YandexCheckout\Commands;

use Illuminate\Console\Command;
use Orkhanahmadov\YandexCheckout\Models\YandexCheckout;
use Orkhanahmadov\YandexCheckout\YandexCheckoutService;

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
     * @param YandexCheckoutService $yandexCheckout
     */
    public function handle(YandexCheckoutService $yandexCheckout): void
    {
        if ($paymentKey = $this->argument('paymentId')) {
            $payment = YandexCheckout::where('payment_key', $paymentKey)->firstOrFail();

            $yandexCheckout->paymentInfo($payment);

            return;
        }

        foreach (YandexCheckout::pending()->cursor() as $payment) {
            $yandexCheckout->paymentInfo($payment);
        }
    }
}
