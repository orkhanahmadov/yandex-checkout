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
     * @param YandexCheckoutService $yandexCheckoutService
     */
    public function handle(YandexCheckoutService $yandexCheckoutService): void
    {
        if ($paymentId = $this->argument('paymentId')) {
            $payment = YandexCheckout::where('payment_id', $paymentId)->firstOrFail();

            $yandexCheckoutService->paymentInfo($payment);

            $this->output->success("Finished checking Yandex Checkout with payment ID: {$paymentId}");

            return;
        }

        $pendingCheckouts = YandexCheckout::pending()->get();
        $progressBar = $this->output->createProgressBar($pendingCheckouts->count());
        $progressBar->start();

        foreach ($pendingCheckouts as $yandexCheckout) {
            $yandexCheckoutService->paymentInfo($yandexCheckout);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->output->success('Finished checking all pending Yandex Checkouts');
    }
}
