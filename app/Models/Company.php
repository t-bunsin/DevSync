<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Company extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'name',
        'slug',
        'registration_no',
        'employer_type',
        'industry',
        'employee_count',
        'description',
        'vision_mission',
        'what_we_do',
        'why_join_us',
        'workplace_culture',
        'status',
        'logo',
        'cover',
        'website',
        'facebook',
        'linkedin',
        'email',
        'phone',
        'address',
    ];

    /** @var bool|null */
    protected $verifiedCompliance = null;

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];
    }

    public static function employerTypes(): array
    {
        return ['Direct Employer', 'Recruitment Agency', 'Outsourcing Partner', 'Non-profit', 'Government'];
    }

    public static function industries(): array
    {
        return [
            'Banking/ Insurance/ Microfinance',
            'Technology/ Software',
            'Retail/ Wholesale',
            'Manufacturing',
            'Construction/ Property',
            'Education/ Training',
            'Healthcare/ Pharmaceutical',
            'Hospitality/ Tourism',
            'Logistics/ Transport',
            'Telecommunications',
            'Media/ Advertising',
            'NGO/ Development',
            'Other',
        ];
    }

    public static function employeeRanges(): array
    {
        return ['1 to 10', '11 to 50', '51 to 100', '101 to 500', '501 to 1000', 'More than 1000'];
    }

    /**
     * The long-form sections shown on the public Company tab, in reading order.
     * Empty ones are dropped so a half-filled profile renders no blank headings.
     */
    public function profileSections(): array
    {
        // `tone` drives the icon chip gradient so the sections read with a
        // rhythm rather than four identical grey headings.
        return collect([
            ['key' => 'vision_mission', 'title' => 'Company Vision and Mission', 'icon' => 'fa-lightbulb', 'tone' => 'teal'],
            ['key' => 'what_we_do', 'title' => 'What we do', 'icon' => 'fa-folder-open', 'tone' => 'ocean'],
            ['key' => 'why_join_us', 'title' => 'Why you should join us', 'icon' => 'fa-id-badge', 'tone' => 'gold'],
            ['key' => 'workplace_culture', 'title' => 'Our workplace and culture', 'icon' => 'fa-building-user', 'tone' => 'navy'],
        ])
            ->map(fn (array $section) => $section + ['body' => trim((string) $this->{$section['key']})])
            ->filter(fn (array $section) => $section['body'] !== '')
            ->values()
            ->all();
    }

    /** Type / industry / headcount, again skipping anything not filled in. */
    public function employerDetails(): array
    {
        return collect([
            'Type' => $this->employer_type,
            'Industry' => $this->industry,
            'No. Employees' => $this->employee_count,
        ])->filter(fn ($value) => filled($value))->all();
    }

    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class);
    }

    public function complianceRecords(): HasMany
    {
        return $this->hasMany(Compliance::class);
    }

    /** Only approved employers may be picked when posting or registering. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($term) {
            $inner->where('name', 'like', "%{$term}%")
                ->orWhere('registration_no', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public static function makeSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function coverUrl(): ?string
    {
        return $this->cover ? asset('storage/' . $this->cover) : null;
    }

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

    /**
     * Compliance is only meaningful once a licence is verified, so this asks
     * whether the employer holds at least one signed-off record.
     *
     * Memoised: eager loading a belongsTo hands every job post the same Company
     * instance, so a list of adverts costs one query rather than one per row.
     */
    public function hasVerifiedCompliance(): bool
    {
        // Prefer a count the caller eager-loaded, which answers this for a whole
        // page of adverts in one query. See JobController::catalog().
        if (array_key_exists('verified_compliance_count', $this->attributes)) {
            return (int) $this->attributes['verified_compliance_count'] > 0;
        }

        return $this->verifiedCompliance ??= $this->complianceRecords()
            ->where('status', Compliance::STATUS_VERIFIED)
            ->exists();
    }
}
