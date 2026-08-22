<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\Company;
use App\Models\EmployerProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Registering no longer signs anyone in. The account exists but is unusable
     * until the code mailed to the address is entered, which is the whole point
     * of the step — an address nobody can read is not an account anyone owns.
     *
     * Overrides RegistersUsers::register(), which would log the user straight
     * in. `url.intended` is left in the session untouched: a visitor sent here
     * by the job apply gate still lands back on that job, one screen later.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        EmailVerificationCodeController::start($request, $user);

        return redirect()
            ->route('verification.code')
            ->withSuccess('We sent a 6-digit code to ' . $user->email . '.');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'account_type' => ['required', 'in:employee,employer'],
            'company_name' => ['nullable', 'required_if:account_type,employer', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'company_name.required_if' => 'Please tell us which company you are hiring for.',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $isEmployer = ($data['account_type'] ?? 'employee') === 'employer';

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'display_name' => User::composeDisplayName($data['first_name'], $data['last_name']),
            'email' => $data['email'],
            'status' => User::STATUS_ACTIVE,
            'preferred_locale' => in_array(app()->getLocale(), ['km', 'kh'], true) ? 'km' : 'en',
            'password_hash' => Hash::make($data['password']),
        ]);

        $role = $isEmployer ? Role::EMPLOYER : Role::EMPLOYEE;
        $user->syncRoles([$role], $role);

        if ($isEmployer) {
            $company = $this->resolveCompany($data);

            EmployerProfile::create([
                'user_id' => $user->id,
                'company_name' => $company->name,
            ]);
        }

        return $user;
    }

    /**
     * Employers type their company name. An existing company is reused; a new
     * name lands as `pending` — an unvetted claim until an admin approves it
     * in the back office.
     */
    protected function resolveCompany(array $data): Company
    {
        $name = trim($data['company_name']);

        // Match case-insensitively so "aba bank" does not create a second ABA Bank.
        $existing = Company::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        return $existing ?: Company::create([
            'name' => $name,
            'slug' => Company::makeSlug($name),
            'status' => Company::STATUS_PENDING,
        ]);
    }
}
