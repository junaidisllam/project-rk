<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $input['contact'] = $input['contact'] ?? $input['email'] ?? $input['mobile'] ?? null;

        $isEmail = filter_var($input['contact'], FILTER_VALIDATE_EMAIL);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'contact' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class, $isEmail ? 'email' : 'mobile'),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $isEmail ? $input['contact'] : null,
            'mobile' => $isEmail ? null : $input['contact'],
            'password' => $input['password'],
        ]);
    }
}
