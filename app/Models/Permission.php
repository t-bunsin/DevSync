<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * The `permissions` / `role_permissions` tables predate this feature — see
 * create_permissions_tables — and were deliberately left unseeded until a
 * module needed them. Job posts was the first; resumes is the second; the
 * applicant inbox is the third. Every module keeps its own `.view` code, so
 * "may download a CV" and "may read the inbox at all" are separate grants.
 */
class Permission extends Model
{
    public const JOB_VIEW = 'job.view';
    public const JOB_CREATE = 'job.create';
    public const JOB_EDIT = 'job.edit';
    public const JOB_DELETE = 'job.delete';
    public const JOB_DOWNLOAD = 'job.download';

    public const RESUME_VIEW = 'resume.view';
    public const RESUME_CREATE = 'resume.create';
    public const RESUME_EDIT = 'resume.edit';
    public const RESUME_DELETE = 'resume.delete';
    public const RESUME_DOWNLOAD = 'resume.download';

    public const APPLICATION_VIEW = 'application.view';
    public const APPLICATION_DOWNLOAD = 'application.download';

    public $timestamps = false;

    protected $fillable = ['code', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /** There's no separate display-name column — derive one from the code's action segment. */
    public function label(): string
    {
        return Str::headline(Str::afterLast($this->code, '.'));
    }
}
