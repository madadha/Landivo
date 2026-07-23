<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\DataTransfer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProcessDataExport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;

    public int $tries = 2;

    public function __construct(public int $transferId) {}

    public function handle(): void
    {
        $transfer = DataTransfer::query()->findOrFail($this->transferId);

        if ($transfer->status !== 'queued') {
            return;
        }

        $transfer->update(['status' => 'processing', 'started_at' => now(), 'error_message' => null]);
        $path = "data-transfers/{$transfer->account_id}/exports/".Str::uuid().'.csv';
        Storage::disk('local')->makeDirectory(dirname($path));
        $handle = fopen(Storage::disk('local')->path($path), 'wb');

        if ($handle === false) {
            throw new \RuntimeException('تعذر إنشاء ملف التصدير.');
        }

        fwrite($handle, "\xEF\xBB\xBF");

        try {
            match ($transfer->entity) {
                'orders' => $this->exportOrders($transfer, $handle),
                'customers' => $this->exportCustomers($transfer, $handle),
                'products' => $this->exportProducts($transfer, $handle),
                default => throw new \InvalidArgumentException('نوع بيانات التصدير غير مدعوم.'),
            };

            fclose($handle);
            $transfer->update([
                'status' => 'completed',
                'result_path' => $path,
                'completed_at' => now(),
                'processed_rows' => $transfer->fresh()->total_rows,
            ]);
        } catch (Throwable $exception) {
            fclose($handle);
            Storage::disk('local')->delete($path);
            $this->markFailed($exception);
            throw $exception;
        }
    }

    private function exportOrders(DataTransfer $transfer, $handle): void
    {
        fputcsv($handle, ['order_number', 'customer_name', 'phone', 'email', 'status', 'total', 'currency', 'source', 'utm_parameters', 'created_at']);
        $query = Order::query()->where('account_id', $transfer->account_id);
        $transfer->update(['total_rows' => (clone $query)->count()]);

        $query->with(['customer:id,name,phone,email', 'status:id,name_ar'])
            ->orderBy('id')
            ->chunkById(500, function ($orders) use ($transfer, $handle): void {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_number, $order->customer?->name, $order->customer?->phone,
                        $order->customer?->email, $order->status?->name_ar, $order->total,
                        $order->currency, $order->source,
                        json_encode($order->utm_parameters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        $order->created_at?->toDateTimeString(),
                    ]);
                }
                $this->advance($transfer, $orders->count());
            });
    }

    private function exportCustomers(DataTransfer $transfer, $handle): void
    {
        fputcsv($handle, ['name', 'phone', 'email', 'city', 'country', 'orders_count', 'orders_total', 'created_at']);
        $query = Customer::query()->where('account_id', $transfer->account_id);
        $transfer->update(['total_rows' => (clone $query)->count()]);

        $query->withCount('orders')->withSum('orders as orders_total', 'total')
            ->orderBy('id')
            ->chunkById(500, function ($customers) use ($transfer, $handle): void {
                foreach ($customers as $customer) {
                    fputcsv($handle, [
                        $customer->name, $customer->phone, $customer->email, $customer->city,
                        $customer->country, $customer->orders_count, $customer->orders_total,
                        $customer->created_at?->toDateTimeString(),
                    ]);
                }
                $this->advance($transfer, $customers->count());
            });
    }

    private function exportProducts(DataTransfer $transfer, $handle): void
    {
        fputcsv($handle, ['sku', 'name_ar', 'name_en', 'description_ar', 'description_en', 'price', 'compare_at_price', 'currency', 'quantity', 'status']);
        $query = Product::query()->where('account_id', $transfer->account_id);
        $transfer->update(['total_rows' => (clone $query)->count()]);

        $query->with('translations')->orderBy('id')
            ->chunkById(500, function ($products) use ($transfer, $handle): void {
                foreach ($products as $product) {
                    $ar = $product->translations->firstWhere('locale', 'ar');
                    $en = $product->translations->firstWhere('locale', 'en');
                    fputcsv($handle, [
                        $product->sku, $ar?->name, $en?->name, $ar?->description, $en?->description,
                        $product->price, $product->compare_at_price, $product->currency,
                        $product->quantity, $product->status?->value ?? (string) $product->status,
                    ]);
                }
                $this->advance($transfer, $products->count());
            });
    }

    private function advance(DataTransfer $transfer, int $count): void
    {
        DataTransfer::query()->whereKey($transfer->getKey())->incrementEach([
            'processed_rows' => $count,
            'succeeded_rows' => $count,
        ]);
    }

    private function markFailed(Throwable $exception): void
    {
        DataTransfer::query()->whereKey($this->transferId)->update([
            'status' => 'failed',
            'error_message' => Str::limit($exception->getMessage(), 2000),
            'completed_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            $this->markFailed($exception);
        }
    }
}
