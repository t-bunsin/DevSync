<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Module 01 identity. Deliberately thin: this is the login identity and
 * nothing more. Anything specific to being an employer or a job seeker
 * belongs in that role's own profile table.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasUuids;

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

    // -----------------------------------------------------------------
    // Roles
    // -----------------------------------------------------------------

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
