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

            return;
        }

        $pendingCheckouts = YandexCheckout::pending()->get();
        $this->output->createProgressBar($pendingCheckouts->count());

        foreach ($pendingCheckouts as $yandexCheckout) {
            $yandexCheckoutService->paymentInfo($yandexCheckout);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->output->success('Finished checking all pending Yandex Checkouts');
    }
}
