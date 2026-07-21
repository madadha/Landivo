<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Support\OrderDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class OrderDeletionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_permanently_deletes_a_test_order_and_its_stored_attachments(): void
    {
        Storage::fake('public');

        $account = Account::create(['name' => 'Test Account', 'slug' => 'test-account']);
        $customer = Customer::create([
            'account_id' => $account->id,
            'name' => 'Test Customer',
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
            'order_number' => 'TEST-DELETE-001',
            'currency' => 'AED',
        ]);

        $path = 'orders/attachments/test-order.txt';
        Storage::disk('public')->put($path, 'test attachment');
        $order->attachments()->create(['path' => $path, 'original_name' => 'test-order.txt']);
        $order->items()->create([
            'product_name' => 'Test Product',
            'quantity' => 1,
            'unit_price' => 10,
            'total' => 10,
        ]);

        app(OrderDeletionService::class)->delete($order);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
        $this->assertDatabaseMissing('order_attachments', ['order_id' => $order->id]);
        Storage::disk('public')->assertMissing($path);
    }
}
