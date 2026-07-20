<?php

namespace App\Support;

use App\Models\LandingPage;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderInventoryService
{
    public function syncForStatusChange(Order $order, ?OrderStatus $oldStatus, ?OrderStatus $newStatus): void
    {
        $wasDeducting = (bool) $oldStatus?->deduct_inventory;
        $isDeducting = (bool) $newStatus?->deduct_inventory;

        if ($isDeducting && ! $order->inventory_deducted_at) {
            $this->deduct($order);
        } elseif ($wasDeducting && ! $isDeducting && $order->inventory_deducted_at) {
            $this->restore($order);
        }
    }

    public function deduct(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->inventory_deducted_at) {
                return;
            }

            $movements = $this->moveStock($lockedOrder, -1);
            $lockedOrder->updateQuietly(['inventory_deducted_at' => now()]);
            $order->setAttribute('inventory_deducted_at', $lockedOrder->inventory_deducted_at);
            $lockedOrder->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'inventory',
                'body' => 'تم خصم مخزون الطلب بعد اكتمال التسليم.',
                'metadata' => ['event' => 'inventory_deducted', 'movements' => $movements],
            ]);
        });
    }

    public function restore(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! $lockedOrder->inventory_deducted_at) {
                return;
            }

            $deduction = $lockedOrder->activities()
                ->where('type', 'inventory')
                ->where('metadata->event', 'inventory_deducted')
                ->latest()
                ->first();
            $movements = $this->restoreMovements((array) data_get($deduction?->metadata, 'movements', []));
            $lockedOrder->updateQuietly(['inventory_deducted_at' => null]);
            $order->setAttribute('inventory_deducted_at', null);
            $lockedOrder->activities()->create([
                'user_id' => auth()->id(),
                'type' => 'inventory',
                'body' => 'تمت إعادة كمية الطلب إلى المخزون بعد التراجع عن حالة التسليم.',
                'metadata' => ['event' => 'inventory_restored', 'movements' => $movements],
            ]);
        });
    }

    /** @return array<int, array<string, int|string>> */
    private function moveStock(Order $order, int $direction): array
    {
        $movements = [];
        $items = $order->items()->get();

        foreach ($items as $item) {
            $quantity = max(1, (int) $item->quantity);
            $delta = $direction * $quantity;

            if ($item->product_id) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if ($product) {
                    $before = (int) $product->quantity;
                    $after = max(0, $before + $delta);
                    $product->updateQuietly(['quantity' => $after]);
                    $movements[] = ['scope' => 'product', 'id' => $product->id, 'before' => $before, 'after' => $after, 'quantity' => $quantity, 'moved' => abs($after - $before)];
                }
            }

            if ($item->product_variant_id) {
                $variant = ProductVariant::query()->lockForUpdate()->find($item->product_variant_id);
                if ($variant) {
                    $before = (int) $variant->quantity;
                    $after = max(0, $before + $delta);
                    $variant->updateQuietly(['quantity' => $after]);
                    $movements[] = ['scope' => 'variant', 'id' => $variant->id, 'before' => $before, 'after' => $after, 'quantity' => $quantity, 'moved' => abs($after - $before)];
                }
            }
        }

        if ($order->landing_page_id) {
            $page = LandingPage::query()->lockForUpdate()->find($order->landing_page_id);
            if ($page?->track_inventory) {
                $quantity = max(1, (int) $items->sum('quantity'));
                $before = (int) $page->stock_quantity;
                $after = max(0, $before + ($direction * $quantity));
                $page->updateQuietly(['stock_quantity' => $after]);
                $movements[] = ['scope' => 'landing_page', 'id' => $page->id, 'before' => $before, 'after' => $after, 'quantity' => $quantity, 'moved' => abs($after - $before)];
            }
        }

        return $movements;
    }

    /** @param array<int, array<string, mixed>> $deductions
     * @return array<int, array<string, int|string>>
     */
    private function restoreMovements(array $deductions): array
    {
        $restored = [];

        foreach ($deductions as $movement) {
            $scope = (string) ($movement['scope'] ?? '');
            $id = (int) ($movement['id'] ?? 0);
            $quantity = max(0, (int) ($movement['moved'] ?? 0));

            if (! $id || ! $quantity) {
                continue;
            }

            $model = match ($scope) {
                'product' => Product::query()->lockForUpdate()->find($id),
                'variant' => ProductVariant::query()->lockForUpdate()->find($id),
                'landing_page' => LandingPage::query()->lockForUpdate()->find($id),
                default => null,
            };

            if (! $model) {
                continue;
            }

            $column = $scope === 'landing_page' ? 'stock_quantity' : 'quantity';
            $before = (int) $model->{$column};
            $after = $before + $quantity;
            $model->updateQuietly([$column => $after]);
            $restored[] = ['scope' => $scope, 'id' => $id, 'before' => $before, 'after' => $after, 'quantity' => $quantity];
        }

        return $restored;
    }
}
