<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class JobPost extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';

    /**
     * The panels a post authors. `job_description` used to be `company`; the
     * Company tab now shows the employer's own profile instead, so this panel
     * became extra job copy and renders under Requirements.
     */
    public const TABS = ['description', 'requirements', 'job_description'];

    protected $fillable = [
        'company_id',
        'slug',
        'title',
        'company',
        'location',
        'salary',
        'short_salary',
        'summary',
        'type',
        'mode',
        'experience',
        'department',
        'deadline',
        'applicants',
        'logo',
        'featured',
        'highlighted',
        'status',
        'published_at',
        'tabs',
        'quick_apply_title',
        'quick_apply_text',
        'benefits',
        'highlights',
        'career_opportunities',
    ];

    protected $casts = [
        'deadline' => 'date',
        'published_at' => 'datetime',
        'featured' => 'boolean',
        'highlighted' => 'boolean',
        'applicants' => 'integer',
        'tabs' => 'array',
    ];

    public static function statuses(): array
    {
        return [self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_CLOSED];
    }

    public static function types(): array
    {
        return ['Full-time', 'Part-time', 'Contract', 'Internship', 'Temporary'];
    }

    public static function modes(): array
    {
        return ['On-site', 'Remote', 'Hybrid'];
    }

    /** Keywords the job views switch on to pick the card artwork. */
    public static function logos(): array
    {
        return ['default', 'aba', 'tech', 'design'];
    }

    /**
     * Named employer(), not company(): this model already has a `company`
     * string column, and an attribute of the same name shadows the relation
     * when it is read back — eager loading would silently return the string.
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($term) {
            $inner->where('title', 'like', "%{$term}%")
                ->orWhere('company', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%");
        });
    }

    /**
     * The three "What we can offer" columns, authored one item per line.
     * Empty columns are dropped so the card never renders a blank heading.
     */
    public function offerColumns(): array
    {
        return collect([
            ['key' => 'benefits', 'title' => 'Benefits', 'icon' => 'fa-thumbs-up'],
            ['key' => 'highlights', 'title' => 'Highlights', 'icon' => 'fa-lightbulb'],
            ['key' => 'career_opportunities', 'title' => 'Career Opportunities', 'icon' => 'fa-star'],
        ])
            ->map(fn (array $column) => $column + [
                'items' => $this->linesOf($this->{$column['key']}),
            ])
            ->filter(fn (array $column) => $column['items'] !== [])
            ->values()
            ->all();
    }

    private function linesOf(?string $value): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', (string) $value) ?: []),
            fn (string $line) => $line !== ''
        ));
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public static function makeSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'job';
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

    /** Whole days since publication; 0 for anything published today. */
    public function postedDays(): int
    {
        $from = $this->published_at ?? $this->created_at;

        return $from ? max(0, (int) $from->startOfDay()->diffInDays(now()->startOfDay())) : 0;
    }

    public function postedLabel(): string
    {
        $days = $this->postedDays();

        return match (true) {
            $days === 0 => 'Posted today',
            $days === 1 => 'Posted 1 day ago',
            default => "Posted {$days} days ago",
        };
    }

    public function deadlineLabel(): string
    {
        if (! $this->deadline) {
            return 'Open until filled';
        }

        if ($this->deadline->isPast()) {
            return 'Closed';
        }

        $days = (int) now()->startOfDay()->diffInDays($this->deadline->startOfDay());

        return match (true) {
            $days === 0 => 'Closes today',
            $days === 1 => '1 day left',
            default => "{$days} days left",
        };
    }

    /**
     * The exact array the public job views expect. Keeping this mapping in one
     * place is what let the frontend move onto the database without a single
     * change to the blade templates or the explorer script.
     */
    public function toCatalogArray(): array
    {
        $tabs = $this->tabs ?: [];

        return [
            'id' => $this->slug,
            'title' => $this->title,
            'company' => $this->company,
            'location' => $this->location,
            'salary' => $this->salary ?: 'Salary undisclosed',
            'short_salary' => $this->short_salary ?: ($this->salary ?: 'Negotiable'),
            'summary' => $this->summary ?: '',
            'type' => $this->type,
            'mode' => $this->mode,
            'experience' => $this->experience ?: 'Any experience',
            'department' => $this->department ?: 'General',
            'deadline' => $this->deadlineLabel(),
            'posted' => $this->postedLabel(),
            'posted_days' => $this->postedDays(),
            'applicants' => $this->applicants . ' ' . Str::plural('applicant', $this->applicants),
            'featured' => (bool) $this->featured,
            'highlighted' => (bool) $this->highlighted,
            'logo' => $this->logo ?: 'default',

            // Employer identity for the public job pages. Null/false whenever a
            // post has no company yet, which the views fall back on.
            'company_logo_url' => $this->employer?->logoUrl(),
            'company_cover_url' => $this->employer?->coverUrl(),
            'company_details' => $this->employer?->employerDetails() ?: [],
            'company_address' => $this->employer?->address,
            'company_website' => $this->employer?->website,
            'company_sections' => $this->employer?->profileSections() ?: [],
            'company_verified' => (bool) $this->employer?->hasVerifiedCompliance(),

            'badges' => array_values(array_filter([$this->type, $this->mode])),
            'tabs' => [
                'description' => $this->tabPanel($tabs, 'description', 'Role overview', 'What You Will Do'),
                'requirements' => $this->tabPanel($tabs, 'requirements', 'What we are looking for', 'Core Skills'),
                'job_description' => $this->tabPanel($tabs, 'job_description', 'Job description', 'Good to know'),
            ],
            'detail_items' => [
                ['label' => 'Job type', 'value' => $this->type],
                ['label' => 'Work mode', 'value' => $this->mode],
                ['label' => 'Experience', 'value' => $this->experience ?: 'Any'],
                ['label' => 'Department', 'value' => $this->department ?: 'General'],
            ],
            'quick_apply' => [
                'title' => $this->quick_apply_title ?: 'Quick apply',
                'text' => $this->quick_apply_text ?: 'Applications are reviewed as they arrive.',
            ],
            'offer' => $this->offerColumns(),
        ];
    }

    /** Fills the gaps so a half-completed post still renders a full page. */
    private function tabPanel(array $tabs, string $key, string $fallbackTitle, string $fallbackListTitle): array
    {
        $panel = $tabs[$key] ?? [];

        return [
            'title' => $panel['title'] ?? $fallbackTitle,
            'body' => $panel['body'] ?? ($this->summary ?: ''),
            'list_title' => $panel['list_title'] ?? $fallbackListTitle,
            'list' => array_values(array_filter($panel['list'] ?? [])),
        ];
    }
}
