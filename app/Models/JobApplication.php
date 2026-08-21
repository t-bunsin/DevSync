<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JobApplication extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_HIRED = 'hired';

    /**
     * Deliberately narrow: which post, which account and which CV an
     * application belongs to are decided by the controller, never by the
     * submitted form, so those columns are set explicitly instead.
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'message',
        'status',
        'note',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    /** The formats an uploaded CV may take, and its ceiling in kilobytes. */
    public const CV_MIMES = ['pdf', 'doc', 'docx'];
    public const CV_MAX_KB = 5120;

    /**
     * Deleting the row takes the uploaded file with it. Without this every
     * deleted application would leave its CV behind on disk for good.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $application) {
            $application->deleteCvFile();
        });
    }

    public static function statuses(): array
    {
        // Pipeline order: the review control renders them in this sequence.
        return [
            self::STATUS_NEW,
            self::STATUS_REVIEWING,
            self::STATUS_SHORTLISTED,
            self::STATUS_HIRED,
            self::STATUS_REJECTED,
        ];
    }

    /**
     * The three badge tones the back office ships, mapped over five states —
     * the same reuse the resume and job post lists make of them.
     */
    public function statusTone(): string
    {
        return match ($this->status) {
            self::STATUS_SHORTLISTED, self::STATUS_HIRED => 'verified',
            self::STATUS_REJECTED => 'rejected',
            default => 'pending',
        };
    }

    /**
     * The accent each pipeline state wears in the review control. Wider than
     * statusTone(), which only has the three badge tones the back office ships
     * to work with — here each step is worth telling apart at a glance.
     */
    public static function statusTones(): array
    {
        return [
            self::STATUS_NEW => 'neutral',
            self::STATUS_REVIEWING => 'amber',
            self::STATUS_SHORTLISTED => 'blue',
            self::STATUS_HIRED => 'teal',
            self::STATUS_REJECTED => 'danger',
        ];
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    /** The account that applied; null once that account is deleted. */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The CV attached at apply time, when the candidate had one. */
    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
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
            $inner->where('full_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    /**
     * Applied-date window for the back office inquiry filter, mirroring
     * JobPost::scopePostedBetween(). Reads the same date the Applied column
     * shows — `applied_at`, falling back to `created_at` for any row written
     * before that stamp existed. Both bounds are inclusive and independent.
     */
    public function scopeAppliedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereRaw('DATE(COALESCE(applied_at, created_at)) >= ?', [$from]))
            ->when($to, fn (Builder $q) => $q->whereRaw('DATE(COALESCE(applied_at, created_at)) <= ?', [$to]));
    }

    /** Two letters for the avatar tile, mirroring Resume::initials(). */
    public function initials(): string
    {
        $parts = preg_split('/\s+/u', trim((string) $this->full_name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return '?';
        }

        $first = mb_substr($parts[0], 0, 1);
        $second = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first . $second);
    }

    /**
     * Whether this application carries a CV at all — by either route the apply
     * form accepts: a file the candidate uploaded, or a resume they built in
     * the dashboard.
     */
    public function hasCv(): bool
    {
        return $this->cv_path !== null || $this->resume !== null;
    }

    /** What to call the CV in a link; the resume falls back to its owner. */
    public function cvLabel(): ?string
    {
        return match (true) {
            $this->cv_path !== null => $this->cv_name ?: basename($this->cv_path),
            $this->resume !== null => $this->resume->full_name . ' — resume',
            default => null,
        };
    }

    public function deleteCvFile(): void
    {
        if ($this->cv_path) {
            Storage::disk('local')->delete($this->cv_path);
        }
    }

    /**
     * The candidate's own photo: their resume photo when they built one,
     * otherwise the avatar on their account.
     */
    public function photoUrl(): ?string
    {
        return $this->resume?->photoUrl() ?? $this->candidate?->avatarUrl();
    }

    public function appliedAgo(): string
    {
        $at = $this->applied_at ?? $this->created_at;

        if (! $at) {
            return 'unknown';
        }

        $days = max(0, (int) $at->startOfDay()->diffInDays(now()->startOfDay()));

        return match (true) {
            $days === 0 => 'today',
            $days === 1 => '1 day ago',
            default => "{$days} days ago",
        };
    }
}
