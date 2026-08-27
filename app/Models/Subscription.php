<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_FAILED = 'failed';

    /** Every value the status enum accepts, in the order the billing register lists them. */
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_PENDING,
        self::STATUS_FAILED,
        self::STATUS_CANCELED,
    ];

    protected $fillable = [
        'user_id',
        'tran_id',
        'plan_id',
        'billing_period',
        'payment_option',
        'amount',
        'status',
        'started_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'started_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The config/plans.php tier this subscription is on, or null if the plan was since removed. */
    public function plan(): ?array
    {
        return collect(config('plans.tiers'))->firstWhere('id', $this->plan_id);
    }
}
