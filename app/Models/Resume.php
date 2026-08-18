<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Resume extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * The repeating sections, and the fields each row may carry. The controller
     * normalises submitted rows against this map, so a hand-crafted POST cannot
     * push arbitrary keys into the JSON columns.
     */
    public const SECTIONS = [
        'work_history' => ['role', 'company', 'location', 'started_on', 'ended_on', 'bullets'],
        'education' => ['degree', 'field', 'institution', 'location', 'graduated_on'],
        'certifications' => ['name', 'issuer'],
        'languages' => ['name', 'level'],
    ];

    /** Rows in these sections keep a `bullets` list rather than a single value. */
    public const LIST_FIELDS = ['bullets'];

    protected $fillable = [
        'full_name',
        'headline',
        'email',
        'phone',
        'location',
        'summary',
        'status',
    ];

    protected $casts = [
        'work_history' => 'array',
        'education' => 'array',
        'certifications' => 'array',
        'skills' => 'array',
        'languages' => 'array',
    ];

    public static function statuses(): array
    {
        return [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_ARCHIVED];
    }

    /** The CEFR ladder the printed layout shows beside each language. */
    public static function languageLevels(): array
    {
        return [
            'Beginner (A1)',
            'Elementary (A2)',
            'Intermediate (B1)',
            'Upper intermediate (B2)',
            'Advanced (C1)',
            'Native (C2)',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status && in_array($status, self::statuses(), true)
            ? $query->where('status', $status)
            : $query;
    }

    /**
     * Matches the header fields only. The JSON sections are deliberately left
     * out: `LIKE` against a JSON blob matches punctuation and key names, which
     * reads to the user as the search returning rows for no visible reason.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($term) {
            $inner->where('full_name', 'like', "%{$term}%")
                ->orWhere('headline', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /** Rows of one repeating section, always an array even when never set. */
    public function section(string $name): array
    {
        return array_key_exists($name, self::SECTIONS) ? (array) ($this->{$name} ?? []) : [];
    }

    public function skillList(): array
    {
        return (array) ($this->skills ?? []);
    }

    /** How much of the document is filled in, for the "3 of 5 sections" hint. */
    public function filledSectionCount(): int
    {
        $filled = count(array_filter(
            array_keys(self::SECTIONS),
            fn (string $name) => $this->section($name) !== []
        ));

        return $filled + ($this->skillList() === [] ? 0 : 1);
    }

    /**
     * The month inputs store a bare "YYYY-MM"; the printed layout wants
     * "01/2021". An unparseable or empty value renders as nothing rather than
     * as a fallback date, so a half-filled row never invents a timeline.
     */
    public static function formatMonth(?string $value): string
    {
        return preg_match('/^(\d{4})-(\d{2})$/', (string) $value, $m) === 1
            ? "{$m[2]}/{$m[1]}"
            : '';
    }

    public function hasPhoto(): bool
    {
        return $this->photo !== null && Storage::disk('public')->exists($this->photo);
    }

    /**
     * asset() rather than Storage::url(): the latter builds on APP_URL, which
     * points at the production host and breaks every photo when the app is
     * served from anywhere else. Same call as Compliance::logoUrl().
     */
    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    /**
     * The photo inlined as a data URI, for the PDF.
     *
     * dompdf resolves image paths against a chroot of public_path(), and
     * public/storage is a symlink out to storage/app/public — so a plain path
     * or URL resolves outside the chroot and renders as a broken image. Base64
     * sidesteps the filesystem entirely.
     */
    public function photoDataUri(): ?string
    {
        if (! $this->hasPhoto()) {
            return null;
        }

        $disk = Storage::disk('public');
        $mime = $disk->mimeType($this->photo) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($disk->get($this->photo));
    }

    /** Two letters for the avatar tile, mirroring Compliance::initials(). */
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
}
