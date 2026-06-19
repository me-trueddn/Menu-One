@if(\App\Support\CaptchaPolicy::requiredFor($context))
    <div class="mb-3 captcha-wrap">
        @if(\App\Support\CaptchaPolicy::provider() === 'google')
            <div class="g-recaptcha" data-sitekey="{{ \App\Support\CaptchaPolicy::siteKey() }}"></div>
        @elseif(\App\Support\CaptchaPolicy::provider() === 'turnstile')
            <div class="cf-turnstile" data-sitekey="{{ \App\Support\CaptchaPolicy::siteKey() }}"></div>
        @endif
        @error('captcha')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <input type="hidden" name="captcha_check" value="1">
@endif
