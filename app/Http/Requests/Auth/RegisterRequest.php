<?php

namespace App\Http\Requests\Auth;

use App\Rules\AvailableCustomerEmail;
use App\Rules\ValidCaptcha;
use App\Support\CaptchaPolicy;
use App\Support\SecurityPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return CaptchaPolicy::registrationEnabled();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', new AvailableCustomerEmail],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'confirmed', SecurityPolicy::passwordRules()],
        ];

        if (CaptchaPolicy::requiredFor(CaptchaPolicy::CONTEXT_REGISTER)) {
            $rules['captcha_check'] = [new ValidCaptcha(CaptchaPolicy::CONTEXT_REGISTER)];
        }

        return $rules;
    }

    protected function failedAuthorization(): void
    {
        throw ValidationException::withMessages([
            'email' => __('menu.registration_disabled'),
        ])->redirectTo(route('login'));
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw (new ValidationException($validator))
            ->redirectTo(route('login', ['register' => 1]));
    }
}
