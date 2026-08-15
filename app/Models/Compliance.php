<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Compliance extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    /** Laravel would otherwise pluralise this to `compliances` via "complianc". */
    protected $table = 'compliances';

    protected $fillable = [
        'name',
        'category',
        'reference',
        'logo',
        'status',
        'notes',
        'issued_on',
        'expires_on',
    ];

    protected $casts = [
        'issued_on' => 'date',
        'expires_on' => 'date',
        'verified_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_VERIFIED, self::STATUS_REJECTED];
    }

    public static function categories(): array
    {
        return [
            'Business Licence',
            'Tax Certificate',
            'Labour Registration',
            'Insurance',
            'Data Protection',
            'Other',
        ];
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status && in_array($status, self::statuses(), true)
            ? $query->where('status', $status)
            : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($term) {
            $inner->where('name', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%");
        });
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /**
     * Verified but past its expiry date. Kept separate from the status column
     * so a lapsed document reads as "was verified, needs renewing" rather than
     * silently reverting to pending.
     */
    public function hasExpired(): bool
    {
        return $this->expires_on !== null && $this->expires_on->isPast();
    }

    /**
     * Compared against a future instant rather than with diffInDays: Carbon 3
     * returns that difference signed, so a date months away came back negative
     * and every unexpired record read as "expiring soon".
     */
    public function expiresSoon(int $days = 30): bool
    {
        return $this->expires_on !== null
            && ! $this->hasExpired()
            && $this->expires_on->lessThanOrEqualTo(now()->addDays($days));
    }

    /**
     * asset() rather than Storage::url(): the latter builds on APP_URL, which
     * points at the production host and breaks every logo when the app is
     * served from anywhere else.
     */
    public function logoUrl(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    /** Two letters for the fallback tile when no logo was uploaded. */
    public function initials(): string
    {
        $parts = preg_split('/\s+/u', trim($this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return '?';
        }

        $first = mb_substr($parts[0], 0, 1);
        $second = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first . $second);
    }
}
