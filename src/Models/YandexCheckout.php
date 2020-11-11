<?php

namespace Orkhanahmadov\YandexCheckout\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

/**
 * @property int $id
 * @property string $payable_type
 * @property int $payable_id
 * @property string $payment_id
 * @property string $status
 * @property array $response
 * @property bool $paid
 * @property string|null $confirmation_url
 * @method static Builder successful()
 */
class YandexCheckout extends Model
{
    public const STATUS_SUCCESSFUL = 'success';

    protected $guarded = [];

    protected $casts = [
        'response' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        $this->setTable(Config::get('yandex-checkout.table_name'));

        parent::__construct($attributes);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getPaidAttribute(): bool
    {
        return Arr::get($this->response, 'paid', false);
    }

    public function getConfirmationUrlAttribute(): ?string
    {
        return Arr::get($this->response, 'confirmation.confirmation_url');
    }

    public function scopeSuccessful(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUS_SUCCESSFUL);
    }
}
