<?php

namespace App\Rules;

use App\Support\MediaLimits;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class AllowedImageUpload implements ValidationRule
{
    public function __construct(private string $context) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $config = MediaLimits::imageContext($this->context);
        $mime = strtolower((string) $value->getMimeType());

        if (MediaLimits::shouldStayLocalMime($mime)) {
            return;
        }

        $extension = strtolower((string) $value->getClientOriginalExtension());

        if (! in_array($extension, $config['mimes'], true)) {
            $fail(__('menu.media_format_not_allowed'));
        }
    }
}
