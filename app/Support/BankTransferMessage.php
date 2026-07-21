<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Order;

final class BankTransferMessage
{
    /** @return array{bank_name:string,account_holder:string,account_number:string,iban:string,swift:string,currency:string} */
    public static function details(?Account $account): array
    {
        $settings = (array) ($account?->settings ?? []);

        return [
            'bank_name' => trim((string) data_get($settings, 'bank_name')),
            'account_holder' => trim((string) data_get($settings, 'bank_account_holder')),
            'account_number' => trim((string) data_get($settings, 'bank_account_number')),
            'iban' => strtoupper(str_replace(' ', '', (string) data_get($settings, 'bank_iban'))),
            'swift' => strtoupper(trim((string) data_get($settings, 'bank_swift'))),
            'currency' => strtoupper(trim((string) data_get($settings, 'bank_currency', 'AED'))),
        ];
    }

    public static function isConfigured(?Account $account): bool
    {
        $details = self::details($account);

        return $details['bank_name'] !== ''
            && $details['account_holder'] !== ''
            && $details['account_number'] !== ''
            && $details['iban'] !== '';
    }

    public static function render(Order $order): string
    {
        $order->loadMissing(['account', 'customer']);
        $details = self::details($order->account);
        $customerName = trim((string) $order->customer?->name);
        $greeting = $customerName !== '' ? 'مرحبًا '.$customerName.'،' : 'مرحبًا،';
        $total = number_format((float) $order->total, 2).' '.($details['currency'] ?: $order->currency ?: 'AED');

        return implode("\n", array_filter([
            $greeting.' 🌿',
            'شكرًا لطلبك منّا. فيما يلي بيانات التحويل البنكي لإتمام الطلب رقم '.$order->order_number.':',
            '',
            '🏦 اسم البنك: '.$details['bank_name'],
            '👤 اسم صاحب الحساب: '.$details['account_holder'],
            '🔢 رقم الحساب: '.$details['account_number'],
            '💳 IBAN: '.$details['iban'],
            $details['swift'] !== '' ? '🌐 SWIFT: '.$details['swift'] : null,
            '💰 المبلغ المستحق: '.$total,
            '💱 العملة: '.($details['currency'] ?: 'AED'),
            '',
            'بعد إتمام التحويل، يرجى إرسال صورة إيصال التحويل هنا لتأكيد الطلب. شكرًا لثقتك بنا 🤍',
        ], static fn ($line): bool => $line !== null));
    }
}
