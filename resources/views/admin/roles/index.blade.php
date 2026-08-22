@extends('layouts.admin')

@section('title', 'User Role & Permission | KH-WORKS Admin')
@section('body-class', 'kh-user-create-page')

@push('styles')
    <link href="{{ asset('css/users-create.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
    <style>
        /* The permission table borrows the admin table component (kh-bo__table)
           rather than a bespoke grid — it already handles both themes. */
        .kh-role__table-card { padding: 0; }
        .kh-role__about { max-width: 300px; color: var(--kh-muted, #6b7280); font-size: .85rem; }
        .kh-role__enforcement { display: block; margin-top: .35rem; font-size: .76rem; }
        .kh-role__perm-col { text-align: center; }
        html[data-admin-theme="dark"] .kh-role__table-card .kh-bo__table-wrap { background: transparent; }
    </style>
@endpush

@section('main-content')
    <div class="kh-user-create">
        <div class="kh-user-create__shell">
            <header class="kh-user-create__page-head">
                <div class="kh-user-create__heading">
                    <a class="kh-user-create__back" href="{{ route('home') }}" aria-label="Back to back office">
                        <i data-feather="arrow-left" aria-hidden="true"></i>
                    </a>

                    <span class="kh-user-create__heading-icon" aria-hidden="true">
                        <i data-feather="shield"></i>
                    </span>

                    <div>
                        <span class="kh-user-create__eyebrow">User access</span>
                        <h1>User Role &amp; Permission</h1>
                        <p>Who holds each role, how the app enforces it, and what they may do to job posts and resumes.</p>
                    </div>
                </div>
            </header>

            @if (session('success'))
                <div class="kh-bo__flash" role="status" style="margin-bottom: 1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="kh-user-create__layout">
                <section class="kh-user-create__card kh-user-create__form-card kh-role__table-card" aria-labelledby="roles-permissions-title">
                    <div class="kh-user-create__card-head">
                        <div>
                            <span class="kh-user-create__card-kicker">Roles</span>
                            <h2 id="roles-permissions-title">Roles &amp; permissions</h2>
                            <p>{{ $roles->count() }} {{ \Illuminate\Support\Str::plural('role', $roles->count()) }} — only the grouped columns on the right are assignable.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('roles.permissions.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="kh-bo__table-wrap">
                            <table class="kh-bo__table">
                                <thead>
                                    <tr>
                                        <th scope="col" rowspan="2">Role</th>
                                        <th scope="col" rowspan="2">About</th>
                                        <th scope="col" rowspan="2">Users</th>
                                        @foreach ($permissionGroups as $group)
                                            <th scope="colgroup" colspan="{{ count($group['permissions']) }}" class="kh-role__perm-col">{{ $group['label'] }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach ($permissionGroups as $group)
                                            @foreach ($group['permissions'] as $permission)
                                                <th scope="col" class="kh-role__perm-col" title="{{ $permission->description }}">{{ $permission->label() }}</th>
                                            @endforeach
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                        @php($grantedIds = $role->permissions->pluck('id'))
                                        <tr>
                                            <td>
                                                <div class="kh-bo__identity">
                                                    <span @class([
                                                        'kh-user-create__role-dot',
                                                        'kh-user-create__role-dot--admin' => $role->code === 'admin',
                                                        'kh-user-create__role-dot--manager' => $role->code === 'employer',
                                                        'kh-user-create__role-dot--user' => $role->code === 'employee',
                                                    ]) style="flex-shrink: 0;"></span>
                                                    <div>
                                                        <span class="kh-bo__name">{{ $role->name_en }}</span>
                                                        <span class="kh-bo__ref">
                                                            {{ $role->code }}
                                                            @if ($role->name_km)
                                                                · {{ $role->name_km }}
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="kh-role__about">
                                                {{ $role->description ?: '—' }}
                                                <span class="kh-role__enforcement">{{ $role->enforcement }}</span>
                                            </td>
                                            <td>
                                                {{ $role->users_count }}
                                                <span class="kh-bo__ref">
                                                    <a href="{{ route('users', ['role' => $role->code]) }}">View users</a>
                                                </span>
                                            </td>
                                            @foreach ($permissionGroups as $group)
                                                @foreach ($group['permissions'] as $permission)
                                                    <td class="kh-role__perm-col">
                                                        @if ($role->code === \App\Models\Role::ADMIN)
                                                            <input type="checkbox" checked disabled title="Admin always has every permission">
                                                        @elseif ($role->code === \App\Models\Role::EMPLOYEE)
                                                            <input type="checkbox" disabled title="No route reaches employee yet">
                                                        @else
                                                            <input type="checkbox" name="permissions[{{ $role->id }}][]" value="{{ $permission->id }}"
                                                                @checked($grantedIds->contains($permission->id))>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="kh-user-create__actions">
                            <p>
                                <i data-feather="shield" aria-hidden="true"></i>
                                Changes apply immediately to live requests.
                            </p>
                            <div class="kh-user-create__button-row">
                                <button class="kh-user-create__btn kh-user-create__btn--primary" type="submit">
                                    <i data-feather="check" aria-hidden="true"></i>
                                    <span>Save permissions</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <aside class="kh-user-create__side" aria-label="Permission guidance">
                    <section class="kh-user-create__card kh-user-create__guide-card">
                        <span class="kh-user-create__card-kicker">Before you save</span>
                        <h2>Permission tips</h2>
                        <p>What actually changes when you check or uncheck a box.</p>

                        <ol class="kh-user-create__checklist">
                            <li>
                                <span aria-hidden="true"><i data-feather="briefcase"></i></span>
                                <div>
                                    <strong>Job posts and resumes only, for now</strong>
                                    <small>These grouped columns are the only assignable permissions. Everything else is still fixed in code.</small>
                                </div>
                            </li>
                            <li>
                                <span aria-hidden="true"><i data-feather="shield"></i></span>
                                <div>
                                    <strong>Admin is never restricted</strong>
                                    <small>Admin always has every permission, regardless of this matrix.</small>
                                </div>
                            </li>
                            <li>
                                <span aria-hidden="true"><i data-feather="zap"></i></span>
                                <div>
                                    <strong>Takes effect immediately</strong>
                                    <small>Saved changes are checked live by JobPostController and ResumeController on the next request.</small>
                                </div>
                            </li>
                        </ol>
                    </section>

                    <section class="kh-user-create__card kh-user-create__role-card">
                        <div class="kh-user-create__role-head">
                            <span aria-hidden="true"><i data-feather="users"></i></span>
                            <div>
                                <span class="kh-user-create__card-kicker">Access guide</span>
                                <h2>Role legend</h2>
                            </div>
                        </div>

                        <dl class="kh-user-create__role-list">
                            @foreach ($roles as $role)
                                <div>
                                    <dt>
                                        <span @class([
                                            'kh-user-create__role-dot',
                                            'kh-user-create__role-dot--admin' => $role->code === 'admin',
                                            'kh-user-create__role-dot--manager' => $role->code === 'employer',
                                            'kh-user-create__role-dot--user' => $role->code === 'employee',
                                        ])></span>{{ $role->name_en }}
                                    </dt>
                                    <dd>{{ $role->description }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                </aside>
            </div>
        </div>
    </div>
@endsection
