<?php

namespace App\Support;

use App\Enums\PaymentMethod;

class PaymentConfig
{
    /** @return array<string, string> */
    public static function methodOptions(): array
    {
        $configured = config('payment.methods', []);

        if ($configured !== []) {
            $options = [];
            foreach ($configured as $value => $labelKey) {
                $options[$value] = __($labelKey);
            }

            return $options;
        }

        return PaymentMethod::options();
    }

    /** @return list<string> */
    public static function methodValues(): array
    {
        return array_keys(static::methodOptions());
    }
}
