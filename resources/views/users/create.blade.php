@extends('layouts.admin')

@section('main-content')
    <style>
        .kh-user-create {
            padding: 1.5rem 1.5rem 2rem;
        }

        .kh-user-create__hero {
            position: relative;
            overflow: hidden;
            padding: 1.9rem;
            margin-bottom: 1.5rem;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.14), transparent 24%),
                linear-gradient(135deg, #0b466f 0%, #11517f 55%, #156799 100%);
            border-radius: 0;
            box-shadow: 0 20px 42px rgba(11, 70, 111, 0.18);
        }

        .kh-user-create__hero::after {
            content: "";
            position: absolute;
            right: -70px;
            bottom: -90px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .kh-user-create__hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(300px, 1fr);
            gap: 1.5rem;
            align-items: start;
        }

        .kh-user-create__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.45rem 0.85rem;
            margin-bottom: 1rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.12);
        }

        .kh-user-create__hero h1 {
            margin: 0 0 0.75rem;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            letter-spacing: -0.05em;
        }

        .kh-user-create__hero p {
            max-width: 56ch;
            margin: 0;
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.7;
        }

        .kh-user-create__hero-highlights {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.2rem;
        }

        .kh-user-create__hero-highlights span {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .kh-user-create__hero-side {
            padding: 1.2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .kh-user-create__hero-side strong {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 2rem;
            line-height: 1;
        }

        .kh-user-create__hero-side p {
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.92rem;
        }

        .kh-user-create__content {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(280px, 0.8fr);
            gap: 1.5rem;
        }

        .kh-user-create__card {
            background: #fff;
            border: 1px solid #e6edf5;
            border-radius: 0;
            box-shadow: 0 16px 30px rgba(17, 24, 39, 0.05);
        }

        .kh-user-create__form-card {
            padding: 1.5rem;
        }

        .kh-user-create__section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.4rem;
        }

        .kh-user-create__section-head h2 {
            margin: 0 0 0.3rem;
            color: #101828;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .kh-user-create__section-head p {
            margin: 0;
            color: #667085;
            font-size: 0.92rem;
        }

        .kh-user-create__badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.45rem 0.8rem;
            color: #0b466f;
            background: #e7f1f8;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .kh-user-create__alert {
            margin-bottom: 1rem;
            padding: 0.95rem 1rem;
            border-radius: 0;
        }

        .kh-user-create__alert ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        .kh-user-create__form {
            display: grid;
            gap: 1.1rem;
        }

        .kh-user-create__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .kh-user-create__field {
            display: grid;
            gap: 0.55rem;
        }

        .kh-user-create__label {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin: 0;
            color: #344054;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .kh-user-create__label i {
            color: #0b466f;
        }

        .kh-user-create__control {
            width: 100%;
            min-height: 58px;
            padding: 0 1rem;
            color: #101828;
            background: #fff;
            border: 1px solid #d7e1ec;
            border-radius: 0;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .kh-user-create__control:focus {
            border-color: #0b466f;
            box-shadow: 0 0 0 4px rgba(11, 70, 111, 0.08);
        }

        .kh-user-create__control::placeholder {
            color: #98a1bb;
        }

        .kh-user-create__actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.4rem;
        }

        .kh-user-create__helper {
            color: #667085;
            font-size: 0.9rem;
        }

        .kh-user-create__button-row {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .kh-user-create__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 46px;
            padding: 0 1rem;
            border: 0;
            border-radius: 0;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
        }

        .kh-user-create__btn:hover {
            text-decoration: none;
        }

        .kh-user-create__btn--primary {
            color: #fff;
            background: #0b466f;
            box-shadow: 0 14px 24px rgba(11, 70, 111, 0.16);
        }

        .kh-user-create__btn--primary:hover {
            color: #fff;
            background: #093654;
        }

        .kh-user-create__btn--ghost {
            color: #0b466f;
            background: #eef4f8;
        }

        .kh-user-create__btn--ghost:hover {
            color: #0b466f;
            background: #e1edf4;
        }

        .kh-user-create__side {
            display: grid;
            gap: 1.5rem;
        }

        .kh-user-create__panel {
            padding: 1.3rem;
        }

        .kh-user-create__panel h3 {
            margin: 0 0 0.75rem;
            color: #101828;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .kh-user-create__panel p {
            margin: 0;
            color: #667085;
            line-height: 1.7;
            font-size: 0.92rem;
        }

        .kh-user-create__tips {
            display: grid;
            gap: 0.9rem;
            margin-top: 1rem;
        }

        .kh-user-create__tip {
            display: flex;
            gap: 0.8rem;
            align-items: flex-start;
            padding: 0.95rem 1rem;
            background: #fbfdff;
            border: 1px solid #edf2f7;
        }

        .kh-user-create__tip-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            min-width: 38px;
            color: #0b466f;
            background: #e7f1f8;
        }

        .kh-user-create__tip strong {
            display: block;
            color: #101828;
            margin-bottom: 0.2rem;
        }

        .kh-user-create__tip span {
            color: #667085;
            font-size: 0.88rem;
            line-height: 1.6;
        }

        .kh-user-create__stats {
            display: grid;
            gap: 0.9rem;
            margin-top: 1rem;
        }

        .kh-user-create__stat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.9rem 1rem;
            background: #fbfdff;
            border: 1px solid #edf2f7;
        }

        .kh-user-create__stat strong {
            display: block;
            color: #101828;
        }

        .kh-user-create__stat span {
            color: #667085;
            font-size: 0.88rem;
        }

        .kh-user-create__stat-badge {
            color: #0b466f;
            font-weight: 700;
        }

        @media (max-width: 1200px) {
            .kh-user-create__content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            .kh-user-create {
                padding: 1rem;
            }

            .kh-user-create__hero-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .kh-user-create__grid {
                grid-template-columns: 1fr;
            }

            .kh-user-create__hero,
            .kh-user-create__form-card,
            .kh-user-create__panel {
                padding: 1rem;
            }

            .kh-user-create__actions {
                align-items: stretch;
            }
        }
    </style>

    <div class="kh-user-create">
        <section class="kh-user-create__hero">
            <div class="kh-user-create__hero-grid">
                <div>
                    <span class="kh-user-create__eyebrow">
                        <i data-feather="user-plus"></i>
                        User Management
                    </span>
                    <h1>Create a polished new user profile</h1>
                    <p>Add team members with the right access level, keep your directory organized, and bring new users into KH-WORKS with a cleaner admin workflow.</p>

                    <div class="kh-user-create__hero-highlights">
                        <span><i data-feather="shield"></i> Role-based access control</span>
                        <span><i data-feather="check-circle"></i> Clean onboarding flow</span>
                    </div>
                </div>

                <div class="kh-user-create__hero-side">
                    <div class="kh-user-create__badge">Quick Note</div>
                    <strong>Ready</strong>
                    <p>Create internal accounts for administrators, editors, guests, or registered members from one focused workspace.</p>
                </div>
            </div>
        </section>

        <div class="kh-user-create__content">
            <section class="kh-user-create__card kh-user-create__form-card">
                <div class="kh-user-create__section-head">
                    <div>
                        <h2>Users List</h2>
                        <p>Fill in the user information below to create a fresh account.</p>
                    </div>
                    <span class="kh-user-create__badge">Admin Form</span>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger kh-user-create__alert" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.store') }}" class="kh-user-create__form">
                    @csrf

                    <div class="kh-user-create__grid">
                        <div class="kh-user-create__field">
                            <label class="kh-user-create__label" for="inputFirstName">
                                <i data-feather="user"></i>
                                <span>First Name</span>
                            </label>
                            <input class="kh-user-create__control" id="inputFirstName" name="first_name" type="text" value="{{ old('first_name') }}" placeholder="Enter your first name" required>
                        </div>

                        <div class="kh-user-create__field">
                            <label class="kh-user-create__label" for="inputLastName">
                                <i data-feather="user-check"></i>
                                <span>Last Name</span>
                            </label>
                            <input class="kh-user-create__control" id="inputLastName" name="last_name" type="text" value="{{ old('last_name') }}" placeholder="Enter your last name" required>
                        </div>
                    </div>

                    <div class="kh-user-create__field">
                        <label class="kh-user-create__label" for="inputEmailAddress">
                            <i data-feather="mail"></i>
                            <span>Email Address</span>
                        </label>
                        <input class="kh-user-create__control" id="inputEmailAddress" name="email" type="email" value="{{ old('email') }}" placeholder="Enter your email address" required>
                    </div>

                    <div class="kh-user-create__field">
                        <label class="kh-user-create__label" for="inputRole">
                            <i data-feather="briefcase"></i>
                            <span>Role</span>
                        </label>
                        <select class="kh-user-create__control" id="inputRole" name="role" required>
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select a role:</option>
                            <option value="administrator" {{ old('role') === 'administrator' ? 'selected' : '' }}>Administrator</option>
                            <option value="registered" {{ old('role') === 'registered' ? 'selected' : '' }}>Registered</option>
                            <option value="editor" {{ old('role') === 'editor' ? 'selected' : '' }}>Editor</option>
                            <option value="guest" {{ old('role') === 'guest' ? 'selected' : '' }}>Guest</option>
                        </select>
                    </div>

                    <div class="kh-user-create__grid">
                        <div class="kh-user-create__field">
                            <label class="kh-user-create__label" for="inputPassword">
                                <i data-feather="lock"></i>
                                <span>Password</span>
                            </label>
                            <input class="kh-user-create__control" id="inputPassword" name="password" type="password" placeholder="Enter password" required>
                        </div>

                        <div class="kh-user-create__field">
                            <label class="kh-user-create__label" for="inputPasswordConfirm">
                                <i data-feather="shield"></i>
                                <span>Confirm Password</span>
                            </label>
                            <input class="kh-user-create__control" id="inputPasswordConfirm" name="password_confirmation" type="password" placeholder="Confirm password" required>
                        </div>
                    </div>

                    <div class="kh-user-create__actions">
                        <div class="kh-user-create__helper">Use strong credentials and assign the correct role before saving this account.</div>

                        <div class="kh-user-create__button-row">
                            <a class="kh-user-create__btn kh-user-create__btn--ghost" href="{{ route('users') }}">
                                <i data-feather="arrow-left"></i>
                                <span>Back to Users</span>
                            </a>
                            <button class="kh-user-create__btn kh-user-create__btn--primary" type="submit">
                                <i data-feather="user-plus"></i>
                                <span>Add User</span>
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <aside class="kh-user-create__side">
                <section class="kh-user-create__card kh-user-create__panel">
                    <h3>Admin Tips</h3>
                    <p>Keep user creation clean and intentional with a structured setup flow designed for admin teams.</p>

                    <div class="kh-user-create__tips">
                        <div class="kh-user-create__tip">
                            <div class="kh-user-create__tip-icon"><i data-feather="layers"></i></div>
                            <div>
                                <strong>Choose the right role</strong>
                                <span>Use administrator only for trusted internal staff with broad permissions.</span>
                            </div>
                        </div>

                        <div class="kh-user-create__tip">
                            <div class="kh-user-create__tip-icon"><i data-feather="mail"></i></div>
                            <div>
                                <strong>Verify email accuracy</strong>
                                <span>Incorrect email addresses can block onboarding and password recovery.</span>
                            </div>
                        </div>

                        <div class="kh-user-create__tip">
                            <div class="kh-user-create__tip-icon"><i data-feather="lock"></i></div>
                            <div>
                                <strong>Use secure credentials</strong>
                                <span>Encourage users to update their password after first login if needed.</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="kh-user-create__card kh-user-create__panel">
                    <h3>Directory Snapshot</h3>
                    <p>A quick read on the current makeup of your user base.</p>

                    <div class="kh-user-create__stats">
                        <div class="kh-user-create__stat">
                            <div>
                                <strong>Administrators</strong>
                                <span>Core management access</span>
                            </div>
                            <div class="kh-user-create__stat-badge">08</div>
                        </div>

                        <div class="kh-user-create__stat">
                            <div>
                                <strong>Editors</strong>
                                <span>Content and moderation support</span>
                            </div>
                            <div class="kh-user-create__stat-badge">16</div>
                        </div>

                        <div class="kh-user-create__stat">
                            <div>
                                <strong>Registered Users</strong>
                                <span>Standard platform accounts</span>
                            </div>
                            <div class="kh-user-create__stat-badge">214</div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
