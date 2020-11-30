<?php

namespace Orkhanahmadov\YandexCheckout\Models;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Orkhanahmadov\YandexCheckout\YandexCheckoutService;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequestInterface;
use YandexCheckout\Request\Refunds\CreateRefundRequestInterface;

/**
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property string $payment_id
 * @property string $status
 * @property array $response
 * @property bool $succeeded
 * @property bool $paid
 * @property string|null $confirmation_url
 * @property string|null $cancellation_reason
 * @method static Builder succeeded()
 * @method static Builder pending()
 */
class YandexCheckout extends Model
{
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_CANCELED = 'canceled';

    protected $guarded = [];

    protected $casts = [
        'response' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('yandex-checkout.table_name'));

        parent::__construct($attributes);
    }

    /**
     * @param  CreateCaptureRequestInterface|array  $captureRequest
     * @return YandexCheckout
     */
    public function capturePayment($captureRequest): YandexCheckout
    {
        /** @var YandexCheckoutService $yandexCheckout */
        $yandexCheckout = Container::getInstance()->make(YandexCheckoutService::class);

        $yandexCheckout->capturePayment($this, $captureRequest, $this->payable->yandexCheckoutIdempotenceKey());

        return $this->refresh();
    }

    public function cancelPayment(): YandexCheckout
    {
        /** @var YandexCheckoutService $yandexCheckout */
        $yandexCheckout = Container::getInstance()->make(YandexCheckoutService::class);

        $yandexCheckout->cancelPayment($this, $this->payable->yandexCheckoutIdempotenceKey());

        return $this->refresh();
    }

    /**
     * @param  CreateRefundRequestInterface|array  $refundRequest
     * @return YandexCheckout
     */
    public function refundPayment($refundRequest): YandexCheckout
    {
        /** @var YandexCheckoutService $yandexCheckout */
        $yandexCheckout = Container::getInstance()->make(YandexCheckoutService::class);

        $yandexCheckout->refundPayment($this, $refundRequest, $this->payable->yandexCheckoutIdempotenceKey());

        return $this->refresh();
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSucceededAttribute(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    public function getPaidAttribute(): bool
    {
        return Arr::get($this->response, 'paid', false);
    }

    public function getRefundableAttribute(): bool
    {
        return Arr::get($this->response, 'refundable', false);
    }

    public function getDescriptionAttribute(): bool
    {
        return Arr::get($this->response, 'description');
    }

    public function getCapturedAtAttribute(): bool
    {
        return Arr::get($this->response, 'captured_at');
    }

    public function getExpiresAtAttribute(): bool
    {
        return Arr::get($this->response, 'expires_at');
    }

    public function getConfirmationUrlAttribute(): ?string
    {
        return Arr::get($this->response, 'confirmation.confirmation_url');
    }

    public function getCancellationReasonAttribute(): ?string
    {
        return Arr::get($this->response, 'cancellation_details.reason');
    }

    public function scopeSucceeded(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUS_SUCCEEDED);
    }

    public function scopePending(Builder $builder): Builder
    {
        return $builder->whereNotIn('status', [
            self::STATUS_SUCCEEDED,
            self::STATUS_CANCELED,
        ]);
    }
}
