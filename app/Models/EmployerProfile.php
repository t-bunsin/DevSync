<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PLACEHOLDER — see the create_employer_profiles_table migration. Holds the one
 * employer-specific field the app collects today, until the real profile table
 * arrives with the company module.
 */
class EmployerProfile extends Model
{
    protected $table = 'employer_profiles';

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'company_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
