<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderDeletionService
{
    public function delete(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($lockedOrder->inventory_deducted_at) {
                app(OrderInventoryService::class)->restore($lockedOrder);
            }

            $attachmentPaths = $lockedOrder->attachments()
                ->pluck('path')
                ->filter()
                ->values()
                ->all();

            $lockedOrder->delete();

            if ($attachmentPaths !== []) {
                Storage::disk('public')->delete($attachmentPaths);
            }
        });
    }
}
