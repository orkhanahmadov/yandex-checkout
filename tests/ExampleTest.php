<?php

namespace Orkhanahmadov\YandexCheckout\Tests;

use Orchestra\Testbench\TestCase;
use Orkhanahmadov\YandexCheckout\YandexCheckoutServiceProvider;

class ExampleTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [YandexCheckoutServiceProvider::class];
    }

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
