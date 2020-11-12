<img src="https://banners.beyondco.de/Yandex%20Checkout.png?theme=light&packageName=orkhanahmadov%2Fyandex-checkout&pattern=architect&style=style_1&description=YooMoney+%28Yandex+Checkout%29+integration+package+for+Laravel&md=1&showWatermark=0&fontSize=100px&images=credit-card&widths=200&heights=200" />

[![Latest Stable Version](https://poser.pugx.org/orkhanahmadov/yandex-checkout/v/stable)](https://packagist.org/packages/orkhanahmadov/yandex-checkout)
[![Latest Unstable Version](https://poser.pugx.org/orkhanahmadov/yandex-checkout/v/unstable)](https://packagist.org/packages/orkhanahmadov/yandex-checkout)
[![Total Downloads](https://img.shields.io/packagist/dt/orkhanahmadov/yandex-checkout)](https://packagist.org/packages/orkhanahmadov/yandex-checkout)
[![GitHub license](https://img.shields.io/github/license/orkhanahmadov/yandex-checkout.svg)](https://github.com/orkhanahmadov/yandex-checkout/blob/master/LICENSE.md)

[![Build Status](https://img.shields.io/travis/orkhanahmadov/yandex-checkout.svg)](https://travis-ci.org/orkhanahmadov/yandex-checkout)
[![Test Coverage](https://api.codeclimate.com/v1/badges/c2a7ab12371ec106ba13/test_coverage)](https://codeclimate.com/github/orkhanahmadov/yandex-checkout/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/c2a7ab12371ec106ba13/maintainability)](https://codeclimate.com/github/orkhanahmadov/yandex-checkout/maintainability)
[![Quality Score](https://img.shields.io/scrutinizer/g/orkhanahmadov/yandex-checkout.svg)](https://scrutinizer-ci.com/g/orkhanahmadov/yandex-checkout)
[![StyleCI](https://github.styleci.io/repos/311930802/shield?branch=master)](https://github.styleci.io/repos/311930802?branch=master)

Easy and complete YooMoney (Yandex Checkout) integration for Laravel

# Todo
- Test coverage

# Table of Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Models](#models)
5. [Commands](#commands)
6. [Events](#events)
7. [Configuration](#configuration)

## Requirements

* PHP **7.3** or above
* Laravel **6** or above 

## Installation

You can install the package via composer:

```bash
composer require orkhanahmadov/yandex-checkout
```

Run this command to publish required migration file:
``` shell script
php artisan vendor:publish --provider="Orkhanahmadov\YandexCheckout\YandexCheckoutServiceProvider" --tag=migrations
```

## Usage

First, set Yandex Checkout shop ID and secret key in `.env` file. You can get these from [YooMoney merchant page](https://yookassa.ru/my).
```
YANDEX_CHECKOUT_SHOP_ID=
YANDEX_CHECKOUT_SECRET_KEY=
```

To use Yandex Checkout service you need instance of `Orkhanahmadov\YandexCheckout\YandexCheckoutService`.
You can instantiate this class using Laravel's service container, for example by injecting to your controller

``` php
use Orkhanahmadov\YandexCheckout\YandexCheckoutService;

class MyController
{
    public function index(YandexCheckoutService $yandexCheckout)
    {
        //
    }
}
```

Or you can use Laravel's service resolver to create instance of the class:

``` php
use Orkhanahmadov\YandexCheckout\YandexCheckoutService;

class MyClass
{
    public function doSomething()
    {
        $goldenpay = app(YandexCheckoutService::class);
        //
    }
}
```

### Available methods:

### `createPayment()`

Creates new payment based on passed credentials and accepts 2 arguments:
* `Model` - Eloquent model payment is associated
* `Payment request` - Payment request that contains amount, currency, etc information

``` php
$product = Product::first();
$yandexCheckout = app(YandexCheckoutService::class);
$yandexCheckout->createPayment(
    $product,
    CreatePaymentRequest::builder()->build([
        'amount' => [
            'value' => 49.99,
            'currency' => 'RUB',
        ],
        'confirmation' => [
            'type' => 'redirect',
            'return_url' => 'https://example.com',
        ],
        'capture' => true,
        'description' => 'Payment for product: ' . $product->id,
    ]
);
```

Method returns created instance of `Orkhanahmadov\YandexCheckout\Models\YandexCheckout` model.

You should use `$confirmation_url` property to get unique payment URL and redirect user to this URL to start payment.

### `paymentInfo()`

Gets information on previously created payment. Accepts single argument:
* `Payment` - This is Yandex Checkout's payment id as a string, or instance of previously created `Orkhanahmadov\YandexCheckout\Models\YandexCheckout` model.

``` php
$product = Product::first();
$yandexCheckout = app(YandexCheckoutService::class);
$payment = $yandexCheckout->createPayment($product, ...);

$paymentInfo = $yandexCheckout->paymentInfo($payment);
// or
$paymentInfo = $yandexCheckout->paymentInfo('1234-ABCD-5678');
```

Method returns updated instance of `Orkhanahmadov\YandexCheckout\Models\YandexCheckout` model with Yandex Checkout's response.

## Models

Package ships with `Orkhanahmadov\YandexCheckout\Models\YandexCheckout` Eloquent model.
Model stores following information for each payment:
* `payment_id` - string, unique payment key provided by Yandex Checkout
* `status` - string, payment status code
* `response` - array, serialized checkout object

Besides usual Eloquent functionality this model also has specific accessors, scopes 
and relationship abilities which you can utilize.

### Accessors

* `succeeded` - Returns `true` if payment marked as "succeeded", `false` otherwise
* `paid` - Returns `true` if checkout is paid, `false` otherwise
* `confirmation_url` - Returns "confirmation URL" which should be used to start payment
* `cancellation_reason` - Returns payment's cancellation/fail reason. Returns `null` when payment is successful or not started yet

### Scopes

* `succeeded()` - Filters "succeeded" payments only
* `pending()` - Filters "pending" payments only. Pending payments are the payments that has status other than "succeeded" or "canceled".

### Relationship

You can make any existing Eloquent model "payable" and attach Yandex Checkouts to it.
Use `Orkhanahmadov\YandexCheckout\Traits\HandlesYandexCheckout` trait in your existing model to establish direct model relationship.

``` php
use Illuminate\Database\Eloquent\Model;
use Orkhanahmadov\YandexCheckout\Traits\HandlesYandexCheckout;

class Product extends Model
{
    use HandlesYandexCheckout;
}
```

Now `Product` model has direct relationship with Yandex Checkouts.
By using `HandlesYandexCheckout` your model also gets access to payment related relationships and payment methods.

#### `createPayment()`

``` php
$product = Product::first();

$paymentRequest = CreatePaymentRequest::builder()->build([
    'amount' => [
      'value' => 49.99,
      'currency' => 'RUB',
    ],
    'confirmation' => [
      'type' => 'redirect',
      'return_url' => 'https://example.com',
    ],
    'capture' => true,
    'description' => 'Payment for product: ' . $product->id,
]);
$product->createPayment($paymentRequest);
```

#### `yandexCheckouts()`

Eloquent relationship method. Return all related Yandex Checkouts.

``` php
$product = Product::first();
$product->yandexCheckouts; // returns collection of related Yandex Checkouts
$product->yandexCheckouts()->where('status', 'succeeded'); // use it as regular Eloquent relationship
$product->yandexCheckouts()->pending(); // use scopes on YandexCheckout model
```

## Commands

Package ships with artisan command for checking payment results.

``` shell script
php artisan yandex-checkout:check
```

Executing above command will loop through all "pending" checkouts and update their models.

Command also accepts payment ID as an argument to check single checkout result.

``` shell script
php artisan yandex-checkout:check 1234-ABCD-5678
```

You can set up a Cron job schedule to frequently check all "pending" checkout.

``` php
protected function schedule(Schedule $schedule)
{
    $schedule->command('yandex-checkout:check')->everyMinute();
}
```

## Events

Package ships with Laravel events which gets fired on specific conditions.

Available event classes:

* `Orkhanahmadov\YandexCheckout\Events\CheckoutCreated` - gets fired when new checkout is created
* `Orkhanahmadov\YandexCheckout\Events\CheckoutSucceeded` - gets fired when payment status changes to "succeeded"
* `Orkhanahmadov\YandexCheckout\Events\CheckoutCanceled` - gets fired when payment status changes to "canceled"
* `Orkhanahmadov\YandexCheckout\Events\CheckoutChecked` - gets fired when payment information is checked

Each event receives instance of `Orkhanahmadov\YandexCheckout\Models\YandexCheckout` Eloquent model as public `$yandexCheckout` property.

You can set up event listeners to trigger when specific payment event gets fired.

``` php
protected $listen = [
    'Orkhanahmadov\YandexCheckout\Events\CheckoutSucceeded' => [
        'App\Listeners\DispatchOrder',
        'App\Listeners\SendInvoice',
    ],
];
```

## Configuration

Run this command to publish package config file:

``` shell script
php artisan vendor:publish --provider="Orkhanahmadov\YandexCheckout\YandexCheckoutServiceProvider" --tag=config
```

Config file contains following settings:

* `shop_id` - Defines Yandex Checkout's "shop ID", defaults to `.env` variable
* `secret_key` - Defines Yandex Checkout's "secret key", defaults to `.env` variable
* `table_name` - Defines name for Yandex Checkout payments database table. Default: "yandex_checkouts"
* `events` - Payment events related settings
    * `created` - "Checkout created" event class. By default uses `Orkhanahmadov\YandexCheckout\Events\CheckoutCreated` class
    * `succeeded` - "Checkout succeeded" event class. By default uses `Orkhanahmadov\YandexCheckout\Events\CheckoutSucceeded` class
    * `canceled` - "Checkout canceled" event class. By default uses `Orkhanahmadov\YandexCheckout\Events\CheckoutCanceled` class
    * `checked` - "Checkout checked" event class. By default uses `Orkhanahmadov\YandexCheckout\Events\CheckoutChecked` class

If you want to use your own event class for specific payment event you can replace class namespace with your class namespace.
Each checkout event receives instance of `Orkhanahmadov\YandexCheckout\Models\YandexCheckout` Eloquent model. 
Because of this, make sure you add payment model as dependency to your event class constructor signature or 
you can extend `Orkhanahmadov\YandexCheckout\Events\CheckoutEvent` class which already has payment model as dependency.

Setting specific payment event to `null` disables that event.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email orkhan@fastmail.com instead of using the issue tracker.

## Credits

- [Orkhan Ahmadov](https://github.com/orkhanahmadov)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
