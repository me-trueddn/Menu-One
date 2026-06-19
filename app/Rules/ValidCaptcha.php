<?php

namespace App\Rules;

use App\Services\CaptchaVerifier;
use App\Support\CaptchaPolicy;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class ValidCaptcha implements ValidationRule
{
    public function __construct(private string $context) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! CaptchaPolicy::requiredFor($this->context)) {
            return;
        }

        /** @var Request $request */
        $request = request();
        $token = CaptchaVerifier::tokenFromRequest($request);

        if (! CaptchaVerifier::verify($token, $request->ip())) {
            $fail(__('menu.captcha_failed'));
        }
    }
}
