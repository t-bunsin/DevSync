<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\Company;
use App\Models\EmployerProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
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
     * Overrides the trait's version to hand the form the approved companies an
     * employer can register against.
     */
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'companies' => Company::approved()->orderBy('name')->get(),
        ]);
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
            // An employer either picks an approved company or names a new one
            // for us to register; exactly one of the two has to arrive.
            'company_id' => ['nullable', 'required_without:company_name', 'exclude_unless:account_type,employer', 'exists:companies,id'],
            'company_name' => ['nullable', 'required_without:company_id', 'exclude_unless:account_type,employer', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'company_id.required_without' => 'Choose your company, or add its name if it is not listed.',
            'company_name.required_without' => 'Please tell us which company you are hiring for.',
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
     * An employer either signs up against a company we already approved, or
     * registers a new one. A newly named company lands as `pending` — it is an
     * unvetted claim until an admin approves it in the back office.
     */
    protected function resolveCompany(array $data): Company
    {
        if (! empty($data['company_id'])) {
            return Company::findOrFail($data['company_id']);
        }

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
