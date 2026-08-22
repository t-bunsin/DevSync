<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

/**
 * The six-digit code mailed to a new account's address, and the rules around
 * guessing it. One row per user: issuing a new code overwrites the old one, so
 * only the most recent code is ever live.
 */
class EmailVerificationCode extends Model
{
    /** How long a code stays usable, and how long before another may be sent. */
    public const TTL_MINUTES = 10;
    public const RESEND_SECONDS = 60;
    public const MAX_ATTEMPTS = 5;

    protected $fillable = ['user_id', 'code_hash', 'attempts', 'expires_at', 'sent_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mints a code, stores only its hash, and hands the plaintext back to the
     * caller once — to be mailed and then forgotten.
     */
    public static function issueFor(User $user): string
    {
        // str_pad, not a 100000–999999 range: a leading zero is a valid digit
        // and dropping it would quietly shrink the keyspace by a tenth.
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        static::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::TTL_MINUTES),
                'sent_at' => now(),
            ]
        );

        return $code;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isSpent(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /** Seconds until another code may be sent; 0 once the wait is over. */
    public function resendWaitSeconds(): int
    {
        return (int) max(0, self::RESEND_SECONDS - (int) $this->sent_at->diffInSeconds(now()));
    }

    /**
     * Checks a guess and counts it. The ceiling is tested before the increment,
     * so MAX_ATTEMPTS means five real guesses rather than four and a wasted one.
     */
    public function matches(string $code): bool
    {
        if ($this->isExpired() || $this->isSpent()) {
            return false;
        }

        $this->increment('attempts');

        return Hash::check($code, $this->code_hash);
    }
}
