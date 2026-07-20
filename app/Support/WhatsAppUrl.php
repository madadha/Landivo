<?php

namespace App\Support;

use App\Models\Order;

final class WhatsAppUrl
{
    public static function normalize(?string $phone, ?string $defaultCountryCode = '971'): ?string
    {
        $phone = strtr(trim((string) $phone), [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        $countryCode = ltrim(preg_replace('/\D+/', '', (string) $defaultCountryCode) ?? '', '0');

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && $countryCode !== '') {
            $digits = $countryCode.ltrim($digits, '0');
        } elseif ($countryCode !== '' && ! str_starts_with($digits, $countryCode) && strlen($digits) <= 10) {
            $digits = $countryCode.$digits;
        }

        return preg_match('/^[1-9]\d{7,14}$/', $digits) === 1 ? $digits : null;
    }

    public static function make(?string $phone, string $message = '', ?string $defaultCountryCode = '971'): string
    {
        $normalized = self::normalize($phone, $defaultCountryCode);

        if ($normalized === null) {
            return '#';
        }

        return 'https://wa.me/'.$normalized.($message !== '' ? '?text='.rawurlencode($message) : '');
    }

    public static function forOrder(Order $order, string $message = ''): string
    {
        $order->loadMissing(['customer', 'account']);

        return self::make(
            $order->customer?->phone,
            $message,
            $order->account?->phone_country_code ?: '971',
        );
    }

    public static function hasValidOrderPhone(Order $order): bool
    {
        $order->loadMissing(['customer', 'account']);

        return self::normalize(
            $order->customer?->phone,
            $order->account?->phone_country_code ?: '971',
        ) !== null;
    }
}
