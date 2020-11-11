<?php

namespace Orkhanahmadov\YandexCheckout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;

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
