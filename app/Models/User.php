<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * Module 01 identity. Deliberately thin: this is the login identity and
 * nothing more. Anything specific to being an employer or a job seeker
 * belongs in that role's own profile table.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasUuids;

    /**
     * Validation for an uploaded profile photo. Lives here so the rule the
     * controller enforces is the same one the tests assert against.
     */
    public const PHOTO_RULES = [
        'nullable',
        'image',
        'mimes:jpg,jpeg,png,webp',
        'max:2048',
        'dimensions:min_width=100,min_height=100',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_BANNED    = 'banned';
    public const STATUS_DELETED   = 'deleted';

    protected $fillable = [
        'email',
        'phone',
        'password_hash',
        'status',
        'first_name',
        'last_name',
        'display_name',
        'avatar_url',
        'preferred_locale',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * The module 01 users table has no remember_token column. Blanking the name
     * turns Laravel's remember-me writes into no-ops instead of letting them
     * fail against a column that does not exist.
     */
    protected $rememberTokenName = '';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'deleted_at' => 'datetime',
            'failed_attempts' => 'integer',
            'password_hash' => 'hashed',
        ];
    }

    /** The credential column is password_hash, not Laravel's default `password`. */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // -----------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['is_primary', 'granted_by', 'granted_at']);
    }

    public function employerProfile(): HasOne
    {
        return $this->hasOne(EmployerProfile::class, 'user_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    // -----------------------------------------------------------------
    // Roles
    // -----------------------------------------------------------------

    /**
     * Admin accounts are back-office identities: only another admin may see
     * one. Everyone else browses a directory with them filtered out, which is
     * why this is a query scope and not a view-level `@if` — hiding a row in
     * Blade still leaves it reachable by URL.
     */
    public function scopeVisibleTo(Builder $query, ?self $viewer): Builder
    {
        if ($viewer?->isAdmin()) {
            return $query;
        }

        return $query->whereDoesntHave('roles', fn (Builder $roles) => $roles->where('code', Role::ADMIN));
    }

    public function scopeWithRole(Builder $query, ?string $code): Builder
    {
        return $code
            ? $query->whereHas('roles', fn (Builder $roles) => $roles->where('code', $code))
            : $query;
    }

    /** Matches the fields the directory actually shows. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($term) {
            $inner->where('display_name', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function hasRole(string $code): bool
    {
        return $this->roles->contains('code', $code);
    }

    public function primaryRole(): ?Role
    {
        return $this->roles->firstWhere('pivot.is_primary', true)
            ?? $this->roles->first();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isEmployer(): bool
    {
        return $this->hasRole(Role::EMPLOYER);
    }

    /**
     * Replace this user's roles, marking one of them primary. Kept here so the
     * "exactly one primary" rule the database enforces is written in one place.
     */
    public function syncRoles(array $codes, ?string $primaryCode = null): void
    {
        $codes = array_values(array_unique(array_filter($codes)));
        $primaryCode ??= $codes[0] ?? null;

        $roles = Role::whereIn('code', $codes)->get();

        // Detached first so a moved primary flag cannot collide with the old row
        // on idx_one_primary_role mid-update.
        $this->roles()->detach();

        foreach ($roles as $role) {
            $this->roles()->attach($role->id, [
                'is_primary' => $role->code === $primaryCode,
                'granted_at' => now(),
            ]);
        }

        $this->unsetRelation('roles');
    }

    // -----------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------

    /**
     * Write the name parts and refresh display_name from them. The parts are
     * what a form collects, but display_name is what the rest of the app (and
     * v_user_access) reads, so the two are only ever set together.
     */
    public function setName(?string $first, ?string $last): void
    {
        $this->first_name = trim((string) $first) ?: null;
        $this->last_name = trim((string) $last) ?: null;
        $this->display_name = self::composeDisplayName($first, $last);
    }

    public static function composeDisplayName(?string $first, ?string $last): ?string
    {
        return trim(trim((string) $first) . ' ' . trim((string) $last)) ?: null;
    }

    /**
     * The column predates uploads and may still hold a full external URL, so
     * anything already absolute is handed back untouched and only stored paths
     * are resolved against the public disk.
     *
     * asset() rather than Storage::url(): the latter builds on APP_URL, which
     * points at the production host and breaks every avatar when the app is
     * served from anywhere else. Same call as Resume::photoUrl().
     */
    public function avatarUrl(): ?string
    {
        if (! $this->avatar_url) {
            return null;
        }

        return str_starts_with($this->avatar_url, 'http://') || str_starts_with($this->avatar_url, 'https://')
            ? $this->avatar_url
            : asset('storage/' . $this->avatar_url);
    }

    /** True only for an upload still present on disk. */
    public function hasUploadedAvatar(): bool
    {
        return $this->avatar_url
            && ! str_starts_with($this->avatar_url, 'http')
            && Storage::disk('public')->exists($this->avatar_url);
    }

    public function displayName(): string
    {
        return $this->display_name ?: ($this->email ?: $this->phone ?: 'Unnamed User');
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->displayName()), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return 'U';
        }

        $first = mb_substr($parts[0], 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first . $last);
    }
}
