@extends('layouts.admin')

@section('title', 'User Role & Permission | ZIN-WORKS Admin')
@section('body-class', 'kh-user-create-page')

@push('styles')
    <link href="{{ asset('css/users-create.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/backoffice.css') }}?v={{ filemtime(public_path('css/backoffice.css')) }}" rel="stylesheet" />
    <link href="{{ asset('css/roles-permissions.css') }}?v={{ filemtime(public_path('css/roles-permissions.css')) }}" rel="stylesheet" />
@endpush

@php
    $assignableIds = collect($permissionGroups)->flatMap(fn ($group) => collect($group['permissions'])->pluck('id'));
    $assignablePermissions = $assignableIds->count();
    $activeRole = $roles->first();
@endphp

@section('main-content')
    <div class="kh-user-create">
        <div class="kh-user-create__shell">
            <header class="kh-user-create__page-head kh-perm__head">
                <div class="kh-user-create__heading">
                    <a class="kh-user-create__back" href="{{ route('home') }}" aria-label="{{ __('ui.bo.roles.back_to_bo') }}">
                        <i data-feather="arrow-left" aria-hidden="true"></i>
                    </a>

                    <span class="kh-user-create__heading-icon" aria-hidden="true">
                        <i data-feather="shield"></i>
                    </span>

                    <div>
                        <span class="kh-user-create__eyebrow">{{ __('ui.bo.roles.kicker') }}</span>
                        <h1>{{ __('ui.bo.roles.title') }}</h1>
                        <p>{{ __('ui.bo.roles.subtitle') }}</p>
                    </div>
                </div>

                {{-- Value above label, but dt/dd stay in reading order so a screen
                     reader still hears "Roles, 3" rather than a bare number. --}}
                @php
                    $userTotal = $roles->sum('users_count');
                    $stats = [
                        ['icon' => 'shield', 'value' => $roles->count(), 'label' => trans_choice('ui.bo.roles.stat_roles', $roles->count())],
                        ['icon' => 'layers', 'value' => count($permissionGroups), 'label' => trans_choice('ui.bo.roles.stat_modules', count($permissionGroups))],
                        ['icon' => 'toggle-right', 'value' => $assignablePermissions, 'label' => trans_choice('ui.bo.roles.stat_functions', $assignablePermissions)],
                        ['icon' => 'users', 'value' => $userTotal, 'label' => trans_choice('ui.bo.roles.stat_users', $userTotal)],
                    ];
                @endphp

                <dl class="kh-perm__stats">
                    @foreach ($stats as $stat)
                        <div class="kh-perm__stat">
                            <span class="kh-perm__stat-icon" aria-hidden="true">
                                <i data-feather="{{ $stat['icon'] }}"></i>
                            </span>
                            <div class="kh-perm__stat-body">
                                <dt>{{ $stat['label'] }}</dt>
                                <dd>{{ $stat['value'] }}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </header>

            @if (session('success'))
                <div class="kh-bo__flash" role="status" style="margin-bottom: 1rem;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form class="kh-perm" method="POST" action="{{ route('roles.permissions.update') }}" data-perm-root>
                @csrf
                @method('PATCH')

                {{-- Roles are tabs rather than table rows: a role's whole permission set
                     fits on screen at once, however many modules ship later. --}}
                <div class="kh-perm__roles" role="tablist" aria-label="{{ __('ui.bo.roles.roles_tablist') }}">
                    @foreach ($roles as $role)
                        @php($granted = $role->permissions->pluck('id')->intersect($assignableIds)->count())
                        <button type="button"
                                class="kh-perm__role"
                                role="tab"
                                id="role-tab-{{ $role->id }}"
                                aria-controls="role-panel-{{ $role->id }}"
                                aria-selected="{{ $role->id === $activeRole->id ? 'true' : 'false' }}"
                                data-role-tab="{{ $role->id }}">
                            <span @class([
                                'kh-perm__role-dot',
                                'kh-perm__role-dot--admin' => $role->code === \App\Models\Role::ADMIN,
                                'kh-perm__role-dot--employer' => $role->code === \App\Models\Role::EMPLOYER,
                                'kh-perm__role-dot--employee' => $role->code === \App\Models\Role::EMPLOYEE,
                            ])></span>
                            <span class="kh-perm__role-name">{{ $role->name_en }}</span>
                            <span class="kh-perm__role-meta">
                                {{ $role->code }}@if ($role->name_km) · {{ $role->name_km }}@endif
                                · {{ trans_choice('ui.bo.roles.users_count', $role->users_count, ['count' => $role->users_count]) }}
                            </span>
                            <span class="kh-perm__role-count" data-role-count="{{ $role->id }}">
                                {{ $granted }}/{{ $assignablePermissions }}
                            </span>
                        </button>
                    @endforeach
                </div>

                @foreach ($roles as $role)
                    @php($grantedIds = $role->permissions->pluck('id'))
                    <section class="kh-perm__panel"
                             id="role-panel-{{ $role->id }}"
                             role="tabpanel"
                             aria-labelledby="role-tab-{{ $role->id }}"
                             data-role-panel="{{ $role->id }}"
                             @if ($role->id !== $activeRole->id) hidden @endif>
                        <div class="kh-perm__panel-head">
                            <div>
                                <h2 class="kh-perm__panel-title">{{ $role->name_en }}</h2>
                                <p>{{ $role->enforcement }}</p>
                            </div>

                            <div class="kh-perm__panel-tools">
                                <label class="kh-perm__search">
                                    <i data-feather="search" aria-hidden="true"></i>
                                    <span class="sr-only visually-hidden">{{ __('ui.bo.roles.filter_functions') }}</span>
                                    <input type="search" placeholder="{{ __('ui.bo.roles.filter_functions_placeholder') }}" data-perm-search autocomplete="off">
                                </label>
                                <button type="button" class="kh-perm__mini" data-perm-bulk="on">
                                    <i data-feather="check-square" aria-hidden="true"></i> {{ __('ui.bo.roles.allow_all') }}
                                </button>
                                <button type="button" class="kh-perm__mini" data-perm-bulk="off">
                                    <i data-feather="square" aria-hidden="true"></i> {{ __('ui.bo.roles.clear') }}
                                </button>
                                <a class="kh-perm__mini" href="{{ route('users', ['role' => $role->code]) }}">
                                    <i data-feather="users" aria-hidden="true"></i>
                                    {{ trans_choice('ui.bo.roles.users_count', $role->users_count, ['count' => $role->users_count]) }}
                                </a>
                            </div>
                        </div>

                        @if ($role->notice)
                            <p @class(['kh-perm__notice', 'kh-perm__notice--caution' => $role->notice_tone === 'caution'])>
                                <i data-feather="{{ $role->notice_tone === 'caution' ? 'alert-triangle' : 'info' }}" aria-hidden="true"></i>
                                <span>{{ $role->notice }}</span>
                            </p>
                        @endif

                        <p class="kh-perm__empty">{{ __('ui.bo.roles.no_match') }}</p>

                        <div class="kh-perm__modules">
                            @foreach ($permissionGroups as $group)
                                <div class="kh-perm__module" data-perm-module>
                                    <div class="kh-perm__module-head">
                                        <span class="kh-perm__module-icon" aria-hidden="true">
                                            <i data-feather="{{ $group['icon'] }}"></i>
                                        </span>
                                        <div>
                                            <strong>{{ $group['label'] }}</strong>
                                            <small>{{ $group['blurb'] }}</small>
                                        </div>
                                        <label class="kh-perm__module-all">
                                            <span class="kh-perm__switch">
                                                <input type="checkbox" data-perm-module-all
                                                    @checked($grantedIds->intersect(collect($group['permissions'])->pluck('id'))->count() === count($group['permissions']))>
                                                <span></span>
                                            </span>
                                            {{ __('ui.bo.roles.all') }}
                                        </label>
                                    </div>

                                    <div class="kh-perm__rows">
                                        @foreach ($group['permissions'] as $permission)
                                            <label class="kh-perm__row"
                                                   data-perm-row="{{ \Illuminate\Support\Str::lower($permission->label() . ' ' . $permission->code . ' ' . $group['label'] . ' ' . $permission->description) }}">
                                                <span class="kh-perm__switch">
                                                    <input type="checkbox"
                                                           name="permissions[{{ $role->id }}][]"
                                                           value="{{ $permission->id }}"
                                                           data-perm-toggle
                                                           @checked($grantedIds->contains($permission->id))>
                                                    <span></span>
                                                </span>
                                                <div>
                                                    <strong>{{ $permission->label() }}</strong>
                                                    <small>{{ $permission->description }}</small>
                                                    <span class="kh-perm__code">{{ $permission->code }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="kh-perm__savebar">
                            <p>
                                <i data-feather="shield" aria-hidden="true"></i>
                                {{ __('ui.bo.roles.applies_immediately') }}
                                <span class="kh-perm__dirty" data-perm-dirty>{{ __('ui.bo.roles.unsaved', ['count' => 0]) }}</span>
                            </p>
                            <div class="kh-user-create__button-row">
                                <button class="kh-user-create__btn kh-user-create__btn--primary" type="submit">
                                    <i data-feather="check" aria-hidden="true"></i>
                                    <span>{{ __('ui.bo.roles.save') }}</span>
                                </button>
                            </div>
                        </div>
                    </section>
                @endforeach

                <div class="kh-perm__tips">
                    <div class="kh-perm__tip">
                        <span aria-hidden="true"><i data-feather="layers"></i></span>
                        <div>
                            <strong>{{ __('ui.bo.roles.tip_view') }}</strong>
                            <small>{{ __('ui.bo.roles.tip_view_body') }}</small>
                        </div>
                    </div>
                    <div class="kh-perm__tip">
                        <span aria-hidden="true"><i data-feather="shield"></i></span>
                        <div>
                            <strong>{{ __('ui.bo.roles.tip_assignable') }}</strong>
                            <small>{{ __('ui.bo.roles.tip_assignable_body') }}</small>
                        </div>
                    </div>
                    <div class="kh-perm__tip">
                        <span aria-hidden="true"><i data-feather="zap"></i></span>
                        <div>
                            <strong>{{ __('ui.bo.roles.tip_immediate') }}</strong>
                            <small>{{ __('ui.bo.roles.tip_immediate_body') }}</small>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const root = document.querySelector('[data-perm-root]');
            if (!root) return;

            /* Every role's panel stays in the DOM — hidden checkboxes still post,
               so switching tabs never drops another role's permissions. */
            const tabs = Array.from(root.querySelectorAll('[data-role-tab]'));
            const panels = Array.from(root.querySelectorAll('[data-role-panel]'));

            function selectRole(id) {
                tabs.forEach(tab => tab.setAttribute('aria-selected', String(tab.dataset.roleTab === id)));
                panels.forEach(panel => { panel.hidden = panel.dataset.rolePanel !== id; });
            }

            tabs.forEach(tab => tab.addEventListener('click', () => selectRole(tab.dataset.roleTab)));

            tabs.forEach((tab, index) => tab.addEventListener('keydown', event => {
                const step = event.key === 'ArrowRight' ? 1 : event.key === 'ArrowLeft' ? -1 : 0;
                if (!step) return;
                event.preventDefault();
                const next = tabs[(index + step + tabs.length) % tabs.length];
                next.focus();
                selectRole(next.dataset.roleTab);
            }));

            panels.forEach(panel => {
                const id = panel.dataset.rolePanel;
                const counter = root.querySelector('[data-role-count="' + id + '"]');
                const dirtyLabel = panel.querySelector('[data-perm-dirty]');
                const toggles = Array.from(panel.querySelectorAll('[data-perm-toggle]'));

                /* Every role is assignable, so a panel without toggles means the
                   modules are empty — leave the server-rendered count alone. */
                if (!toggles.length) return;

                const saved = new Map(toggles.map(input => [input.value, input.checked]));

                function syncModuleAll() {
                    panel.querySelectorAll('[data-perm-module]').forEach(module => {
                        const all = module.querySelector('[data-perm-module-all]');
                        const inputs = Array.from(module.querySelectorAll('[data-perm-toggle]'));
                        if (!all || !inputs.length) return;
                        const on = inputs.filter(input => input.checked).length;
                        all.checked = on === inputs.length;
                        all.indeterminate = on > 0 && on < inputs.length;
                    });
                }

                function refresh() {
                    if (counter) {
                        counter.textContent = toggles.filter(input => input.checked).length + '/' + toggles.length;
                    }
                    const changed = toggles.filter(input => saved.get(input.value) !== input.checked).length;
                    root.dataset.dirty = changed > 0 ? 'true' : 'false';
                    if (dirtyLabel) dirtyLabel.textContent = changed + ' unsaved';
                    syncModuleAll();
                }

                panel.addEventListener('change', event => {
                    const target = event.target;

                    if (target.matches('[data-perm-module-all]')) {
                        target.closest('[data-perm-module]')
                            .querySelectorAll('[data-perm-toggle]')
                            .forEach(input => { input.checked = target.checked; });
                    }

                    refresh();
                });

                /* Bulk acts on what the filter currently shows, so "Allow all"
                   after a search never grants functions the admin can't see. */
                panel.querySelectorAll('[data-perm-bulk]').forEach(button => button.addEventListener('click', () => {
                    const on = button.dataset.permBulk === 'on';
                    toggles
                        .filter(input => !input.closest('[data-perm-row]').hidden)
                        .forEach(input => { input.checked = on; });
                    refresh();
                }));

                const search = panel.querySelector('[data-perm-search]');
                if (search) {
                    search.addEventListener('input', () => {
                        const term = search.value.trim().toLowerCase();
                        let hits = 0;

                        panel.querySelectorAll('[data-perm-module]').forEach(module => {
                            let visible = 0;
                            module.querySelectorAll('[data-perm-row]').forEach(row => {
                                const match = !term || row.dataset.permRow.includes(term);
                                row.hidden = !match;
                                if (match) visible++;
                            });
                            module.hidden = visible === 0;
                            hits += visible;
                        });

                        panel.dataset.empty = hits === 0 ? 'true' : 'false';
                    });
                }

                refresh();
            });
        })();
    </script>
@endpush
