<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Support\BankTransferMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankTransferMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_transfer_message_uses_account_settings_and_order_details(): void
    {
        $account = Account::create([
            'name' => 'Almwasem',
            'slug' => 'almwasem-bank-message',
            'settings' => [
                'bank_name' => 'ADIB',
                'bank_account_holder' => 'RAAD SATEA SALEM ALMDADHA',
                'bank_account_number' => '19546383',
                'bank_iban' => 'AE95 0500 0000 0001 9546 383',
                'bank_swift' => 'abdiaeadxxx',
                'bank_currency' => 'AED',
            ],
        ]);
        $customer = Customer::create([
            'account_id' => $account->id,
            'name' => 'ريان المدادحة',
            'phone' => '0500000000',
        ]);
        $status = OrderStatus::create([
            'account_id' => $account->id,
            'name_ar' => 'جديد',
            'name_en' => 'New',
            'slug' => 'new',
        ]);
        $order = Order::create([
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'order_status_id' => $status->id,
            'order_number' => 'LDV-BANK-1',
            'subtotal' => 650,
            'total' => 650,
            'currency' => 'AED',
        ]);

        $message = BankTransferMessage::render($order);

        self::assertTrue(BankTransferMessage::isConfigured($account));
        self::assertStringContainsString('مرحبًا ريان المدادحة', $message);
        self::assertStringContainsString('LDV-BANK-1', $message);
        self::assertStringContainsString('اسم البنك: ADIB', $message);
        self::assertStringContainsString('رقم الحساب: 19546383', $message);
        self::assertStringContainsString('IBAN: AE950500000000019546383', $message);
        self::assertStringContainsString('SWIFT: ABDIAEADXXX', $message);
        self::assertStringContainsString('650.00 AED', $message);
        self::assertStringContainsString('إرسال صورة إيصال التحويل', $message);
    }

    public function test_bank_button_configuration_requires_core_bank_details(): void
    {
        $account = Account::create([
            'name' => 'Incomplete bank',
            'slug' => 'incomplete-bank',
            'settings' => ['bank_name' => 'ADIB'],
        ]);

        self::assertFalse(BankTransferMessage::isConfigured($account));
    }
}
