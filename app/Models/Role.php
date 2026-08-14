<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    public const ADMIN    = 'admin';
    public const EMPLOYER = 'employer';
    public const EMPLOYEE = 'employee';

    /** Reference data seeded by migration; the table carries no timestamps. */
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name_en',
        'name_km',
        'description',
        'is_system',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['is_primary', 'granted_by', 'granted_at']);
    }

    /**
     * Label in the viewer's locale, falling back to English when no Khmer
     * name is present.
     */
    public function label(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'kh' || $locale === 'km'
            ? ($this->name_km ?: $this->name_en)
            : $this->name_en;
    }
}
